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
            $table->text('status_reason')->nullable()->after('status');
            $table->string('device_id', 120)->nullable()->after('phone');
        });

        Schema::table('order_ratings', function (Blueprint $table) {
            $table->text('review')->nullable()->after('score');
        });

        Schema::create('blacklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('phone', 30)->nullable()->index();
            $table->string('device_id', 120)->nullable()->index();
            $table->text('reason');
            $table->foreignId('blocked_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['phone', 'device_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blacklists');

        Schema::table('order_ratings', function (Blueprint $table) {
            $table->dropColumn('review');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status_reason', 'device_id']);
        });
    }
};

