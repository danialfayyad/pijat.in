<?php

use App\Enums\Role;
use App\Models\Region;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\SupportChatService;
use Carbon\Carbon;
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

    $this->customer = User::factory()->create([
        'role' => Role::Pelanggan,
    ]);

    $this->admin = User::factory()->create([
        'role' => Role::Admin,
    ]);
    $this->admin->assignedRegions()->attach($this->region->id);
});

test('inactive support ticket receives 1-minute warning message after 4 minutes of silence', function () {
    $ticket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateTicketCode(),
        'user_id' => $this->partner->id,
        'user_role' => 'mitra',
        'region_id' => $this->region->id,
        'admin_id' => $this->admin->id,
        'subject' => 'Kendala Aplikasi',
        'status' => SupportTicket::STATUS_BERLANGSUNG,
        'last_message_at' => Carbon::now()->subMinutes(4)->subSeconds(10),
    ]);

    $service = app(SupportChatService::class);
    $result = $service->processInactivityTimeouts($ticket);

    expect($result['warned'])->toBe(1);
    expect($result['closed'])->toBe(0);

    $ticket->refresh();
    expect($ticket->inactivity_warning_at)->not->toBeNull();
    expect($ticket->status)->toBe(SupportTicket::STATUS_BERLANGSUNG);

    $lastMessage = $ticket->messages()->latest('id')->first();
    expect($lastMessage)->not->toBeNull()
        ->and($lastMessage->sender_role)->toBe('admin')
        ->and($lastMessage->message)->toContain('dalam 1 menit');
});

test('inactive ticket without assigned admin still receives warning message without null sender constraint error', function () {
    $ticket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateTicketCode(),
        'user_id' => $this->partner->id,
        'user_role' => 'mitra',
        'region_id' => $this->region->id,
        'admin_id' => null,
        'subject' => 'Kendala Menunggu Admin',
        'status' => SupportTicket::STATUS_MENUNGGU,
        'last_message_at' => Carbon::now()->subMinutes(4)->subSeconds(10),
    ]);

    $service = app(SupportChatService::class);
    $result = $service->processInactivityTimeouts($ticket);

    expect($result['warned'])->toBe(1);

    $ticket->refresh();
    expect($ticket->inactivity_warning_at)->not->toBeNull();

    $lastMessage = $ticket->messages()->latest('id')->first();
    expect($lastMessage)->not->toBeNull()
        ->and($lastMessage->sender_role)->toBe('admin');
});

test('user reply resets inactivity warning timer back to normal', function () {
    $ticket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateTicketCode(),
        'user_id' => $this->partner->id,
        'user_role' => 'mitra',
        'region_id' => $this->region->id,
        'admin_id' => $this->admin->id,
        'subject' => 'Kendala Aplikasi',
        'status' => SupportTicket::STATUS_BERLANGSUNG,
        'last_message_at' => Carbon::now()->subMinutes(4),
        'inactivity_warning_at' => Carbon::now()->subSeconds(30),
    ]);

    $response = $this->actingAs($this->partner)->post(route('mitra.help.reply', $ticket), [
        'message' => 'Halo masih ada pertanyaan lagi min',
    ]);

    $response->assertSessionHasNoErrors();

    $ticket->refresh();
    expect($ticket->inactivity_warning_at)->toBeNull();
    expect($ticket->status)->toBe(SupportTicket::STATUS_BERLANGSUNG);
});

test('inactive support ticket is automatically closed with polite message after 5 minutes total silence', function () {
    $ticket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateTicketCode(),
        'user_id' => $this->customer->id,
        'user_role' => 'pelanggan',
        'region_id' => $this->region->id,
        'admin_id' => $this->admin->id,
        'subject' => 'Pertanyaan Pembayaran',
        'status' => SupportTicket::STATUS_BERLANGSUNG,
        'last_message_at' => Carbon::now()->subMinutes(5)->subSeconds(10),
        'inactivity_warning_at' => Carbon::now()->subMinutes(1)->subSeconds(5),
    ]);

    $service = app(SupportChatService::class);
    $result = $service->processInactivityTimeouts($ticket);

    expect($result['closed'])->toBe(1);

    $ticket->refresh();
    expect($ticket->status)->toBe(SupportTicket::STATUS_SELESAI);
    expect($ticket->closed_at)->not->toBeNull();

    $closingMessage = $ticket->messages()->latest('id')->first();
    expect($closingMessage)->not->toBeNull()
        ->and($closingMessage->sender_role)->toBe('admin')
        ->and($closingMessage->message)->toContain('Terima kasih telah menghubungi Layanan Bantuan Pijat.In');
});

test('support:check-inactivity artisan command runs successfully', function () {
    $ticket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateTicketCode(),
        'user_id' => $this->partner->id,
        'user_role' => 'mitra',
        'region_id' => $this->region->id,
        'admin_id' => $this->admin->id,
        'subject' => 'Kendala Akun',
        'status' => SupportTicket::STATUS_BERLANGSUNG,
        'last_message_at' => Carbon::now()->subMinutes(4)->subSeconds(20),
    ]);

    $this->artisan('support:check-inactivity')
        ->assertSuccessful();

    $ticket->refresh();
    expect($ticket->inactivity_warning_at)->not->toBeNull();
});

test('data retention cleanup prunes all closed support tickets immediately', function () {
    $closedTicket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateTicketCode(),
        'user_id' => $this->partner->id,
        'user_role' => 'mitra',
        'region_id' => $this->region->id,
        'subject' => 'Tiket Selesai',
        'status' => SupportTicket::STATUS_SELESAI,
        'closed_at' => Carbon::now()->subMinutes(10),
    ]);

    SupportMessage::create([
        'support_ticket_id' => $closedTicket->id,
        'sender_id' => $this->partner->id,
        'sender_role' => 'mitra',
        'message' => 'Pesan selesai',
    ]);

    $activeTicket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateTicketCode(),
        'user_id' => $this->partner->id,
        'user_role' => 'mitra',
        'region_id' => $this->region->id,
        'subject' => 'Tiket Masih Aktif',
        'status' => SupportTicket::STATUS_BERLANGSUNG,
    ]);

    $this->artisan('app:cleanup-expired-data')
        ->assertSuccessful();

    expect(SupportTicket::where('id', $closedTicket->id)->exists())->toBeFalse();
    expect(SupportMessage::where('support_ticket_id', $closedTicket->id)->exists())->toBeFalse();
    expect(SupportTicket::where('id', $activeTicket->id)->exists())->toBeTrue();
});

test('user can end live chat session manually and delete the ticket', function () {
    $ticket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateTicketCode(),
        'user_id' => $this->partner->id,
        'user_role' => 'mitra',
        'region_id' => $this->region->id,
        'subject' => 'Pertanyaan Selesai Cepat',
        'status' => SupportTicket::STATUS_BERLANGSUNG,
    ]);

    $response = $this->actingAs($this->partner)->post(route('mitra.help.close', $ticket));
    $response->assertRedirect(route('mitra.help.index'));

    expect(SupportTicket::where('id', $ticket->id)->exists())->toBeFalse();
});

test('starting chat cleans up any previous ended tickets for the user', function () {
    $closedTicket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateTicketCode(),
        'user_id' => $this->partner->id,
        'user_role' => 'mitra',
        'region_id' => $this->region->id,
        'subject' => 'Sesi Kemarin Selesai',
        'status' => SupportTicket::STATUS_SELESAI,
        'closed_at' => Carbon::now()->subHours(2),
    ]);

    $response = $this->actingAs($this->partner)->get(route('mitra.help.chat'));
    $response->assertOk()
        ->assertViewIs('mitra.help.create');

    expect(SupportTicket::where('id', $closedTicket->id)->exists())->toBeFalse();
});
