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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type')->default('image'); // 'image' or 'template'
            $table->string('image_path')->nullable();
            $table->string('badge_text')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('promo_code')->nullable();
            $table->string('action_url')->nullable();
            $table->string('action_text')->nullable();
            $table->string('background_color')->default('emerald'); // emerald, teal, slate, amber, indigo, rose
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
