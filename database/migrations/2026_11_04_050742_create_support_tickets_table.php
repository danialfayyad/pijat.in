<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code', 30)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('user_role', 20); // 'pelanggan' | 'mitra'
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject');
            $table->enum('status', ['menunggu', 'berlangsung', 'selesai'])->default('menunggu');
            $table->enum('priority', ['rendah', 'normal', 'tinggi'])->default('normal');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['region_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
