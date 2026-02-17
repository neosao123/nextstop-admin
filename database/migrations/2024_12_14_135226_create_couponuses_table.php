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
        Schema::create('couponuses', function (Blueprint $table) {
            $table->id();
			$table->integer("couponuses_customer_id");
			$table->integer("couponuses_trip_id");
			$table->integer("couponuses_coupon_id");
			$table->dateTime("couponuses_used_date");
			$table->integer("couponuses_decided_exisiting_limit");
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
        Schema::dropIfExists('couponuses');
    }
};
