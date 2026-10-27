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
        Schema::table('users', function (Blueprint $table) {
            $table->string('status', 40)->default('belum_lengkap')->after('role');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->text('rejection_reason')->nullable()->after('status');
            $table->string('rejection_code', 50)->nullable()->after('rejection_reason');

            $table->index(['role', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'status']);
            $table->dropColumn(['status', 'phone_verified_at', 'rejection_reason', 'rejection_code']);
        });
    }
};
