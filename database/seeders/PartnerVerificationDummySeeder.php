<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\BankAccount;
use App\Models\ComplianceAgreement;
use App\Models\PartnerProfile;
use App\Models\Region;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PartnerVerificationDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $blitar = Region::where('name', 'like', '%BLITAR%')->first() ?? Region::first();
        $jember = Region::where('name', 'like', '%JEMBER%')->first() ?? Region::skip(1)->first() ?? $blitar;

        // =========================================================================
        // 1. MITRA DEWI LESTARI (Valid Application, Primary + Secondary Region, Bank Name Matched)
        // =========================================================================
        $dewi = User::updateOrCreate(
            ['email' => 'dewi.lestari@pijatin.test'],
            [
                'name' => 'Dewi Lestari',
                'nik' => '3505016508920002',
                'gender' => 'perempuan',
                'phone' => '081298765432',
                'date_of_birth' => '1992-08-15',
                'role' => Role::Mitra,
                'status' => UserStatus::MenungguVerifikasi,
                'region_id' => $blitar->id,
                'penalty_points' => 0,
                'password' => 'password',
                'email_verified_at' => Carbon::now()->subDays(1),
                'phone_verified_at' => Carbon::now()->subDays(1),
            ]
        );

        PartnerProfile::updateOrCreate(
            ['user_id' => $dewi->id],
            [
                'region_id' => $blitar->id,
                'secondary_region_id' => $jember->id,
                'address_text' => 'Jl. Melati No. 12, Kel. Kepanjenkidul, Kec. Kepanjenkidul, Kota Blitar',
                'coordinate' => ['lat' => -8.0983, 'lng' => 112.1682],
                'ktp_path' => 'partners/ktp/dummy_ktp_dewi.svg',
                'selfie_path' => 'partners/selfie/dummy_selfie_dewi.svg',
                'is_active' => false,
            ]
        );

        BankAccount::updateOrCreate(
            ['user_id' => $dewi->id],
            [
                'bank_name' => 'Bank Central Asia (BCA)',
                'account_number' => '0182938475',
                'account_holder_name' => 'Dewi Lestari',
                'is_verified' => false,
            ]
        );

        ComplianceAgreement::updateOrCreate(
            [
                'user_id' => $dewi->id,
                'agreement_type' => 'SOP_KEMITRAAN',
            ],
            [
                'version' => '2.2',
                'agreed_at' => Carbon::now()->subHours(3),
            ]
        );

        // =========================================================================
        // 2. MITRA RAHMAT HIDAYAT (Mismatched Bank Account Name for ADM-03 Verification Alert)
        // =========================================================================
        $rahmat = User::updateOrCreate(
            ['email' => 'rahmat.hidayat@pijatin.test'],
            [
                'name' => 'Rahmat Hidayat',
                'nik' => '3505021405900003',
                'gender' => 'laki-laki',
                'phone' => '081377889900',
                'date_of_birth' => '1990-05-14',
                'role' => Role::Mitra,
                'status' => UserStatus::MenungguVerifikasi,
                'region_id' => $blitar->id,
                'penalty_points' => 0,
                'password' => 'password',
                'email_verified_at' => Carbon::now()->subDays(2),
                'phone_verified_at' => Carbon::now()->subDays(2),
            ]
        );

        PartnerProfile::updateOrCreate(
            ['user_id' => $rahmat->id],
            [
                'region_id' => $blitar->id,
                'secondary_region_id' => null,
                'address_text' => 'Jl. Cempaka No. 8, Kel. Sukorejo, Kec. Sukorejo, Kota Blitar',
                'coordinate' => ['lat' => -8.1025, 'lng' => 112.1550],
                'ktp_path' => 'partners/ktp/dummy_ktp_rahmat.svg',
                'selfie_path' => 'partners/selfie/dummy_selfie_rahmat.svg',
                'is_active' => false,
            ]
        );

        BankAccount::updateOrCreate(
            ['user_id' => $rahmat->id],
            [
                'bank_name' => 'Bank Rakyat Indonesia (BRI)',
                'account_number' => '330101928374501',
                'account_holder_name' => 'Siti Aminah', // Intentional mismatch to test UI warning
                'is_verified' => false,
            ]
        );

        ComplianceAgreement::updateOrCreate(
            [
                'user_id' => $rahmat->id,
                'agreement_type' => 'SOP_KEMITRAAN',
            ],
            [
                'version' => '2.2',
                'agreed_at' => Carbon::now()->subHours(6),
            ]
        );

        // =========================================================================
        // 3. MITRA AGUS SETIAWAN (Kabupaten Jember - To Test ADM-15 Region Filter Dropdown)
        // =========================================================================
        $agus = User::updateOrCreate(
            ['email' => 'agus.setiawan@pijatin.test'],
            [
                'name' => 'Agus Setiawan',
                'nik' => '3509012301880004',
                'gender' => 'laki-laki',
                'phone' => '085612345678',
                'date_of_birth' => '1988-01-23',
                'role' => Role::Mitra,
                'status' => UserStatus::MenungguVerifikasi,
                'region_id' => $jember->id,
                'penalty_points' => 0,
                'password' => 'password',
                'email_verified_at' => Carbon::now()->subDays(3),
                'phone_verified_at' => Carbon::now()->subDays(3),
            ]
        );

        PartnerProfile::updateOrCreate(
            ['user_id' => $agus->id],
            [
                'region_id' => $jember->id,
                'secondary_region_id' => null,
                'address_text' => 'Jl. Kalimantan No. 34, Kec. Sumbersari, Kabupaten Jember',
                'coordinate' => ['lat' => -8.1724, 'lng' => 113.7208],
                'ktp_path' => 'partners/ktp/dummy_ktp_agus.svg',
                'selfie_path' => 'partners/selfie/dummy_selfie_agus.svg',
                'is_active' => false,
            ]
        );

        BankAccount::updateOrCreate(
            ['user_id' => $agus->id],
            [
                'bank_name' => 'Bank Mandiri',
                'account_number' => '1420019283741',
                'account_holder_name' => 'Agus Setiawan',
                'is_verified' => false,
            ]
        );

        ComplianceAgreement::updateOrCreate(
            [
                'user_id' => $agus->id,
                'agreement_type' => 'SOP_KEMITRAAN',
            ],
            [
                'version' => '2.2',
                'agreed_at' => Carbon::now()->subDay(),
            ]
        );
    }
}
