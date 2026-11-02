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
        // 1. Parameters Table (SA-01)
        Schema::create('parameters', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value');
            $table->string('type', 30)->default('string'); // string, integer, decimal, boolean
            $table->string('group', 50)->index(); // operational_hours, dispatch, order, location, timeout, financial, penalty, privacy
            $table->string('label', 255);
            $table->text('description')->nullable();
            $table->string('unit', 50)->nullable(); // Menit, Jam, Detik, Meter, Kilometer, %, IDR, Hari, Bulan, Pesanan, Skala Bintang
            $table->timestamps();
        });

        // 2. Audit Logs Table (SA-03)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('module', 50)->index(); // parameter, region, admin, catalog, user, penalty, legal
            $table->string('action', 50)->index(); // create, update, delete, status_change
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['auditable_type', 'auditable_id']);
        });

        // 3. Legal Documents Table (SA-05)
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->unique(); // sop_kemitraan, terms_and_conditions, privacy_policy, risk_statement
            $table->string('title', 255);
            $table->string('version', 20)->default('1.0');
            $table->longText('content');
            $table->text('summary')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('parameters');
    }
};
