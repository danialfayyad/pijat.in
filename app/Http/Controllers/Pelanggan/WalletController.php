<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Parameter;
use App\Models\TopupInvoice;
use App\Models\WithdrawalRequest;
use App\Services\NotificationService;
use App\Services\TopupService;
use App\Services\WalletService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService,
        protected TopupService $topupService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Display the customer wallet screen with balance and transactions (CUS-16).
     */
    public function index(Request $request): View
    {
        $user = $request->user()->load('primaryAddress.region');
        $wallet = $user->getOrCreateWallet();

        $selectedMonth = $request->string('month', 'semua')->toString();
        $selectedType = $request->string('type', 'semua')->toString();

        $transactionsQuery = $wallet->transactions()->with('region')->latest();

        if (preg_match('/^(\d{4})-(\d{2})$/', $selectedMonth, $matches) === 1) {
            $transactionsQuery
                ->whereYear('created_at', (int) $matches[1])
                ->whereMonth('created_at', (int) $matches[2]);
        } else {
            $selectedMonth = 'semua';
        }

        match ($selectedType) {
            'masuk' => $transactionsQuery->where('type', 'credit'),
            'keluar' => $transactionsQuery->where('type', 'debit'),
            'penarikan' => $transactionsQuery->where('reference_type', 'customer_withdrawal'),
            default => $selectedType = 'semua',
        };

        $transactions = $transactionsQuery->paginate(15)->withQueryString();

        $monthOptions = collect([
            ['value' => 'semua', 'label' => 'Semua'],
        ]);

        for ($i = 0; $i < 6; $i++) {
            $date = now()->startOfMonth()->subMonths($i);
            $monthOptions->push([
                'value' => $date->format('Y-m'),
                'label' => $date->translatedFormat('M Y'),
            ]);
        }

        $withdrawalRequests = WithdrawalRequest::where('user_id', $user->id)->latest()->take(5)->get();

        return view('pelanggan.wallet.index', compact(
            'user',
            'wallet',
            'transactions',
            'withdrawalRequests',
            'monthOptions',
            'selectedMonth',
            'selectedType'
        ));
    }

    /**
     * Show the top-up form (CUS-16).
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user()->load('primaryAddress.region');

        // Jika pelanggan memiliki tagihan top-up yang masih aktif (ongoing) dan tidak sedang dalam mode ubah metode
        if (! $request->boolean('change')) {
            $pendingInvoice = TopupInvoice::where('user_id', $user->id)
                ->whereNull('order_id')
                ->where('status', 'PENDING')
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if ($pendingInvoice) {
                return redirect()->route('pelanggan.wallet.invoice', $pendingInvoice->invoice_number)
                    ->with('info', 'Anda memiliki pembayaran top up yang sedang berlangsung. Selesaikan pembayaran atau pilih Ubah Metode Pembayaran.');
            }
        }

        return view('pelanggan.wallet.topup', compact('user'));
    }

    /**
     * Process top-up invoice generation (CUS-16, DEC-02).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10000', 'max:10000000'],
            'payment_method' => ['nullable', 'string', 'max:50'],
        ], [
            'amount.required' => 'Nominal pengisian saldo wajib diisi.',
            'amount.min' => 'Nominal minimal pengisian saldo adalah Rp 10.000.',
            'amount.max' => 'Nominal maksimal pengisian saldo adalah Rp 10.000.000.',
        ]);

        try {
            $invoice = $this->topupService->createInvoice(
                user: $request->user(),
                amount: (float) $validated['amount'],
                paymentMethod: $validated['payment_method'] ?? 'XENDIT'
            );

            return redirect()->route('pelanggan.wallet.invoice', $invoice->invoice_number)
                ->with('status', 'Tagihan isi saldo berhasil dibuat. Silakan selesaikan pembayaran sebelum batas waktu.');
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['amount' => $e->getMessage()]);
        }
    }

    /**
     * Display the payment invoice with countdown and payment instructions (SYS-05).
     */
    public function invoice(Request $request, string $invoiceNumber): View|RedirectResponse
    {
        $invoice = TopupInvoice::where('invoice_number', $invoiceNumber)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Auto-sync status from Xendit if pending and has Xendit invoice reference
        if ($invoice->isPending() && ! empty($invoice->xendit_invoice_id)) {
            $this->topupService->checkAndSyncXenditStatus($invoice);
            $invoice->refresh();
        }

        return view('pelanggan.wallet.invoice', compact('invoice'));
    }

    /**
     * Simulation action for instant payment verification in testing / demo mode.
     */
    public function simulatePay(Request $request, string $invoiceNumber): RedirectResponse
    {
        $invoice = TopupInvoice::where('invoice_number', $invoiceNumber)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        try {
            $secretKey = config('services.xendit.secret_key');

            // 1. Jika ini invoice Xendit sungguhan di mode sandbox, panggil simulator resmi Xendit lewat API
            if (! empty($secretKey) && ! empty($invoice->xendit_invoice_id) && ! str_starts_with($invoice->xendit_invoice_id, 'SIMULATED-')) {
                try {
                    // Panggil API Simulasi Resmi Xendit
                    Http::withBasicAuth($secretKey, '')
                        ->timeout(10)
                        ->post("https://api.xendit.co/v2/invoices/{$invoice->xendit_invoice_id}/simulate_payment");
                } catch (\Throwable $e) {
                    Log::warning('Xendit simulate payment error: '.$e->getMessage());
                }
            }

            // 2. Tandai lunas di database lokal dan tambah saldo
            $this->topupService->markPaid($invoice, $invoice->xendit_invoice_id ?: 'SIMULATED-'.strtoupper(Str::random(10)));

            if ($invoice->order_id) {
                return redirect()->route('pelanggan.orders.show', $invoice->order_id)
                    ->with('success', "Pembayaran tagihan sebesar {$invoice->formatted_amount} berhasil! Pesanan Anda telah lunas dan sistem sedang mencarikan mitra terapis.");
            }

            // Arahkan kembali ke halaman invoice agar pengguna melihat animasi "Pembayaran Berhasil"
            return redirect()->route('pelanggan.wallet.invoice', $invoice->invoice_number)
                ->with('status', "Pembayaran senilai {$invoice->formatted_amount} berhasil diverifikasi!");
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Check status directly from Xendit API without waiting for webhooks (CUS-09, Localhost support).
     */
    public function checkStatus(Request $request, string $invoiceNumber): RedirectResponse|JsonResponse
    {
        $invoice = TopupInvoice::where('invoice_number', $invoiceNumber)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $isPaid = $this->topupService->checkAndSyncXenditStatus($invoice);

        if ($request->wantsJson()) {
            return response()->json([
                'paid' => $isPaid,
                'status' => $invoice->fresh()->status,
                'message' => $isPaid ? 'Pembayaran berhasil terkonfirmasi!' : 'Pembayaran belum terdeteksi di Xendit.',
            ]);
        }

        if ($isPaid) {
            if ($invoice->order_id) {
                return redirect()->route('pelanggan.orders.show', $invoice->order_id)
                    ->with('success', 'Pembayaran terkonfirmasi oleh Xendit! Pesanan Anda telah lunas dan sistem sedang mencarikan mitra terapis.');
            }

            return redirect()->route('pelanggan.wallet.invoice', $invoice->invoice_number)
                ->with('status', "Pembayaran senilai {$invoice->formatted_amount} terkonfirmasi oleh Xendit! Saldo dompet Anda telah bertambah.");
        }

        return back()->with('warning', 'Pembayaran belum terdeteksi di Xendit. Jika Anda sudah menyelesaikan pembayaran di Xendit, silakan tunggu beberapa detik lalu tekan tombol ini kembali.');
    }

    /**
     * Cancel an ongoing pending wallet top-up invoice.
     */
    public function cancel(Request $request, string $invoiceNumber): RedirectResponse
    {
        $invoice = TopupInvoice::where('invoice_number', $invoiceNumber)
            ->where('user_id', $request->user()->id)
            ->where('status', 'PENDING')
            ->firstOrFail();

        $invoice->update(['status' => 'EXPIRED']);

        return redirect()->route('pelanggan.wallet.index')
            ->with('status', 'Tagihan pengisian saldo berhasil dibatalkan.');
    }

    /**
     * Show customer withdrawal form (CUS-17).
     */
    public function withdraw(Request $request): View|RedirectResponse
    {
        $user = $request->user()->load('primaryAddress.region');
        $wallet = $user->getOrCreateWallet();

        if (! $user->primaryAddress) {
            return redirect()->route('pelanggan.addresses.index')
                ->with('error', 'Silakan tentukan alamat utama domisili terlebih dahulu sebelum mengajukan penarikan saldo.');
        }

        $minAmount = (float) Parameter::get('min_withdrawal_amount', 50000);
        $maxAmount = (float) Parameter::get('max_withdrawal_amount', 5000000);
        $fee = (float) Parameter::get('withdrawal_fee_amount', 0);

        $pendingWithdrawal = WithdrawalRequest::where('user_id', $user->id)
            ->where('status', WithdrawalRequest::STATUS_PENDING)
            ->latest()
            ->first();
        $hasPending = $pendingWithdrawal !== null;

        return view('pelanggan.wallet.withdraw', compact('user', 'wallet', 'minAmount', 'maxAmount', 'fee', 'hasPending', 'pendingWithdrawal'));
    }

    /**
     * Process customer withdrawal submission (CUS-17).
     */
    public function storeWithdrawal(Request $request): RedirectResponse
    {
        $user = $request->user()->load('primaryAddress');
        $wallet = $user->getOrCreateWallet();

        if (! $user->primaryAddress) {
            return redirect()->route('pelanggan.addresses.index')
                ->with('error', 'Alamat utama wajib diisi untuk penetapan wilayah operasional transaksi.');
        }

        $hasPending = WithdrawalRequest::where('user_id', $user->id)
            ->where('status', WithdrawalRequest::STATUS_PENDING)
            ->exists();

        if ($hasPending) {
            return redirect()->route('pelanggan.wallet.index')
                ->with('error', 'Anda masih memiliki permohonan penarikan dana yang sedang menunggu verifikasi admin.');
        }

        $minAmount = (float) Parameter::get('min_withdrawal_amount', 50000);
        $maxAmount = (float) Parameter::get('max_withdrawal_amount', 5000000);
        $fee = (float) Parameter::get('withdrawal_fee_amount', 0);

        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:100', function ($attribute, $value, $fail) {
                if (! Bank::isAllowed($value)) {
                    $fail('Bank yang dipilih tidak tersedia atau tidak diizinkan oleh sistem.');
                }
            }],
            'account_number' => ['required', 'string', 'max:50'],
            'account_holder_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', "min:{$minAmount}", "max:{$maxAmount}"],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'bank_name.required' => 'Nama bank wajib diisi.',
            'account_number.required' => 'Nomor rekening tujuan wajib diisi.',
            'account_holder_name.required' => 'Nama pemilik rekening wajib diisi.',
            'amount.min' => 'Nominal penarikan minimal Rp '.number_format($minAmount, 0, ',', '.').'.',
            'amount.max' => 'Nominal penarikan maksimal Rp '.number_format($maxAmount, 0, ',', '.').'.',
        ]);

        $amount = (float) $validated['amount'];

        if ($amount > (float) $wallet->balance) {
            return back()->withInput()->withErrors([
                'amount' => 'Saldo dompet tidak mencukupi untuk melakukan penarikan senilai Rp '.number_format($amount, 0, ',', '.').'.',
            ]);
        }

        $netAmount = max(0, $amount - $fee);
        $regionId = $user->primaryAddress->region_id;

        DB::transaction(function () use ($user, $amount, $fee, $netAmount, $regionId, $validated) {
            $withdrawal = WithdrawalRequest::create([
                'withdrawal_code' => WithdrawalRequest::generateCode(),
                'user_id' => $user->id,
                'region_id' => $regionId,
                'amount' => $amount,
                'fee' => $fee,
                'net_amount' => $netAmount,
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'account_holder_name' => $validated['account_holder_name'],
                'status' => WithdrawalRequest::STATUS_PENDING,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Deduct customer wallet immediately
            $this->walletService->debit(
                $user,
                $amount,
                'customer_withdrawal',
                (string) $withdrawal->id,
                "Penarikan saldo ke {$validated['bank_name']} ({$withdrawal->withdrawal_code})",
                $regionId
            );

            // 1. Notify Customer itself
            $this->notificationService->sendToUser(
                $user,
                'withdrawal_requested',
                'Pengajuan Penarikan Saldo Berhasil',
                'Permohonan penarikan saldo sebesar Rp '.number_format($amount, 0, ',', '.')." ({$withdrawal->withdrawal_code}) telah berhasil diajukan dan sedang menunggu verifikasi admin wilayah.",
                [
                    'withdrawal_id' => $withdrawal->id,
                    'withdrawal_code' => $withdrawal->withdrawal_code,
                    'amount' => $amount,
                    'action_url' => route('pelanggan.wallet.index'),
                ],
                'normal'
            );

            // 2. Notify Regional Admins
            $this->notificationService->notifyWithdrawalRequested($withdrawal);
        });

        return redirect()->route('pelanggan.wallet.index')
            ->with('status', 'Permohonan penarikan saldo berhasil diajukan dan sedang dalam antrean verifikasi admin wilayah.');
    }

    /**
     * Cancel pending customer withdrawal and refund wallet balance immediately (CUS-17).
     */
    public function cancelWithdrawal(Request $request, WithdrawalRequest $withdrawal): RedirectResponse
    {
        $user = $request->user();

        if ($withdrawal->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk membatalkan penarikan ini.');
        }

        return DB::transaction(function () use ($withdrawal, $user) {
            $lockedWithdrawal = WithdrawalRequest::where('id', $withdrawal->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedWithdrawal->isPending()) {
                return redirect()->route('pelanggan.wallet.index')
                    ->with('error', 'Permohonan penarikan dana ini tidak dapat dibatalkan karena sudah diproses atau dibatalkan sebelumnya.');
            }

            $lockedWithdrawal->update([
                'status' => WithdrawalRequest::STATUS_CANCELLED,
                'processed_at' => now(),
            ]);

            // Refund customer wallet balance atomically (SYS-05)
            $this->walletService->credit(
                $user,
                (float) $lockedWithdrawal->amount,
                'withdrawal_refund',
                (string) $lockedWithdrawal->id,
                "Pengembalian saldo: Pembatalan penarikan ({$lockedWithdrawal->withdrawal_code})",
                $lockedWithdrawal->region_id
            );

            // Notify Customer
            $this->notificationService->sendToUser(
                $user,
                'withdrawal_cancelled',
                'Penarikan Saldo Dibatalkan',
                "Pengajuan penarikan dana {$lockedWithdrawal->withdrawal_code} senilai Rp ".number_format($lockedWithdrawal->amount, 0, ',', '.').' berhasil dibatalkan dan saldo telah dikembalikan ke dompet Anda.',
                [
                    'withdrawal_id' => $lockedWithdrawal->id,
                    'withdrawal_code' => $lockedWithdrawal->withdrawal_code,
                    'amount' => (float) $lockedWithdrawal->amount,
                    'action_url' => route('pelanggan.wallet.index'),
                ],
                'normal'
            );

            return redirect()->route('pelanggan.wallet.index')
                ->with('status', "Pengajuan penarikan dana {$lockedWithdrawal->withdrawal_code} senilai Rp ".number_format($lockedWithdrawal->amount, 0, ',', '.').' berhasil dibatalkan dan saldo telah dikembalikan ke dompet Anda.');
        });
    }
}
