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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('type', 20)->default('fixed'); // 'fixed' (nominal rupiah) atau 'percentage' (persen)
            $table->decimal('amount', 14, 2); // nominal potongan rupiah atau angka persentase
            $table->decimal('max_discount_amount', 14, 2)->nullable(); // plafon maksimal diskon untuk tipe persentase
            $table->decimal('min_order_amount', 14, 2)->default(0); // syarat minimum nominal transaksi
            $table->unsignedInteger('quota')->nullable(); // total kuota pemakaian nasional (null = tanpa batas)
            $table->unsignedInteger('used_count')->default(0); // jumlah yang telah terpakai
            $table->unsignedInteger('per_user_limit')->default(1); // batas pemakaian per akun pelanggan
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'starts_at', 'expires_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'voucher_id')) {
                $table->foreignId('voucher_id')->nullable()->after('service_id')->constrained('vouchers')->nullOnDelete();
            }
            if (! Schema::hasColumn('orders', 'discount_amount')) {
                $table->decimal('discount_amount', 14, 2)->default(0)->after('total_price');
            }
        });

        Schema::create('voucher_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->timestamps();

            $table->index(['voucher_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_usages');

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'voucher_id')) {
                $table->dropForeign(['voucher_id']);
                $table->dropColumn('voucher_id');
            }
            if (Schema::hasColumn('orders', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }
        });

        Schema::dropIfExists('vouchers');
    }
};
