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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
			$table->integer('rating_customer_id')->nullable();
			$table->integer('rating_trip_id')->nullable();
			$table->integer('rating_driver_id')->nullable();
			$table->integer('rating_value')->nullable();
			$table->string('rating_given_by')->nullable();
			$table->longText('rating_description')->nullable();
			$table->tinyInteger('is_active')->default(1);
            $table->tinyInteger('is_delete')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
