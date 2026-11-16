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
        Schema::table('topup_invoices', function (Blueprint $table) {
            $table->text('qris_string')->nullable()->after('payment_url');
            $table->string('va_number', 50)->nullable()->after('qris_string');
            $table->string('va_bank_code', 30)->nullable()->after('va_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('topup_invoices', function (Blueprint $table) {
            $table->dropColumn(['qris_string', 'va_number', 'va_bank_code']);
        });
    }
};
