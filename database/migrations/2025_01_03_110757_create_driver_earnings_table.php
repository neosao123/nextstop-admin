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
        Schema::create('driver_earnings', function (Blueprint $table) {
            $table->id();
			$table->integer('trip_id')->nullable();
			$table->integer('driver_id')->nullable();
			$table->string('type')->nullable();
			$table->longText('message')->nullable();
			$table->decimal('amount',20, 2)->nullable();
            $table->string('status')->nullable();
			$table->string('paymentMode')->nullable();
			$table->string('payment_status')->nullable();
			$table->longText('payment_response')->nullable();
			$table->string('payment_id')->nullable();
			$table->string('added_form')->nullable();
			$table->string('payment_order_id')->nullable(); 
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
        Schema::dropIfExists('driver_transactions');
    }
};
