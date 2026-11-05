<?php

use App\Enums\Role;
use App\Models\Region;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->regionA = Region::factory()->create([
        'name' => 'KOTA BLITAR',
        'status' => 'aktif',
    ]);

    $this->regionB = Region::factory()->create([
        'name' => 'KABUPATEN KEDIRI',
        'status' => 'aktif',
    ]);

    $this->adminA = User::factory()->create(['role' => Role::Admin]);
    $this->adminA->assignedRegions()->attach($this->regionA->id);

    $this->adminB = User::factory()->create(['role' => Role::Admin]);
    $this->adminB->assignedRegions()->attach($this->regionB->id);

    $this->mitra = User::factory()->create([
        'role' => Role::Mitra,
        'region_id' => $this->regionA->id,
    ]);

    $this->customer = User::factory()->create([
        'role' => Role::Pelanggan,
    ]);
});

test('regional admin can view support chat queue scoped strictly to assigned region', function () {
    // Ticket in Region A
    $ticketA = SupportTicket::create([
        'ticket_code' => 'TIK-TEST-001',
        'user_id' => $this->mitra->id,
        'user_role' => 'mitra',
        'region_id' => $this->regionA->id,
        'subject' => 'Kendala pencairan saldo BCA',
        'status' => SupportTicket::STATUS_MENUNGGU,
    ]);

    // Ticket in Region B
    $ticketB = SupportTicket::create([
        'ticket_code' => 'TIK-TEST-002',
        'user_id' => $this->customer->id,
        'user_role' => 'pelanggan',
        'region_id' => $this->regionB->id,
        'subject' => 'Terapis belum tiba di alamat',
        'status' => SupportTicket::STATUS_MENUNGGU,
    ]);

    // Admin A visits support chats
    $response = $this->actingAs($this->adminA)->get(route('admin.support-chats.index'));
    $response->assertOk()
        ->assertSee('TIK-TEST-001')
        ->assertSee('Kendala pencairan saldo BCA')
        ->assertDontSee('TIK-TEST-002');
});

test('admin cannot view or reply to ticket from unassigned region', function () {
    $ticketB = SupportTicket::create([
        'ticket_code' => 'TIK-TEST-003',
        'user_id' => $this->customer->id,
        'user_role' => 'pelanggan',
        'region_id' => $this->regionB->id,
        'subject' => 'Masalah voucher',
        'status' => SupportTicket::STATUS_MENUNGGU,
    ]);

    $response = $this->actingAs($this->adminA)->get(route('admin.support-chats.show', $ticketB));
    $response->assertForbidden();

    $replyResponse = $this->actingAs($this->adminA)->post(route('admin.support-chats.reply', $ticketB), [
        'message' => 'Halo, ini balasan terlarang.',
    ]);
    $replyResponse->assertForbidden();
});

test('admin opening waiting ticket claims it and marks incoming messages as read', function () {
    $ticket = SupportTicket::create([
        'ticket_code' => 'TIK-TEST-004',
        'user_id' => $this->mitra->id,
        'user_role' => 'mitra',
        'region_id' => $this->regionA->id,
        'subject' => 'Aplikasi freeze saat selesai pijat',
        'status' => SupportTicket::STATUS_MENUNGGU,
    ]);

    $msg = SupportMessage::create([
        'support_ticket_id' => $ticket->id,
        'sender_id' => $this->mitra->id,
        'sender_role' => 'mitra',
        'message' => 'Tolong min, tombol selesaikan tidak bisa ditekan.',
        'is_read' => false,
    ]);

    $response = $this->actingAs($this->adminA)->get(route('admin.support-chats.show', $ticket));
    $response->assertOk()
        ->assertSee('Aplikasi freeze saat selesai pijat')
        ->assertSee('Tolong min, tombol selesaikan tidak bisa ditekan.');

    $ticket->refresh();
    $msg->refresh();

    expect($ticket->status)->toBe(SupportTicket::STATUS_BERLANGSUNG);
    expect($ticket->admin_id)->toBe($this->adminA->id);
    expect($msg->is_read)->toBeTrue();
});

test('admin can reply to user in chat room and fetch live messages', function () {
    $ticket = SupportTicket::create([
        'ticket_code' => 'TIK-TEST-005',
        'user_id' => $this->mitra->id,
        'user_role' => 'mitra',
        'region_id' => $this->regionA->id,
        'subject' => 'Konfirmasi jam operasional',
        'status' => SupportTicket::STATUS_BERLANGSUNG,
    ]);

    // Admin replies
    $response = $this->actingAs($this->adminA)->postJson(route('admin.support-chats.reply', $ticket), [
        'message' => 'Halo Budi, operasional layanan dibuka mulai pukul 08:00 hingga 21:00.',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => [
                'message' => 'Halo Budi, operasional layanan dibuka mulai pukul 08:00 hingga 21:00.',
                'sender_role' => 'admin',
            ],
        ]);

    expect(SupportMessage::where('support_ticket_id', $ticket->id)->count())->toBe(1);

    // Fetch messages polling endpoint
    $fetchRes = $this->actingAs($this->adminA)->getJson(route('admin.support-chats.messages', $ticket));
    $fetchRes->assertOk()
        ->assertJsonStructure(['messages', 'ticket_status']);
});

test('admin can resolve and reopen support ticket', function () {
    $ticket = SupportTicket::create([
        'ticket_code' => 'TIK-TEST-006',
        'user_id' => $this->mitra->id,
        'user_role' => 'mitra',
        'region_id' => $this->regionA->id,
        'subject' => 'Pertanyaan selesai',
        'status' => SupportTicket::STATUS_BERLANGSUNG,
    ]);

    // 1. Close ticket
    $closeRes = $this->actingAs($this->adminA)->post(route('admin.support-chats.close', $ticket));
    $closeRes->assertRedirect(route('admin.support-chats.index'));

    $ticket->refresh();
    expect($ticket->status)->toBe(SupportTicket::STATUS_SELESAI);
    expect($ticket->closed_by)->toBe($this->adminA->id);
    expect($ticket->closed_at)->not->toBeNull();

    // 2. Reopen ticket
    $reopenRes = $this->actingAs($this->adminA)->post(route('admin.support-chats.reopen', $ticket));
    $reopenRes->assertRedirect();

    $ticket->refresh();
    expect($ticket->status)->toBe(SupportTicket::STATUS_BERLANGSUNG);
    expect($ticket->closed_at)->toBeNull();
});
