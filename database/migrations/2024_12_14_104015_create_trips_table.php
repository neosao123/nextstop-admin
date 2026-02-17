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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
			$table->string('trip_unique_id')->unique();
			$table->integer('trip_customer_id');
			$table->integer('trip_vehicle_id');
			$table->integer('trip_vehicle_distance')->nullable();
			$table->integer('trip_vehicle_time')->nullable();
			$table->integer('trip_goods_type_id')->nullable();
			$table->integer('trip_coupon_id')->nullable();
			$table->integer('trip_driver_id')->nullable();
			$table->decimal('trip_fair_amount', 20, 2)->default(0);
			$table->decimal('trip_netfair_amount', 20, 2)->default(0);
			$table->decimal('trip_discount', 20, 2)->default(0);
			$table->decimal('trip_total_amount', 20, 2)->default(0);
			$table->string('trip_status')->nullable();
			
			$table->string('trip_latitude')->nullable();
			$table->string('trip_longitude')->nullable();
			$table->string('trip_txn_id')->nullable();
			$table->longText('trip_payment_response')->nullable();
			$table->string('trip_payment_order_id')->nullable();
			$table->string('trip_payment_id')->nullable();
			$table->string('trip_payment_status')->nullable();
			$table->string('trip_payment_mode')->nullable();
			$table->integer('trip_source_address_id')->nullable();
			$table->integer('trip_destination_address_id')->nullable();
			$table->integer('trip_merchant_id')->nullable();
			$table->integer('trip_transaction_id')->nullable();
			$table->integer('trip_merchant_user_id')->nullable();
			$table->longText('webhook_response')->nullable();
			$table->longText('refund_webhook_response')->nullable();
			$table->integer('trip_distance')->default(0);
			$table->integer('trip_vehicle_fix_distance')->default(0);
			$table->decimal('trip_vehicle_fix_amount', 20, 2)->default(0);
			$table->decimal('trip_vehicle_per_km_amount', 20, 2)->default(0);
			$table->integer('is_active')->default(1);
            $table->integer('is_delete')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
