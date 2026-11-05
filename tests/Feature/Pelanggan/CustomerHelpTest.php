<?php

use App\Enums\Role;
use App\Models\Faq;
use App\Models\Region;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->region = Region::factory()->create([
        'name' => 'KOTA KEDIRI',
        'status' => 'aktif',
    ]);

    $this->customer = User::factory()->create([
        'role' => Role::Pelanggan,
    ]);

    $this->otherCustomer = User::factory()->create([
        'role' => Role::Pelanggan,
    ]);

    $this->admin = User::factory()->create([
        'role' => Role::Admin,
    ]);
    $this->admin->assignedRegions()->attach($this->region->id);
});

test('customer can view faqs scoped to customer and general audience', function () {
    Faq::create([
        'target_audience' => 'pelanggan',
        'category' => 'Pemesanan',
        'question' => 'Bagaimana cara pesan terapis pria?',
        'answer' => 'Pilih filter gender pria saat proses pemesanan.',
        'is_active' => true,
    ]);

    Faq::create([
        'target_audience' => 'semua',
        'category' => 'Akun',
        'question' => 'Bagaimana cara ganti password?',
        'answer' => 'Masuk ke menu Pengaturan Akun.',
        'is_active' => true,
    ]);

    Faq::create([
        'target_audience' => 'mitra',
        'category' => 'Keuangan',
        'question' => 'Berapa potongan komisi mitra?',
        'answer' => 'Komisi platform adalah 20%.',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->customer)->get(route('pelanggan.help.index'));

    $response->assertOk()
        ->assertSee('Bagaimana cara pesan terapis pria?')
        ->assertSee('Bagaimana cara ganti password?')
        ->assertDontSee('Berapa potongan komisi mitra?');
});

test('customer can search and filter faqs by category', function () {
    Faq::create([
        'target_audience' => 'pelanggan',
        'category' => 'Pemesanan',
        'question' => 'Bisa batalkan pesanan?',
        'answer' => 'Bisa sebelum terapis menuju lokasi.',
        'is_active' => true,
    ]);

    Faq::create([
        'target_audience' => 'pelanggan',
        'category' => 'Pembayaran',
        'question' => 'Metode top up apa saja?',
        'answer' => 'Transfer bank dan e-wallet.',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->customer)->get(route('pelanggan.help.index', ['category' => 'Pemesanan']));

    $response->assertOk()
        ->assertSee('Bisa batalkan pesanan?')
        ->assertDontSee('Metode top up apa saja?');
});

test('customer can open a new support ticket and send initial message', function () {
    $response = $this->actingAs($this->customer)->post(route('pelanggan.help.store'), [
        'subject' => 'Kendala Pembayaran Dompet',
        'message' => 'Saldo saya sudah terpotong tapi status invoice belum lunas.',
    ]);

    expect(SupportTicket::where('user_id', $this->customer->id)->exists())->toBeTrue();

    $ticket = SupportTicket::where('user_id', $this->customer->id)->first();
    expect($ticket->subject)->toBe('Kendala Pembayaran Dompet');
    expect($ticket->status)->toBe(SupportTicket::STATUS_MENUNGGU);
    expect($ticket->user_role)->toBe('pelanggan');

    expect(SupportMessage::where('support_ticket_id', $ticket->id)->count())->toBe(1);
    $firstMsg = SupportMessage::where('support_ticket_id', $ticket->id)->first();
    expect($firstMsg->sender_id)->toBe($this->customer->id);
    expect($firstMsg->sender_role)->toBe('pelanggan');

    $response->assertRedirect(route('pelanggan.help.ticket', $ticket));
});

test('customer can only access their own support ticket chat room', function () {
    $myTicket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateTicketCode(),
        'user_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'user_role' => 'pelanggan',
        'subject' => 'Tiket Saya',
        'status' => SupportTicket::STATUS_MENUNGGU,
    ]);

    $otherTicket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateTicketCode(),
        'user_id' => $this->otherCustomer->id,
        'region_id' => $this->region->id,
        'user_role' => 'pelanggan',
        'subject' => 'Tiket Orang Lain',
        'status' => SupportTicket::STATUS_MENUNGGU,
    ]);

    // Can access own ticket
    $this->actingAs($this->customer)->get(route('pelanggan.help.ticket', $myTicket))
        ->assertOk()
        ->assertSee('Tiket Saya');

    // Forbidden from other user's ticket
    $this->actingAs($this->customer)->get(route('pelanggan.help.ticket', $otherTicket))
        ->assertForbidden();
});

test('customer can reply and poll messages in support chat room', function () {
    $ticket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateTicketCode(),
        'user_id' => $this->customer->id,
        'region_id' => $this->region->id,
        'user_role' => 'pelanggan',
        'subject' => 'Tanya Jadwal',
        'status' => SupportTicket::STATUS_BERLANGSUNG,
    ]);

    // Send reply via JSON
    $replyResponse = $this->actingAs($this->customer)->postJson(route('pelanggan.help.reply', $ticket), [
        'message' => 'Halo admin, apakah terapis sudah dalam perjalanan?',
    ]);

    $replyResponse->assertOk()
        ->assertJsonPath('success', true);

    expect(SupportMessage::where('support_ticket_id', $ticket->id)->count())->toBe(1);

    // Admin adds a reply
    $adminMsg = SupportMessage::create([
        'support_ticket_id' => $ticket->id,
        'sender_id' => $this->admin->id,
        'sender_role' => 'admin',
        'message' => 'Betul, terapis sedang menuju lokasi Anda.',
        'is_read' => false,
    ]);

    // Customer polls messages
    $pollResponse = $this->actingAs($this->customer)->getJson(route('pelanggan.help.messages', $ticket));

    $pollResponse->assertOk()
        ->assertJsonStructure(['ticket_status', 'messages']);

    $adminMsg->refresh();
    expect($adminMsg->is_read)->toBeTrue();
});
