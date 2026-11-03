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
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->string('withdrawal_code', 30)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->decimal('fee', 14, 2)->default(0.00);
            $table->decimal('net_amount', 14, 2);
            $table->string('bank_name', 100);
            $table->string('account_number', 100);
            $table->string('account_holder_name', 255);
            $table->string('status', 30)->default('PENDING');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('proof_path', 255)->nullable();
            $table->string('transfer_reference', 100)->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['region_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('withdrawal_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
