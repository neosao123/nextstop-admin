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
        Schema::create('admin_commissions', function (Blueprint $table) {
            $table->id();
			$table->integer('trip_id')->nullable();
			$table->integer('driver_id')->nullable();
			$table->string('type')->nullable();
			$table->decimal('commission_percentage',10, 2)->nullable();
            $table->decimal('subtotal',10, 2)->nullable();
			$table->decimal('commission_amount',10, 2)->nullable();
			$table->decimal('admin_amount',10, 2)->nullable();
			$table->decimal('grand_total',10, 2)->nullable();
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
        Schema::dropIfExists('admin_commissions');
    }
};
