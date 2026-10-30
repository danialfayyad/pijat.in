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
        Schema::table('complaint_categories', function (Blueprint $table) {
            $table->unsignedInteger('penalty_points')->default(15)->after('description');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('penalty_points')->default(0)->after('rejection_code');
            $table->timestamp('suspended_until')->nullable()->after('penalty_points');
        });

        Schema::create('penalty_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('complaint_id')->nullable()->constrained('complaints')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['violation', 'merit_redemption', 'appeal_reversal', 'admin_adjustment']);
            $table->integer('points'); // positive for penalty (+15), negative for redemption (-1)
            $table->unsignedInteger('balance_before');
            $table->unsignedInteger('balance_after');
            $table->text('reason');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalty_ledgers');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['penalty_points', 'suspended_until']);
        });

        Schema::table('complaint_categories', function (Blueprint $table) {
            $table->dropColumn('penalty_points');
        });
    }
};
