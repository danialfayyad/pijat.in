<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use Carbon\Carbon;

class SupportChatService
{
    public const INACTIVITY_WARNING_MINUTES = 4;

    public const INACTIVITY_CLOSE_MINUTES = 1;

    /**
     * Process inactive support tickets.
     * 1. Send 1-minute warning message after 4 minutes of silence.
     * 2. Send polite closure message after 1 additional minute of silence (5 min total) and close the ticket.
     *
     * @return array{warned: int, closed: int}
     */
    public function processInactivityTimeouts(?SupportTicket $specificTicket = null): array
    {
        $query = SupportTicket::whereIn('status', [
            SupportTicket::STATUS_MENUNGGU,
            SupportTicket::STATUS_BERLANGSUNG,
        ]);

        if ($specificTicket) {
            $query->where('id', $specificTicket->id);
        }

        $tickets = $query->get();

        $warnedCount = 0;
        $closedCount = 0;

        foreach ($tickets as $ticket) {
            $lastTime = $ticket->last_message_at ?? $ticket->created_at;

            // Step 2 Check: If warning was already sent and 1 minute has elapsed without reply -> Close session
            if ($ticket->inactivity_warning_at !== null) {
                if ($ticket->inactivity_warning_at->lte(Carbon::now()->subMinutes(self::INACTIVITY_CLOSE_MINUTES))) {
                    $this->closeInactiveTicket($ticket);
                    $closedCount++;

                    continue;
                }
            }

            // Step 1 Check: If no warning sent yet and 4 minutes have elapsed without reply -> Send warning message
            if ($ticket->inactivity_warning_at === null && $lastTime !== null) {
                if ($lastTime->lte(Carbon::now()->subMinutes(self::INACTIVITY_WARNING_MINUTES))) {
                    $this->sendInactivityWarning($ticket);
                    $warnedCount++;
                }
            }
        }

        return [
            'warned' => $warnedCount,
            'closed' => $closedCount,
        ];
    }

    /**
     * Resolve admin sender id or fallback to region admin / general admin.
     */
    protected function resolveAdminSenderId(SupportTicket $ticket): ?int
    {
        return $ticket->admin_id
            ?? $ticket->region?->admins()->first()?->id
            ?? User::where('role', Role::Admin)->first()?->id;
    }

    /**
     * Send 1-minute inactivity warning message.
     */
    public function sendInactivityWarning(SupportTicket $ticket): void
    {
        SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_id' => $this->resolveAdminSenderId($ticket),
            'sender_role' => 'admin',
            'message' => 'Halo, apakah masih ada pertanyaan atau kendala lain yang bisa kami bantu? Jika tidak ada respon, sesi chat ini akan diakhiri otomatis dalam 1 menit.',
            'is_read' => false,
        ]);

        $ticket->update([
            'inactivity_warning_at' => Carbon::now(),
        ]);
    }

    /**
     * Send polite closing message and mark ticket as closed.
     */
    public function closeInactiveTicket(SupportTicket $ticket): void
    {
        SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_id' => $this->resolveAdminSenderId($ticket),
            'sender_role' => 'admin',
            'message' => 'Terima kasih telah menghubungi Layanan Bantuan Pijat.In. Sesi chat ini kami akhiri karena tidak ada aktivitas lebih lanjut. Anda dapat memulai obrolan baru kapan saja jika membutuhkan bantuan. Semoga hari Anda menyenangkan!',
            'is_read' => false,
        ]);

        $ticket->update([
            'status' => SupportTicket::STATUS_SELESAI,
            'closed_at' => Carbon::now(),
        ]);
    }
}
