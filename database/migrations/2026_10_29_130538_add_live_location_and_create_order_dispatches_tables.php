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
        Schema::table('partner_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('partner_profiles', 'current_coordinate')) {
                $table->geometry('current_coordinate', subtype: 'point', srid: 4326)->nullable()->after('coordinate');
            }
            if (! Schema::hasColumn('partner_profiles', 'last_active_at')) {
                $table->timestamp('last_active_at')->nullable()->after('is_active');
            }
        });

        if (! Schema::hasTable('order_dispatches')) {
            Schema::create('order_dispatches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('offered_at');
                $table->timestamp('expires_at');
                $table->string('status', 30)->default('offered');
                $table->timestamp('responded_at')->nullable();
                $table->string('rejection_reason')->nullable();
                $table->timestamps();

                $table->index(['order_id', 'status']);
                $table->index(['partner_id', 'status']);
                $table->index(['expires_at', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_dispatches');

        Schema::table('partner_profiles', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('partner_profiles', 'current_coordinate')) {
                $columnsToDrop[] = 'current_coordinate';
            }
            if (Schema::hasColumn('partner_profiles', 'last_active_at')) {
                $columnsToDrop[] = 'last_active_at';
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
