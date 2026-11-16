<?php

namespace App\Services;

use App\Models\WithdrawalRequest;
use DomainException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditDisbursementService
{
    /**
     * Map bank names/codes to Xendit disbursement bank_code values.
     *
     * @var array<string, string>
     */
    protected const BANK_CODE_MAP = [
        'BCA' => 'BCA',
        'BANK CENTRAL ASIA' => 'BCA',
        'MANDIRI' => 'MANDIRI',
        'BANK MANDIRI' => 'MANDIRI',
        'BRI' => 'BRI',
        'BANK RAKYAT INDONESIA' => 'BRI',
        'BNI' => 'BNI',
        'BANK NEGARA INDONESIA' => 'BNI',
        'BSI' => 'BSI',
        'BANK SYARIAH INDONESIA' => 'BSI',
        'CIMB' => 'CIMB',
        'CIMB NIAGA' => 'CIMB',
        'PERMATA' => 'PERMATA',
        'BANK PERMATA' => 'PERMATA',
        'DANAMON' => 'DANAMON',
        'BANK DANAMON' => 'DANAMON',
        'OCBC' => 'OCBC',
        'OCBC NISP' => 'OCBC',
        'BTN' => 'BTN',
        'BTPN' => 'BTPN',
        'JENIUS' => 'BTPN',
        'SEABANK' => 'SEABANK',
        'JAGO' => 'JAGO',
        'BANK JAGO' => 'JAGO',
    ];

    /**
     * Daftar bank nasional populer yang umum dipakai oleh mitra & pelanggan.
     *
     * @var array<string, int>
     */
    public const POPULAR_BANKS = [
        'BCA' => 1,
        'MANDIRI' => 2,
        'BRI' => 3,
        'BNI' => 4,
        'BSI' => 5,
        'CIMB' => 6,
        'PERMATA' => 7,
        'DANAMON' => 8,
        'BTPN' => 9,      // Jenius / BTPN
        'JAGO' => 10,     // Bank Jago
        'SEABANK' => 11,  // SeaBank (Shopee)
        'NEO' => 12,      // Bank Neo Commerce
        'ALLO' => 13,     // Allo Bank
        'PANIN' => 14,
        'OCBC' => 15,     // OCBC NISP
        'BTN' => 16,
        'MAYBANK' => 17,
        'SINARMAS' => 18,
        'MEGA' => 19,
        'BCA_SYR' => 20,
    ];

    /**
     * Determine whether Xendit API key is configured.
     */
    public function isConfigured(): bool
    {
        return ! empty(config('services.xendit.secret_key'));
    }

    /**
     * Resolve Xendit bank_code from the stored bank name.
     */
    public function resolveBankCode(string $bankName): string
    {
        $normalized = strtoupper(trim($bankName));

        if (isset(self::BANK_CODE_MAP[$normalized])) {
            return self::BANK_CODE_MAP[$normalized];
        }

        foreach (self::BANK_CODE_MAP as $needle => $code) {
            if (str_contains($normalized, $needle)) {
                return $code;
            }
        }

        return preg_replace('/[^A-Z0-9_]/', '', $normalized) ?: 'BCA';
    }

    /**
     * Create a Xendit disbursement payout for an approved withdrawal request.
     *
     * @return array{id: string, status: string, external_id: string}
     */
    public function disburse(WithdrawalRequest $withdrawal): array
    {
        $secretKey = (string) config('services.xendit.secret_key');
        if ($secretKey === '') {
            throw new DomainException('Xendit secret key belum dikonfigurasi.');
        }

        $amount = (int) round((float) $withdrawal->net_amount);
        if ($amount <= 0) {
            throw new DomainException('Nominal pencairan tidak valid.');
        }

        $payload = [
            'external_id' => $withdrawal->withdrawal_code,
            'amount' => $amount,
            'bank_code' => $this->resolveBankCode((string) $withdrawal->bank_name),
            'account_holder_name' => $withdrawal->account_holder_name,
            'account_number' => preg_replace('/\s+/', '', (string) $withdrawal->account_number),
            'description' => "Penarikan saldo Pijat.In ({$withdrawal->withdrawal_code})",
        ];

        $response = Http::withBasicAuth($secretKey, '')
            ->withHeaders(['X-IDEMPOTENCY-KEY' => $withdrawal->withdrawal_code])
            ->timeout(15)
            ->post('https://api.xendit.co/disbursements', $payload);

        if (! $response->successful()) {
            $body = $response->json();
            $message = $body['message'] ?? 'Gagal memproses transfer otomatis ke Xendit.';
            Log::warning('Xendit disbursement failed', [
                'withdrawal_code' => $withdrawal->withdrawal_code,
                'status' => $response->status(),
                'response' => $body,
            ]);

            throw new DomainException("Xendit Disbursement gagal: {$message}");
        }

        $data = $response->json();

        return [
            'id' => (string) ($data['id'] ?? ''),
            'status' => strtoupper((string) ($data['status'] ?? 'PENDING')),
            'external_id' => (string) ($data['external_id'] ?? $withdrawal->withdrawal_code),
        ];
    }

    /**
     * Fetch supported disbursement banks from Xendit API, filtered to popular national banks.
     *
     * @return array<int, array{name: string, code: string, sort_order: int}>
     */
    public function getAvailableBanks(bool $popularOnly = true): array
    {
        $secretKey = (string) config('services.xendit.secret_key');
        if ($secretKey === '') {
            throw new DomainException('Xendit secret key belum dikonfigurasi.');
        }

        $response = Http::withBasicAuth($secretKey, '')
            ->timeout(15)
            ->get('https://api.xendit.co/available_disbursements_banks');

        if (! $response->successful()) {
            $message = $response->json()['message'] ?? 'Gagal mengambil daftar bank dari Xendit.';
            throw new DomainException("Xendit API error: {$message}");
        }

        $banks = $response->json();
        if (! is_array($banks)) {
            return [];
        }

        $collection = collect($banks)
            ->filter(fn ($item) => is_array($item) && ! empty($item['code']) && ! empty($item['name']))
            ->map(function ($item) {
                $code = strtoupper(trim((string) $item['code']));
                $sortOrder = self::POPULAR_BANKS[$code] ?? 999;

                return [
                    'name' => (string) $item['name'],
                    'code' => $code,
                    'sort_order' => $sortOrder,
                ];
            });

        if ($popularOnly) {
            $collection = $collection->filter(fn ($item) => isset(self::POPULAR_BANKS[$item['code']]))
                ->sortBy('sort_order');
        }

        return $collection->values()->all();
    }
}
