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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_address_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('customer_addresses')
                ->nullOnDelete();

            $table->string('recipient_type', 20)
                ->default('myself')
                ->after('commission_percent');

            $table->text('medical_notes')
                ->nullable()
                ->after('scheduled_start_time');

            $table->string('risk_agreement_version', 20)
                ->nullable()
                ->after('medical_notes');

            $table->timestamp('consent_timestamp')
                ->nullable()
                ->after('risk_agreement_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_address_id']);
            $table->dropColumn([
                'customer_address_id',
                'recipient_type',
                'medical_notes',
                'risk_agreement_version',
                'consent_timestamp',
            ]);
        });
    }
};
