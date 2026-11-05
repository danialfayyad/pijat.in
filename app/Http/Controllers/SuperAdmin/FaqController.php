<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    /**
     * Display a listing of FAQs (Super Admin).
     */
    public function index(Request $request): View
    {
        $audience = $request->query('audience', 'semua');
        $selectedCategory = $request->query('category');
        $search = trim($request->query('search', ''));

        $query = Faq::query()->with('creator');

        if ($audience !== 'semua') {
            $query->where('target_audience', $audience);
        }

        if ($selectedCategory) {
            $query->where('category', $selectedCategory);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        $faqs = $query->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = Faq::select('category')->distinct()->pluck('category')->filter()->values();

        $counts = [
            'semua' => Faq::count(),
            'pelanggan' => Faq::where('target_audience', 'pelanggan')->count(),
            'mitra' => Faq::where('target_audience', 'mitra')->count(),
            'umum' => Faq::where('target_audience', 'semua')->count(),
        ];

        return view('superadmin.faqs.index', [
            'faqs' => $faqs,
            'currentAudience' => $audience,
            'selectedCategory' => $selectedCategory,
            'search' => $search,
            'categories' => $categories,
            'counts' => $counts,
        ]);
    }

    /**
     * Store a newly created FAQ.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_audience' => ['required', 'in:semua,pelanggan,mitra'],
            'category' => ['required', 'string', 'max:100'],
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'min:5'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Faq::create([
            'target_audience' => $validated['target_audience'],
            'category' => trim($validated['category']),
            'question' => trim($validated['question']),
            'answer' => trim($validated['answer']),
            'order_index' => 0,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('superadmin.faqs.index')
            ->with('success', 'Artikel FAQ baru berhasil ditambahkan.');
    }

    /**
     * Update the specified FAQ.
     */
    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $validated = $request->validate([
            'target_audience' => ['required', 'in:semua,pelanggan,mitra'],
            'category' => ['required', 'string', 'max:100'],
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'min:5'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $faq->update([
            'target_audience' => $validated['target_audience'],
            'category' => trim($validated['category']),
            'question' => trim($validated['question']),
            'answer' => trim($validated['answer']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('superadmin.faqs.index')
            ->with('success', 'Artikel FAQ berhasil diperbarui.');
    }

    /**
     * Toggle active status of the specified FAQ.
     */
    public function toggle(Faq $faq): RedirectResponse
    {
        $faq->update([
            'is_active' => ! $faq->is_active,
        ]);

        $statusLabel = $faq->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status FAQ berhasil {$statusLabel}.");
    }

    /**
     * Delete the specified FAQ.
     */
    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('superadmin.faqs.index')
            ->with('success', 'Artikel FAQ berhasil dihapus.');
    }
}
