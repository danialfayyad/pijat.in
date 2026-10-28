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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 30)->unique();
            $table->string('order_type', 20)->default('terjadwal');
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mitra_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('region_id')->constrained('regions')->restrictOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('service_package_id')->nullable()->constrained('service_packages')->nullOnDelete();
            $table->string('service_name');
            $table->integer('duration_minutes');
            $table->decimal('total_price', 14, 2);
            $table->decimal('commission_percent', 5, 2)->default(10.00);
            $table->string('recipient_name');
            $table->string('recipient_gender', 20);
            $table->string('recipient_phone', 25)->nullable();
            $table->text('service_address_text');
            $table->geometry('service_coordinate', subtype: 'point', srid: 4326);
            $table->timestamp('scheduled_start_time')->nullable();
            $table->string('status', 30)->default('menunggu_pembayaran');
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cancelled_by_role', 30)->nullable();
            $table->unsignedTinyInteger('reschedule_count')->default(0);
            $table->timestamps();

            $table->index(['region_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['mitra_id', 'status']);
            $table->index('scheduled_start_time');
            $table->spatialIndex('service_coordinate');
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_role', 30)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('orders');
    }
};
