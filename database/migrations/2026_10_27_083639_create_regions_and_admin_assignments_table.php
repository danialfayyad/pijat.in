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
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->geometry('boundaries', srid: 4326);
            $table->string('timezone')->default('Asia/Jakarta');
            $table->enum('status', ['aktif', 'nonaktif'])->default('nonaktif');
            $table->timestamps();

            $table->spatialIndex('boundaries');
            $table->index('status');
        });

        Schema::create('admin_region_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->boolean('can_handle_emergency')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'region_id']);
            $table->index(['region_id', 'can_handle_emergency']);
        });

        Schema::create('admin_menu_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('menu_code');
            $table->timestamps();

            $table->unique(['user_id', 'menu_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_menu_permissions');
        Schema::dropIfExists('admin_region_assignments');
        Schema::dropIfExists('regions');
    }
};
