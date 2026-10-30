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
        Schema::table('penalty_appeals', function (Blueprint $table) {
            $table->json('evidence_photos')->nullable()->after('statement_letter_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penalty_appeals', function (Blueprint $table) {
            $table->dropColumn('evidence_photos');
        });
    }
};
