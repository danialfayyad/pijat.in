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
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->string('xendit_disbursement_id', 100)->nullable()->after('transfer_reference')->index();
            $table->string('disbursement_status', 30)->nullable()->after('xendit_disbursement_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropIndex(['xendit_disbursement_id']);
            $table->dropColumn(['xendit_disbursement_id', 'disbursement_status']);
        });
    }
};
