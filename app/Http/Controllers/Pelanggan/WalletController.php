<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\TopupInvoice;
use App\Services\TopupService;
use App\Services\WalletService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService,
        protected TopupService $topupService
    ) {}

    /**
     * Display the customer wallet screen with balance and transactions (CUS-16).
     */
    public function index(Request $request): View
    {
        $user = $request->user()->load('primaryAddress.region');
        $wallet = $user->getOrCreateWallet();
        $transactions = $wallet->transactions()->with('region')->paginate(15);

        return view('pelanggan.wallet.index', compact('user', 'wallet', 'transactions'));
    }

    /**
     * Show the top-up form (CUS-16).
     */
    public function create(Request $request): View
    {
        $user = $request->user()->load('primaryAddress.region');

        return view('pelanggan.wallet.topup', compact('user'));
    }

    /**
     * Process top-up invoice generation (CUS-16, DEC-02).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10000', 'max:10000000'],
            'payment_method' => ['required', 'string', 'in:QRIS,BCA_VA,MANDIRI_VA,BNI_VA,BRI_VA'],
        ], [
            'amount.required' => 'Nominal pengisian saldo wajib diisi.',
            'amount.min' => 'Nominal minimal pengisian saldo adalah Rp 10.000.',
            'amount.max' => 'Nominal maksimal pengisian saldo adalah Rp 10.000.000.',
            'payment_method.required' => 'Pilih metode pembayaran.',
        ]);

        try {
            $invoice = $this->topupService->createInvoice(
                user: $request->user(),
                amount: (float) $validated['amount'],
                paymentMethod: $validated['payment_method']
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
            $this->topupService->markPaid($invoice, 'SIMULATED-'.strtoupper(Str::random(10)));

            return redirect()->route('pelanggan.wallet.index')
                ->with('status', "Pembayaran senilai {$invoice->formatted_amount} berhasil! Saldo dompet Anda telah diperbarui.");
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
