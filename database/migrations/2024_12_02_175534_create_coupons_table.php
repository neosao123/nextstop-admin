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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('coupon_code')->nullable();
            $table->text('coupon_image')->nullable();
            $table->string('coupon_type')->nullble()->comment('flat or percent');
            $table->decimal('coupon_amount_or_percentage', 10, 2)->nullable();
            $table->decimal('coupon_cap_limit', 10, 2)->nullable();
            $table->decimal('coupon_min_order_amount', 10, 2)->nullable();
            $table->text('coupon_description')->nullable();
            $table->unsignedTinyInteger('is_active')->default(1);
            $table->unsignedTinyInteger('is_delete')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
