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
            $table->json('available_banks')->nullable()->after('va_bank_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('topup_invoices', function (Blueprint $table) {
            $table->dropColumn(['available_banks']);
        });
    }
};
