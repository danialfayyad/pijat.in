<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VoucherController extends Controller
{
    /**
     * Tampilkan daftar master voucher diskon nasional.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status', 'semua');
        $type = $request->input('type');
        $search = trim($request->input('search', ''));

        $query = Voucher::query();

        // Filter tab status
        if ($status === 'aktif') {
            $query->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', Carbon::now());
                });
        } elseif ($status === 'kedaluwarsa') {
            $query->where('expires_at', '<', Carbon::now());
        } elseif ($status === 'nonaktif') {
            $query->where('is_active', false);
        }

        // Filter tipe diskon
        if ($type && in_array($type, [Voucher::TYPE_PERCENTAGE, Voucher::TYPE_FIXED], true)) {
            $query->where('type', $type);
        }

        // Pencarian kode atau nama
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $vouchers = $query->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $totalCount = Voucher::count();
        $activeCount = Voucher::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', Carbon::now());
            })->count();
        $totalUsages = VoucherUsage::count();
        $totalDiscountGiven = (float) VoucherUsage::sum('discount_amount');

        return view('superadmin.vouchers.index', compact(
            'vouchers',
            'status',
            'type',
            'search',
            'totalCount',
            'activeCount',
            'totalUsages',
            'totalDiscountGiven'
        ));
    }

    /**
     * Simpan voucher diskon baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:vouchers,code'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'type' => ['required', 'in:percentage,fixed'],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('type') === Voucher::TYPE_PERCENTAGE && $value > 100) {
                        $fail('Nilai diskon persentase tidak boleh lebih dari 100%.');
                    }
                },
            ],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'quota' => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['required', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['min_order_amount'] = $validated['min_order_amount'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);

        Voucher::create($validated);

        return redirect()->route('superadmin.vouchers.index')
            ->with('status', 'Voucher diskon '.$validated['code'].' berhasil ditambahkan.');
    }

    /**
     * Perbarui data voucher diskon.
     */
    public function update(Request $request, Voucher $voucher): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('vouchers', 'code')->ignore($voucher->id)],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'type' => ['required', 'in:percentage,fixed'],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('type') === Voucher::TYPE_PERCENTAGE && $value > 100) {
                        $fail('Nilai diskon persentase tidak boleh lebih dari 100%.');
                    }
                },
            ],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'quota' => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['required', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['min_order_amount'] = $validated['min_order_amount'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active', true);

        $voucher->update($validated);

        return redirect()->route('superadmin.vouchers.index')
            ->with('status', 'Voucher diskon '.$voucher->code.' berhasil diperbarui.');
    }

    /**
     * Ubah status aktif / nonaktif voucher.
     */
    public function toggle(Voucher $voucher): RedirectResponse
    {
        $voucher->update([
            'is_active' => ! $voucher->is_active,
        ]);

        $statusText = $voucher->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('status', 'Voucher '.$voucher->code.' berhasil '.$statusText.'.');
    }

    /**
     * Hapus voucher diskon jika belum pernah digunakan.
     */
    public function destroy(Voucher $voucher): RedirectResponse
    {
        if ($voucher->usages()->exists()) {
            $voucher->update(['is_active' => false]);

            return back()->with('status', 'Voucher '.$voucher->code.' memiliki riwayat transaksi penggunaan, sehingga statusnya dinonaktifkan demi menjaga integritas data laporan.');
        }

        $code = $voucher->code;
        $voucher->delete();

        return redirect()->route('superadmin.vouchers.index')
            ->with('status', 'Voucher '.$code.' berhasil dihapus.');
    }
}
