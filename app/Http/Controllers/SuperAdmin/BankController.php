<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BankController extends Controller
{
    /**
     * Tampilkan daftar master rekening & saluran bank pencairan.
     */
    public function index(Request $request): View
    {
        $query = Bank::query();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('account_number', 'like', "%{$search}%")
                    ->orWhere('account_holder_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $banks = $query->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->withQueryString();

        $totalCount = Bank::count();
        $activeCount = Bank::where('is_active', true)->count();
        $inactiveCount = Bank::where('is_active', false)->count();

        return view('superadmin.banks.index', compact(
            'banks',
            'totalCount',
            'activeCount',
            'inactiveCount'
        ));
    }

    /**
     * Simpan data bank / saluran baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:banks,code'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_holder_name' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama bank wajib diisi.',
            'code.required' => 'Kode bank wajib diisi.',
            'code.unique' => 'Kode bank ini sudah terdaftar.',
            'code.alpha_dash' => 'Kode bank hanya boleh berisi huruf, angka, tanda strip, atau garis bawah.',
        ]);

        $name = trim($validated['name']);
        $code = strtoupper(trim($validated['code']));

        Bank::create([
            'name' => $name,
            'code' => $code,
            'account_number' => ! empty($validated['account_number']) ? trim($validated['account_number']) : null,
            'account_holder_name' => ! empty($validated['account_holder_name']) ? trim($validated['account_holder_name']) : null,
            'notes' => ! empty($validated['notes']) ? trim($validated['notes']) : null,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('superadmin.banks.index')
            ->with('status', "Bank '{$name}' ({$code}) berhasil ditambahkan ke daftar saluran pencairan.");
    }

    /**
     * Perbarui data bank / saluran yang dipilih.
     */
    public function update(Request $request, Bank $bank): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('banks', 'code')->ignore($bank->id)],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_holder_name' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama bank wajib diisi.',
            'code.required' => 'Kode bank wajib diisi.',
            'code.unique' => 'Kode bank ini sudah digunakan oleh bank lain.',
            'code.alpha_dash' => 'Kode bank hanya boleh berisi huruf, angka, tanda strip, atau garis bawah.',
        ]);

        $name = trim($validated['name']);
        $code = strtoupper(trim($validated['code']));

        $bank->update([
            'name' => $name,
            'code' => $code,
            'account_number' => ! empty($validated['account_number']) ? trim($validated['account_number']) : null,
            'account_holder_name' => ! empty($validated['account_holder_name']) ? trim($validated['account_holder_name']) : null,
            'notes' => ! empty($validated['notes']) ? trim($validated['notes']) : null,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('superadmin.banks.index')
            ->with('status', "Data bank '{$name}' ({$code}) berhasil diperbarui.");
    }

    /**
     * Toggle status aktif/nonaktif bank.
     */
    public function toggle(Bank $bank): RedirectResponse
    {
        $bank->update([
            'is_active' => ! $bank->is_active,
        ]);

        $statusText = $bank->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('superadmin.banks.index')
            ->with('status', "Bank '{$bank->name}' berhasil {$statusText}.");
    }

    /**
     * Hapus data bank. Jika memiliki riwayat transaksi, bank otomatis dinonaktifkan demi integritas.
     */
    public function destroy(Bank $bank): RedirectResponse
    {
        if ($bank->withdrawalRequests()->exists()) {
            $bank->update(['is_active' => false]);

            return redirect()->route('superadmin.banks.index')
                ->with('status', "Bank '{$bank->name}' memiliki riwayat pencairan dana. Status otomatis dinonaktifkan demi menjaga integritas data.");
        }

        $name = $bank->name;
        $bank->delete();

        return redirect()->route('superadmin.banks.index')
            ->with('status', "Bank '{$name}' berhasil dihapus dari sistem.");
    }
}
