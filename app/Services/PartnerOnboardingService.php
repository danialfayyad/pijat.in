<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\BankAccount;
use App\Models\ComplianceAgreement;
use App\Models\PartnerProfile;
use App\Models\Region;
use App\Models\User;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PartnerOnboardingService
{
    public function __construct(
        protected RegionService $regionService,
        protected ImageCompressionService $imageCompressionService
    ) {}

    /**
     * Submit partner onboarding documents and data.
     *
     * @param  array{
     *     nik: string,
     *     latitude: float,
     *     longitude: float,
     *     address_text: string,
     *     bank_name: string,
     *     account_number: string,
     *     account_holder_name: string,
     *     phone?: string|null,
     *     ktp_file: UploadedFile|string,
     *     selfie_file: UploadedFile|string,
     *     skck_file?: UploadedFile|string|null,
     *     bank_proof_file?: UploadedFile|string|null,
     *     agreement_version?: string
     * }  $data
     */
    public function submitOnboarding(User $user, array $data): PartnerProfile
    {
        $latitude = (float) $data['latitude'];
        $longitude = (float) $data['longitude'];

        // Validate that coordinates fall within an ACTIVE region
        $activeRegion = $this->regionService->findActiveRegionByCoordinates($latitude, $longitude);

        if (! $activeRegion) {
            throw new DomainException('Titik domisili berada di luar wilayah layanan aktif pijat.in.');
        }

        if (! empty($data['region_id']) && (int) $data['region_id'] !== $activeRegion->id) {
            $selectedRegion = Region::find($data['region_id']);
            $regionName = $selectedRegion ? $selectedRegion->name : 'wilayah terpilih';
            throw new DomainException("Titik koordinat yang Anda sematkan berada di luar batas wilayah {$regionName}.");
        }

        return DB::transaction(function () use ($user, $data, $latitude, $longitude, $activeRegion) {
            // Handle file uploads
            $ktpPath = $this->storeFile($data['ktp_file'], 'partners/ktp');
            $selfiePath = $this->storeFile($data['selfie_file'], 'partners/selfie');
            $skckPath = isset($data['skck_file']) ? $this->storeFile($data['skck_file'], 'partners/skck') : null;
            $bankProofPath = isset($data['bank_proof_file']) ? $this->storeFile($data['bank_proof_file'], 'partners/bank') : null;

            // 1. Create or update PartnerProfile
            $partnerProfile = PartnerProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'region_id' => $activeRegion->id,
                    'address_text' => $data['address_text'],
                    'coordinate' => ['lat' => $latitude, 'lng' => $longitude],
                    'ktp_path' => $ktpPath,
                    'selfie_path' => $selfiePath,
                    'skck_path' => $skckPath,
                    'is_active' => false,
                    'rejection_code' => null,
                    'rejection_reason' => null,
                ]
            );

            // 2. Create or update BankAccount
            BankAccount::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'bank_name' => $data['bank_name'],
                    'account_number' => $data['account_number'],
                    'account_holder_name' => $data['account_holder_name'],
                    'proof_path' => $bankProofPath,
                    'is_verified' => false,
                    'verified_at' => null,
                    'verified_by' => null,
                ]
            );

            // 3. Record ComplianceAgreement
            ComplianceAgreement::create([
                'user_id' => $user->id,
                'agreement_type' => 'SOP_KEMITRAAN',
                'version' => $data['agreement_version'] ?? '2.2',
                'agreed_at' => now(),
            ]);

            // 4. Update User info and status to MENUNGGU_VERIFIKASI
            $userUpdateData = [
                'nik' => $data['nik'],
                'status' => UserStatus::MenungguVerifikasi,
                'phone_verified_at' => $user->phone_verified_at ?? now(),
                'rejection_code' => null,
                'rejection_reason' => null,
            ];

            if (! empty($data['phone'])) {
                $userUpdateData['phone'] = $data['phone'];
            }

            $user->update($userUpdateData);

            return $partnerProfile;
        });
    }

    /**
     * Toggle partner active / resting status (MIT-04).
     */
    public function toggleOnlineStatus(
        User $user,
        bool $isActive,
        ?float $latitude = null,
        ?float $longitude = null
    ): PartnerProfile {
        if (! $user->isTerverifikasi()) {
            throw new DomainException('Hanya mitra yang telah terverifikasi yang dapat mengubah status aktif menerima pesanan.');
        }

        if ($user->status === UserStatus::Ditangguhkan) {
            throw new DomainException('Akun Anda sedang ditangguhkan. Tidak dapat mengaktifkan status online.');
        }

        $profile = $user->partnerProfile;
        if (! $profile) {
            throw new DomainException('Profil mitra tidak ditemukan.');
        }

        $updateData = ['is_active' => $isActive];

        if ($isActive) {
            if ($latitude !== null && $longitude !== null) {
                $pointWkt = sprintf('POINT(%F %F)', $longitude, $latitude);
                $updateData['current_coordinate'] = DB::raw("ST_GeomFromText('{$pointWkt}', 4326, 'axis-order=long-lat')");
            }
            $updateData['last_active_at'] = now();
        }

        $profile->update($updateData);

        return $profile;
    }

    /**
     * Store file with automatic image compression or return path string.
     */
    protected function storeFile(UploadedFile|string|null $file, string $directory): string
    {
        if ($file instanceof UploadedFile) {
            return $this->imageCompressionService->compressAndStore($file, $directory);
        }

        return (string) $file;
    }
}
