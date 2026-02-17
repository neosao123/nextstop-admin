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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_first_name');
            $table->string('customer_last_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_otp', 6)->nullable();
            $table->unsignedBigInteger('customer_affiliate_id')->nullable();
            $table->string('customer_avatar')->nullable();
            $table->string('customer_password');
            $table->decimal('customer_wallet_balance', 10, 2)->default(0);
            $table->boolean('customer_email_verified')->default(false);
            $table->boolean('customer_phone_verified')->default(false);
            $table->boolean('customer_has_business')->default(false);
            $table->string('customer_business_name')->nullable();
            $table->string('customer_business_tax_number')->nullable();
            $table->string('customer_business_type')->nullable();
            $table->string('customer_account_status')->default('1');
			$table->string('customer_referral_code')->nullable();
			$table->string('customer_referral_by')->nullable();
			$table->integer('customer_referral_by_id')->nullable();
			$table->longText('customer_firebase_token')->nullable();
			
			$table->integer('is_active')->default(1);
            $table->integer('is_delete')->default(0);
			$table->integer('is_block')->default(0);
			$table->tinyInteger('is_customer_delete')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
