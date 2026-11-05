<?php

use App\Enums\Role;
use App\Models\Faq;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superadmin = User::factory()->create([
        'role' => Role::Superadmin,
    ]);

    $this->admin = User::factory()->create([
        'role' => Role::Admin,
    ]);

    $this->customer = User::factory()->create([
        'role' => Role::Pelanggan,
    ]);
});

test('superadmin can access faqs management page with audience filtering', function () {
    Faq::create([
        'target_audience' => 'pelanggan',
        'category' => 'Pemesanan',
        'question' => 'Bagaimana cara memesan layanan?',
        'answer' => 'Pilih terapis dan waktu yang diinginkan.',
        'order_index' => 1,
        'is_active' => true,
    ]);

    Faq::create([
        'target_audience' => 'mitra',
        'category' => 'Keuangan',
        'question' => 'Kapan saldo pesanan cair?',
        'answer' => 'Saldo tertahan akan cair dalam 24 jam.',
        'order_index' => 2,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superadmin)->get(route('superadmin.faqs.index', ['audience' => 'pelanggan']));
    $response->assertOk()
        ->assertSee('Bagaimana cara memesan layanan?')
        ->assertDontSee('Kapan saldo pesanan cair?');
});

test('superadmin can create a new faq', function () {
    $response = $this->actingAs($this->superadmin)->post(route('superadmin.faqs.store'), [
        'target_audience' => 'mitra',
        'category' => 'Sanksi & Poin',
        'question' => 'Bagaimana cara mengajukan banding poin?',
        'answer' => 'Masuk ke menu Sanksi lalu unggah surat pernyataan.',
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('superadmin.faqs.index'));

    expect(Faq::where('question', 'Bagaimana cara mengajukan banding poin?')->exists())->toBeTrue();
    $faq = Faq::where('question', 'Bagaimana cara mengajukan banding poin?')->first();
    expect($faq->target_audience)->toBe('mitra');
    expect($faq->created_by)->toBe($this->superadmin->id);
});

test('superadmin can update an existing faq', function () {
    $faq = Faq::create([
        'target_audience' => 'semua',
        'category' => 'Akun',
        'question' => 'Judul Lama',
        'answer' => 'Jawaban Lama yang cukup panjang',
        'order_index' => 0,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->superadmin)->put(route('superadmin.faqs.update', $faq), [
        'target_audience' => 'pelanggan',
        'category' => 'Akun & Keamanan',
        'question' => 'Judul Baru Diperbarui',
        'answer' => 'Jawaban Baru yang telah disesuaikan dengan SOP terbaru',
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('superadmin.faqs.index'));
    $faq->refresh();

    expect($faq->question)->toBe('Judul Baru Diperbarui');
    expect($faq->target_audience)->toBe('pelanggan');
});

test('superadmin can toggle active status and delete faq', function () {
    $faq = Faq::create([
        'target_audience' => 'pelanggan',
        'category' => 'Umum',
        'question' => 'Pertanyaan uji coba?',
        'answer' => 'Jawaban uji coba.',
        'is_active' => true,
    ]);

    // 1. Toggle status
    $toggleRes = $this->actingAs($this->superadmin)->post(route('superadmin.faqs.toggle', $faq));
    $toggleRes->assertRedirect();
    $faq->refresh();
    expect($faq->is_active)->toBeFalse();

    // 2. Delete
    $deleteRes = $this->actingAs($this->superadmin)->delete(route('superadmin.faqs.destroy', $faq));
    $deleteRes->assertRedirect(route('superadmin.faqs.index'));
    expect(Faq::where('id', $faq->id)->exists())->toBeFalse();
});

test('non-superadmin users cannot access superadmin faqs endpoints', function () {
    $response = $this->actingAs($this->admin)->get(route('superadmin.faqs.index'));
    $response->assertForbidden();

    $customerRes = $this->actingAs($this->customer)->get(route('superadmin.faqs.index'));
    $customerRes->assertForbidden();
});
