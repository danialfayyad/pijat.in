<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\BankAccount;
use App\Models\CustomerAddress;
use App\Models\PartnerProfile;
use App\Models\Region;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FinanceSeeder extends Seeder
{
    /**
     * Run the database seeds for testing financial workflows (Slice 9).
     */
    public function run(): void
    {
        $jember = Region::where('name', 'like', '%JEMBER%')->first() ?? Region::first();
        $surabaya = Region::where('name', 'like', '%SURABAYA%')->first() ?? Region::skip(1)->first() ?? $jember;
        $adminJember = User::where('email', 'admin@pijatin.test')->first();

        // =========================================================================
        // 1. DEMO MITRA (mitra@pijatin.test)
        // =========================================================================
        $mitra = User::updateOrCreate(
            ['email' => 'mitra@pijatin.test'],
            [
                'name' => 'Mitra Pijat.in',
                'nik' => '3509012304880001',
                'gender' => 'pria',
                'phone' => '081234567890',
                'role' => Role::Mitra,
                'status' => UserStatus::Terverifikasi,
                'region_id' => $jember->id,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );

        PartnerProfile::updateOrCreate(
            ['user_id' => $mitra->id],
            [
                'region_id' => $jember->id,
                'address_text' => 'Jl. Jawa No. 24, Kec. Sumbersari, Jember',
                'coordinate' => ['lat' => -8.1650, 'lng' => 113.7150],
                'current_coordinate' => ['lat' => -8.1650, 'lng' => 113.7150],
                'ktp_path' => 'ktp/demo-mitra.jpg',
                'selfie_path' => 'selfie/demo-mitra.jpg',
                'is_active' => true,
                'verified_at' => Carbon::now()->subDays(30),
                'verified_by' => $adminJember?->id,
            ]
        );

        $mitraBank = BankAccount::updateOrCreate(
            ['user_id' => $mitra->id],
            [
                'bank_name' => 'BCA',
                'account_number' => '8210987654',
                'account_holder_name' => 'Mitra Pijat.in',
                'is_verified' => true,
                'verified_at' => Carbon::now()->subDays(30),
                'verified_by' => $adminJember?->id,
            ]
        );

        // Wallet with Rp 850.000 available
        $mitraWallet = Wallet::updateOrCreate(
            ['user_id' => $mitra->id],
            [
                'balance' => 850000.00,
                'currency' => 'IDR',
            ]
        );

        // Mutasi Pendapatan Layanan (7 hari & 30 hari)
        WalletTransaction::firstOrCreate(
            [
                'wallet_id' => $mitraWallet->id,
                'reference_type' => 'order_earning',
                'reference_id' => 'ORD-JBR-001',
            ],
            [
                'user_id' => $mitra->id,
                'region_id' => $jember->id,
                'type' => 'credit',
                'amount' => 160000.00,
                'balance_before' => 0.00,
                'balance_after' => 160000.00,
                'description' => 'Pendapatan layanan pijat refleksi 60 menit #ORD-JBR-001',
                'created_at' => Carbon::now()->subDays(6),
            ]
        );

        WalletTransaction::firstOrCreate(
            [
                'wallet_id' => $mitraWallet->id,
                'reference_type' => 'order_earning',
                'reference_id' => 'ORD-JBR-002',
            ],
            [
                'user_id' => $mitra->id,
                'region_id' => $jember->id,
                'type' => 'credit',
                'amount' => 240000.00,
                'balance_before' => 160000.00,
                'balance_after' => 400000.00,
                'description' => 'Pendapatan layanan pijat tradisional 90 menit #ORD-JBR-002',
                'created_at' => Carbon::now()->subDays(3),
            ]
        );

        WalletTransaction::firstOrCreate(
            [
                'wallet_id' => $mitraWallet->id,
                'reference_type' => 'order_earning',
                'reference_id' => 'ORD-JBR-003',
            ],
            [
                'user_id' => $mitra->id,
                'region_id' => $jember->id,
                'type' => 'credit',
                'amount' => 450000.00,
                'balance_before' => 400000.00,
                'balance_after' => 850000.00,
                'description' => 'Pendapatan layanan paket lengkap relaksasi 120 menit #ORD-JBR-003',
                'created_at' => Carbon::now()->subDays(1),
            ]
        );

        // Sample Withdrawal Requests Mitra
        WithdrawalRequest::updateOrCreate(
            ['withdrawal_code' => 'WD-20261101-PAID1'],
            [
                'user_id' => $mitra->id,
                'bank_account_id' => $mitraBank->id,
                'region_id' => $jember->id,
                'amount' => 200000.00,
                'fee' => 0.00,
                'net_amount' => 200000.00,
                'bank_name' => 'BCA',
                'account_number' => '8210987654',
                'account_holder_name' => 'Mitra Pijat.in',
                'status' => WithdrawalRequest::STATUS_APPROVED,
                'transfer_reference' => 'TRF-BCA-99887766',
                'processed_by' => $adminJember?->id,
                'processed_at' => Carbon::now()->subDays(4),
                'created_at' => Carbon::now()->subDays(4),
            ]
        );

        WithdrawalRequest::updateOrCreate(
            ['withdrawal_code' => 'WD-20261102-REJ1'],
            [
                'user_id' => $mitra->id,
                'bank_account_id' => $mitraBank->id,
                'region_id' => $jember->id,
                'amount' => 50000.00,
                'fee' => 0.00,
                'net_amount' => 50000.00,
                'bank_name' => 'BCA',
                'account_number' => '8210987654',
                'account_holder_name' => 'Mitra Pijat.in',
                'status' => WithdrawalRequest::STATUS_REJECTED,
                'rejection_reason' => 'Sedang ada pemeliharaan sistem kliring bank penerima',
                'processed_by' => $adminJember?->id,
                'processed_at' => Carbon::now()->subDays(2),
                'created_at' => Carbon::now()->subDays(2),
            ]
        );

        // ACTIVE PENDING WITHDRAWAL READY FOR ADMIN APPROVAL/REJECTION TEST
        WithdrawalRequest::updateOrCreate(
            ['withdrawal_code' => 'WD-20261103-MTR01'],
            [
                'user_id' => $mitra->id,
                'bank_account_id' => $mitraBank->id,
                'region_id' => $jember->id,
                'amount' => 150000.00,
                'fee' => 0.00,
                'net_amount' => 150000.00,
                'bank_name' => 'BCA',
                'account_number' => '8210987654',
                'account_holder_name' => 'Mitra Pijat.in',
                'status' => WithdrawalRequest::STATUS_PENDING,
                'notes' => 'Penarikan hasil layanan pijat minggu pertama November',
                'created_at' => Carbon::now()->subHours(2),
            ]
        );

        // =========================================================================
        // 2. DEMO PELANGGAN (pelanggan@pijatin.test)
        // =========================================================================
        $pelanggan = User::updateOrCreate(
            ['email' => 'pelanggan@pijatin.test'],
            [
                'name' => 'Pelanggan Pijat.in',
                'phone' => '082100001111',
                'role' => Role::Pelanggan,
                'status' => UserStatus::Terverifikasi,
                'region_id' => $jember->id,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        CustomerAddress::updateOrCreate(
            ['user_id' => $pelanggan->id, 'is_primary' => true],
            [
                'region_id' => $jember->id,
                'label' => 'Rumah Utama',
                'address_text' => 'Perumahan Tidar Asri Blok C-12, Jember',
                'benchmark' => 'Depan taman perumahan',
                'latitude' => -8.1750,
                'longitude' => 113.7100,
                'coordinate' => ['lat' => -8.1750, 'lng' => 113.7100],
            ]
        );

        // Wallet Pelanggan Rp 350.000
        $custWallet = Wallet::updateOrCreate(
            ['user_id' => $pelanggan->id],
            [
                'balance' => 350000.00,
                'currency' => 'IDR',
            ]
        );

        WalletTransaction::firstOrCreate(
            [
                'wallet_id' => $custWallet->id,
                'reference_type' => 'topup',
                'reference_id' => 'INV-20261028-001',
            ],
            [
                'user_id' => $pelanggan->id,
                'region_id' => $jember->id,
                'type' => 'credit',
                'amount' => 500000.00,
                'balance_before' => 0.00,
                'balance_after' => 500000.00,
                'description' => 'Top-up saldo dompet via QRIS Xendit',
                'created_at' => Carbon::now()->subDays(5),
            ]
        );

        WalletTransaction::firstOrCreate(
            [
                'wallet_id' => $custWallet->id,
                'reference_type' => 'order_payment',
                'reference_id' => 'ORD-JBR-001',
            ],
            [
                'user_id' => $pelanggan->id,
                'region_id' => $jember->id,
                'type' => 'debit',
                'amount' => 200000.00,
                'balance_before' => 500000.00,
                'balance_after' => 300000.00,
                'description' => 'Pembayaran pesanan pijat refleksi #ORD-JBR-001',
                'created_at' => Carbon::now()->subDays(4),
            ]
        );

        WalletTransaction::firstOrCreate(
            [
                'wallet_id' => $custWallet->id,
                'reference_type' => 'order_refund',
                'reference_id' => 'ORD-JBR-CANCEL',
            ],
            [
                'user_id' => $pelanggan->id,
                'region_id' => $jember->id,
                'type' => 'credit',
                'amount' => 50000.00,
                'balance_before' => 300000.00,
                'balance_after' => 350000.00,
                'description' => 'Pengembalian dana (refund) pembatalan pesanan #ORD-JBR-CANCEL',
                'created_at' => Carbon::now()->subDays(2),
            ]
        );

        // ACTIVE PENDING CUSTOMER WITHDRAWAL READY FOR ADMIN TEST
        WithdrawalRequest::updateOrCreate(
            ['withdrawal_code' => 'WD-20261103-CUST01'],
            [
                'user_id' => $pelanggan->id,
                'region_id' => $jember->id,
                'amount' => 100000.00,
                'fee' => 0.00,
                'net_amount' => 100000.00,
                'bank_name' => 'Bank Mandiri',
                'account_number' => '1420011223344',
                'account_holder_name' => 'Pelanggan Pijat.in',
                'status' => WithdrawalRequest::STATUS_PENDING,
                'notes' => 'Tarik sisa saldo refund ke rekening Mandiri',
                'created_at' => Carbon::now()->subHour(),
            ]
        );

        // =========================================================================
        // 3. MITRA KEDUA (SURABAYA) UNTUK MULTI-WILAYAH & LINTAS WILAYAH TEST
        // =========================================================================
        $mitraSby = User::updateOrCreate(
            ['email' => 'budi.santoso@pijatin.test'],
            [
                'name' => 'Budi Santoso',
                'nik' => '3578011205850002',
                'gender' => 'pria',
                'phone' => '081399887766',
                'role' => Role::Mitra,
                'status' => UserStatus::Terverifikasi,
                'region_id' => $surabaya->id,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        PartnerProfile::updateOrCreate(
            ['user_id' => $mitraSby->id],
            [
                'region_id' => $surabaya->id,
                'address_text' => 'Jl. Rungkut Madya No. 8, Surabaya',
                'coordinate' => ['lat' => -7.3200, 'lng' => 112.7800],
                'current_coordinate' => ['lat' => -7.3200, 'lng' => 112.7800],
                'ktp_path' => 'ktp/budi-ktp.jpg',
                'selfie_path' => 'selfie/budi-selfie.jpg',
                'is_active' => true,
                'verified_at' => Carbon::now()->subDays(15),
            ]
        );

        $sbyBank = BankAccount::updateOrCreate(
            ['user_id' => $mitraSby->id],
            [
                'bank_name' => 'BRI',
                'account_number' => '001901056789501',
                'account_holder_name' => 'Budi Santoso',
                'is_verified' => true,
                'verified_at' => Carbon::now()->subDays(15),
            ]
        );

        $sbyWallet = Wallet::updateOrCreate(
            ['user_id' => $mitraSby->id],
            [
                'balance' => 640000.00,
                'currency' => 'IDR',
            ]
        );

        WalletTransaction::firstOrCreate(
            [
                'wallet_id' => $sbyWallet->id,
                'reference_type' => 'order_earning',
                'reference_id' => 'ORD-SBY-001',
            ],
            [
                'user_id' => $mitraSby->id,
                'region_id' => $surabaya->id,
                'type' => 'credit',
                'amount' => 640000.00,
                'balance_before' => 0.00,
                'balance_after' => 640000.00,
                'description' => 'Pendapatan layanan pijat kebugaran Surabaya #ORD-SBY-001',
                'created_at' => Carbon::now()->subDays(2),
            ]
        );

        WithdrawalRequest::updateOrCreate(
            ['withdrawal_code' => 'WD-20261103-SBY01'],
            [
                'user_id' => $mitraSby->id,
                'bank_account_id' => $sbyBank->id,
                'region_id' => $surabaya->id,
                'amount' => 300000.00,
                'fee' => 0.00,
                'net_amount' => 300000.00,
                'bank_name' => 'BRI',
                'account_number' => '001901056789501',
                'account_holder_name' => 'Budi Santoso',
                'status' => WithdrawalRequest::STATUS_PENDING,
                'notes' => 'Pencairan saldo Surabaya',
                'created_at' => Carbon::now()->subMinutes(45),
            ]
        );
    }
}
