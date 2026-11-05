<?php

use App\Enums\Role;
use App\Models\Faq;
use App\Models\PartnerProfile;
use App\Models\Region;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->region = Region::factory()->create([
        'name' => 'KOTA BLITAR',
        'status' => 'aktif',
    ]);

    $this->partner = User::factory()->create([
        'role' => Role::Mitra,
    ]);
    PartnerProfile::create([
        'user_id' => $this->partner->id,
        'region_id' => $this->region->id,
        'address_text' => 'Jl. Merdeka No. 10',
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'ktp_path' => 'ktp.jpg',
        'selfie_path' => 'selfie.jpg',
        'ijazah_path' => 'ijazah.jpg',
        'skck_path' => 'skck.jpg',
        'bank_name' => 'BCA',
        'account_number' => '1234567890',
        'account_holder_name' => 'Mitra Bantuan',
    ]);

    $this->otherPartner = User::factory()->create([
        'role' => Role::Mitra,
    ]);
    PartnerProfile::create([
        'user_id' => $this->otherPartner->id,
        'region_id' => $this->region->id,
        'address_text' => 'Jl. Sudirman No. 20',
        'coordinate' => ['lat' => -8.1720, 'lng' => 113.7000],
        'ktp_path' => 'ktp.jpg',
        'selfie_path' => 'selfie.jpg',
        'ijazah_path' => 'ijazah.jpg',
        'skck_path' => 'skck.jpg',
        'bank_name' => 'BCA',
        'account_number' => '0987654321',
        'account_holder_name' => 'Mitra Lain',
    ]);

    $this->admin = User::factory()->create([
        'role' => Role::Admin,
    ]);
    $this->admin->assignedRegions()->attach($this->region->id);
});

test('partner can view faqs scoped to partner and general audience', function () {
    Faq::create([
        'target_audience' => 'mitra',
        'category' => 'Keuangan',
        'question' => 'Kapan saldo pesanan cair ke dompet utama?',
        'answer' => 'Saldo tertahan akan cair dalam 24 jam.',
        'is_active' => true,
    ]);

    Faq::create([
        'target_audience' => 'semua',
        'category' => 'Akun',
        'question' => 'Bagaimana cara ganti nomor HP?',
        'answer' => 'Hubungi admin operasional untuk perubahan nomor.',
        'is_active' => true,
    ]);

    Faq::create([
        'target_audience' => 'pelanggan',
        'category' => 'Pemesanan',
        'question' => 'Bagaimana cara pesan?',
        'answer' => 'Pilih terapis di bursa layanan.',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->partner)->get(route('mitra.help.index'));

    $response->assertOk()
        ->assertSee('Kapan saldo pesanan cair ke dompet utama?')
        ->assertSee('Bagaimana cara ganti nomor HP?')
        ->assertDontSee('Bagaimana cara pesan?');
});

test('partner can open a new support ticket bound to partner region', function () {
    $response = $this->actingAs($this->partner)->post(route('mitra.help.store'), [
        'subject' => 'Pelanggan Tidak di Titik Penjemputan',
        'message' => 'Saya sudah menunggu 15 menit tapi pelanggan tidak bisa dihubungi di lokasi.',
    ]);

    expect(SupportTicket::where('user_id', $this->partner->id)->exists())->toBeTrue();

    $ticket = SupportTicket::where('user_id', $this->partner->id)->first();
    expect($ticket->subject)->toBe('Pelanggan Tidak di Titik Penjemputan');
    expect($ticket->region_id)->toBe($this->region->id);
    expect($ticket->status)->toBe(SupportTicket::STATUS_MENUNGGU);
    expect($ticket->user_role)->toBe('mitra');

    expect(SupportMessage::where('support_ticket_id', $ticket->id)->count())->toBe(1);
    $firstMsg = SupportMessage::where('support_ticket_id', $ticket->id)->first();
    expect($firstMsg->sender_id)->toBe($this->partner->id);
    expect($firstMsg->sender_role)->toBe('mitra');

    $response->assertRedirect(route('mitra.help.ticket', $ticket));
});

test('partner cannot access other partners chat rooms', function () {
    $myTicket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateTicketCode(),
        'user_id' => $this->partner->id,
        'region_id' => $this->region->id,
        'user_role' => 'mitra',
        'subject' => 'Tiket Mitra Saya',
        'status' => SupportTicket::STATUS_MENUNGGU,
    ]);

    $otherTicket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateTicketCode(),
        'user_id' => $this->otherPartner->id,
        'region_id' => $this->region->id,
        'user_role' => 'mitra',
        'subject' => 'Tiket Mitra Lain',
        'status' => SupportTicket::STATUS_MENUNGGU,
    ]);

    // Can access own ticket
    $this->actingAs($this->partner)->get(route('mitra.help.ticket', $myTicket))
        ->assertOk()
        ->assertSee('Tiket Mitra Saya');

    // Forbidden from other user's ticket
    $this->actingAs($this->partner)->get(route('mitra.help.ticket', $otherTicket))
        ->assertForbidden();
});

test('partner can reply and poll messages in support chat room', function () {
    $ticket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateTicketCode(),
        'user_id' => $this->partner->id,
        'region_id' => $this->region->id,
        'user_role' => 'mitra',
        'subject' => 'Pertanyaan Insiden',
        'status' => SupportTicket::STATUS_BERLANGSUNG,
    ]);

    // Send reply via JSON
    $replyResponse = $this->actingAs($this->partner)->postJson(route('mitra.help.reply', $ticket), [
        'message' => 'Admin, bukti foto lokasi sudah saya kumpulkan.',
    ]);

    $replyResponse->assertOk()
        ->assertJsonPath('success', true);

    expect(SupportMessage::where('support_ticket_id', $ticket->id)->count())->toBe(1);

    // Admin adds a reply
    $adminMsg = SupportMessage::create([
        'support_ticket_id' => $ticket->id,
        'sender_id' => $this->admin->id,
        'sender_role' => 'admin',
        'message' => 'Baik, segera kami verifikasi ya mitra.',
        'is_read' => false,
    ]);

    // Partner polls messages
    $pollResponse = $this->actingAs($this->partner)->getJson(route('mitra.help.messages', $ticket));

    $pollResponse->assertOk()
        ->assertJsonStructure(['ticket_status', 'messages']);

    $adminMsg->refresh();
    expect($adminMsg->is_read)->toBeTrue();
});
