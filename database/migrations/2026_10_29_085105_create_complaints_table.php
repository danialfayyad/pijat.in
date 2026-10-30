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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_code', 30)->unique();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->foreignId('complainant_id')->constrained('users')->cascadeOnDelete();
            $table->string('complainant_role', 20)->default('pelanggan');
            $table->foreignId('target_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category', 50)->default('lainnya');
            $table->string('title');
            $table->text('description');
            $table->json('evidence_photos')->nullable();
            $table->string('status', 30)->default('menunggu');
            $table->string('priority', 20)->default('normal');
            $table->foreignId('handled_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->string('resolution_type', 40)->nullable(); // 'refund_penuh', 'rekomendasi_sp', 'damai_tanpa_tindakan', 'ditolak'
            $table->text('admin_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            // Indexes for fast filtered queries
            $table->index(['region_id', 'status']);
            $table->index('status');
            $table->index('created_at');
            $table->index('complainant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
