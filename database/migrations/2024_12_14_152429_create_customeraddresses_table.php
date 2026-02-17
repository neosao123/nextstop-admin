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
        Schema::create('customeraddresses', function (Blueprint $table) {
            $table->id();
			$table->integer("customeraddresses_customer_id")->nullable();
			$table->string("customeraddresses_address")->nullable();
			$table->string("customeraddresses_mobile")->nullable();
			$table->string("customeraddresses_type")->nullable();
			$table->string("customeraddresses_latitude")->nullable();
			$table->string("customeraddresses_longitude")->nullable();
			$table->string("customeraddresses_trip_id")->nullable();
			$table->string("customeraddresses_name")->nullable();
			$table->string("customeraddresses_location_type")->nullable();
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
        Schema::dropIfExists('customeraddresses');
    }
};
