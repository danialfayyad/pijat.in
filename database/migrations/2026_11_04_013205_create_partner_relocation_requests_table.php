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
        Schema::create('partner_relocation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('from_region_id')->constrained('regions')->cascadeOnDelete();
            $table->foreignId('to_region_id')->constrained('regions')->cascadeOnDelete();
            $table->text('address_text');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->geometry('coordinate', subtype: 'point', srid: 4326);
            $table->string('status', 30)->default('PENDING');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['partner_id', 'status']);
            $table->index(['to_region_id', 'status']);
            $table->index(['from_region_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_relocation_requests');
    }
};
