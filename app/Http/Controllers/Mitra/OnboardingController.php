<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Services\PartnerOnboardingService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function __construct(
        protected PartnerOnboardingService $onboardingService
    ) {}

    /**
     * Show the mitra onboarding form (MIT-02).
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isTerverifikasi()) {
            return redirect()->route('mitra.dashboard');
        }

        if ($user->isMenungguVerifikasi()) {
            return redirect()->route('mitra.status');
        }

        $activeRegions = Region::active()->get(['id', 'name', 'timezone']);
        $partnerProfile = $user->partnerProfile;
        $bankAccount = $user->primaryBankAccount;

        return view('mitra.onboarding', compact('user', 'activeRegions', 'partnerProfile', 'bankAccount'));
    }

    /**
     * Submit partner onboarding form.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'nik' => ['required', 'digits:16', Rule::unique('users', 'nik')->ignore($user->id)],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'address_text' => ['required', 'string', 'max:500'],
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_holder_name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:25'],
            'ktp_file' => [$user->partnerProfile?->ktp_path ? 'nullable' : 'required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:5120'],
            'selfie_file' => [$user->partnerProfile?->selfie_path ? 'nullable' : 'required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:5120'],
            'skck_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:5120'],
            'bank_proof_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:5120'],
            'sop_agreement' => ['accepted'],
        ], [
            'nik.required' => 'Nomor Induk Kependudukan (NIK) wajib diisi.',
            'nik.digits' => 'NIK harus berjumlah tepat 16 digit.',
            'nik.unique' => 'NIK ini telah terdaftar di sistem pijat.in.',
            'latitude.required' => 'Silakan sematkan pin lokasi domisili Anda pada peta.',
            'address_text.required' => 'Alamat lengkap domisili wajib diisi.',
            'ktp_file.required' => 'Foto KTP wajib diunggah.',
            'selfie_file.required' => 'Foto selfie bersama KTP wajib diunggah.',
            'sop_agreement.accepted' => 'Anda wajib menyetujui SOP & Kode Etik Kemitraan pijat.in.',
        ]);

        $payload = [
            'nik' => $validated['nik'],
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
            'address_text' => $validated['address_text'],
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'account_holder_name' => $validated['account_holder_name'],
            'phone' => $validated['phone'] ?? $user->phone,
            'ktp_file' => $request->file('ktp_file') ?? $user->partnerProfile?->ktp_path,
            'selfie_file' => $request->file('selfie_file') ?? $user->partnerProfile?->selfie_path,
            'skck_file' => $request->file('skck_file') ?? $user->partnerProfile?->skck_path,
            'bank_proof_file' => $request->file('bank_proof_file') ?? $user->primaryBankAccount?->proof_path,
        ];

        try {
            $this->onboardingService->submitOnboarding($user, $payload);

            return redirect()->route('mitra.status')
                ->with('status', 'Berkas pendaftaran Anda telah berhasil dikirimkan dan sedang dalam antrean verifikasi Admin Wilayah.');
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['latitude' => $e->getMessage()]);
        }
    }

    /**
     * Show current account verification status (MIT-03).
     */
    public function status(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isBelumLengkap()) {
            return redirect()->route('mitra.onboarding');
        }

        if ($user->isTerverifikasi()) {
            return redirect()->route('mitra.dashboard');
        }

        return view('mitra.status', [
            'user' => $user->load('partnerProfile.region', 'primaryBankAccount'),
        ]);
    }
}
