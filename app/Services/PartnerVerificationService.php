<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\PartnerRelocationRequest;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PartnerVerificationService
{
    /**
     * Standard rejection codes and human-readable reasons (ADM-02).
     *
     * @var array<string, string>
     */
    public const STANDARD_REASONS = [
        'KTP_BLURRED' => 'Foto KTP buram, tidak terbaca jelas, atau terpotong',
        'SELFIE_MISMATCH' => 'Foto selfie bersama KTP tidak cocok dengan wajah identitas KTP',
        'BANK_NAME_MISMATCH' => 'Nama pemilik rekening bank tidak sesuai dengan nama di KTP',
        'NIK_INVALID' => 'Format NIK tidak valid atau tidak cocok dengan data resmi',
        'OUTSIDE_REGION' => 'Titik alamat domisili fisik berada di luar cakupan wilayah tugas layanan',
        'OTHER' => 'Lainnya (alasan khusus tertera pada catatan)',
    ];

    /**
     * Get pending partner applications for the assigned regions of an admin.
     *
     * @return Collection<int, User>
     */
    public function getPendingPartners(User $admin, ?int $filterRegionId = null): Collection
    {
        $query = User::where('role', Role::Mitra)
            ->where('status', UserStatus::MenungguVerifikasi)
            ->whereDoesntHave('relocationRequests', fn ($q) => $q->where('status', PartnerRelocationRequest::STATUS_PENDING))
            ->with(['partnerProfile.region', 'primaryBankAccount', 'complianceAgreements']);

        if (! $admin->isSuperadmin()) {
            $assignedRegionIds = $admin->assignedRegions()->pluck('regions.id')->toArray();
            $query->whereHas('partnerProfile', function ($q) use ($assignedRegionIds) {
                $q->whereIn('region_id', $assignedRegionIds);
            });
        }

        if ($filterRegionId) {
            $query->whereHas('partnerProfile', function ($q) use ($filterRegionId) {
                $q->where('region_id', $filterRegionId);
            });
        }

        return $query->latest()->get();
    }

    /**
     * Get pending relocation requests targeted to admin's assigned regions (MIT-22).
     *
     * @return Collection<int, PartnerRelocationRequest>
     */
    public function getPendingRelocations(User $admin, ?int $filterRegionId = null): Collection
    {
        $query = PartnerRelocationRequest::pending()
            ->with(['partner.partnerProfile', 'fromRegion', 'toRegion']);

        if (! $admin->isSuperadmin()) {
            $assignedRegionIds = $admin->assignedRegions()->pluck('regions.id')->toArray();
            $query->whereIn('to_region_id', $assignedRegionIds);
        }

        if ($filterRegionId) {
            $query->where('to_region_id', $filterRegionId);
        }

        return $query->latest()->get();
    }

    /**
     * Approve partner application (ADM-02, ADM-03).
     */
    public function approvePartner(User $admin, User $mitra): void
    {
        $profile = $mitra->partnerProfile;

        if (! $profile) {
            throw new DomainException('Data profil mitra belum lengkap.');
        }

        if (! $admin->canAccessRegion($profile->region_id)) {
            throw new DomainException('Anda tidak memiliki otorisasi untuk memverifikasi mitra di luar wilayah tugas Anda.');
        }

        DB::transaction(function () use ($admin, $mitra, $profile) {
            $mitra->update([
                'status' => UserStatus::Terverifikasi,
                'rejection_code' => null,
                'rejection_reason' => null,
            ]);

            $profile->update([
                'verified_at' => now(),
                'verified_by' => $admin->id,
                'rejection_code' => null,
                'rejection_reason' => null,
            ]);

            $mitra->primaryBankAccount?->update([
                'is_verified' => true,
                'verified_at' => now(),
                'verified_by' => $admin->id,
            ]);
        });
    }

    /**
     * Reject partner application with a standard rejection reason (ADM-02).
     */
    public function rejectPartner(User $admin, User $mitra, string $rejectionCode, ?string $customNote = null): void
    {
        $profile = $mitra->partnerProfile;

        if (! $profile) {
            throw new DomainException('Data profil mitra belum lengkap.');
        }

        if (! $admin->canAccessRegion($profile->region_id)) {
            throw new DomainException('Anda tidak memiliki otorisasi untuk menolak mitra di luar wilayah tugas Anda.');
        }

        $standardReason = self::STANDARD_REASONS[$rejectionCode] ?? 'Dokumen tidak memenuhi persyaratan';
        $fullReason = $customNote ? "{$standardReason}. Catatan: {$customNote}" : $standardReason;

        DB::transaction(function () use ($mitra, $profile, $rejectionCode, $fullReason) {
            $mitra->update([
                'status' => UserStatus::Ditolak,
                'rejection_code' => $rejectionCode,
                'rejection_reason' => $fullReason,
            ]);

            $profile->update([
                'rejection_code' => $rejectionCode,
                'rejection_reason' => $fullReason,
            ]);
        });
    }

    /**
     * Approve partner relocation to destination region (MIT-22).
     */
    public function approveRelocation(User $admin, PartnerRelocationRequest $relocationRequest): void
    {
        if (! $admin->canAccessRegion($relocationRequest->to_region_id)) {
            throw new DomainException('Anda tidak memiliki otorisasi untuk menyetujui relokasi ke wilayah di luar penugasan Anda.');
        }

        if (! $relocationRequest->isPending()) {
            throw new DomainException('Permohonan relokasi ini telah diproses sebelumnya.');
        }

        $partner = $relocationRequest->partner;
        $profile = $partner->partnerProfile;

        if (! $profile) {
            throw new DomainException('Profil kemitraan tidak ditemukan.');
        }

        DB::transaction(function () use ($admin, $relocationRequest, $partner, $profile) {
            $relocationRequest->update([
                'status' => PartnerRelocationRequest::STATUS_APPROVED,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            $partner->update([
                'region_id' => $relocationRequest->to_region_id,
                'status' => UserStatus::Terverifikasi,
            ]);

            $profile->update([
                'region_id' => $relocationRequest->to_region_id,
                'address_text' => $relocationRequest->address_text,
                'latitude' => $relocationRequest->latitude,
                'longitude' => $relocationRequest->longitude,
                'coordinate' => ['lat' => (float) $relocationRequest->latitude, 'lng' => (float) $relocationRequest->longitude],
                'verified_at' => now(),
                'verified_by' => $admin->id,
            ]);

            app(NotificationService::class)->sendToUser(
                $partner,
                'partner_relocation_approved',
                'Perpindahan Wilayah Disetujui',
                "Selamat! Permohonan pindah domisili Anda ke {$relocationRequest->toRegion->name} telah disetujui. Akun dan wilayah operasional Anda kini aktif.",
                ['relocation_id' => $relocationRequest->id, 'to_region_id' => $relocationRequest->to_region_id],
                'high'
            );
        });
    }

    /**
     * Reject partner relocation request (MIT-22).
     */
    public function rejectRelocation(User $admin, PartnerRelocationRequest $relocationRequest, string $reason): void
    {
        if (! $admin->canAccessRegion($relocationRequest->to_region_id)) {
            throw new DomainException('Anda tidak memiliki otorisasi untuk menolak relokasi ke wilayah di luar penugasan Anda.');
        }

        if (! $relocationRequest->isPending()) {
            throw new DomainException('Permohonan relokasi ini telah diproses sebelumnya.');
        }

        $partner = $relocationRequest->partner;

        DB::transaction(function () use ($admin, $relocationRequest, $partner, $reason) {
            $relocationRequest->update([
                'status' => PartnerRelocationRequest::STATUS_REJECTED,
                'rejection_reason' => $reason,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            // Keep partner in original region with verified status restored
            $partner->update([
                'status' => UserStatus::Terverifikasi,
            ]);

            app(NotificationService::class)->sendToUser(
                $partner,
                'partner_relocation_rejected',
                'Perpindahan Wilayah Ditolak',
                "Permohonan pindah domisili ke {$relocationRequest->toRegion->name} ditolak. Alasan: {$reason}.",
                ['relocation_id' => $relocationRequest->id, 'rejection_reason' => $reason],
                'high'
            );
        });
    }
}
