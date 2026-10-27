<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\UserStatus;
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
}
