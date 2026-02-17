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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_type_id');
            $table->string('vehicle_name');
            $table->string('vehicle_icon')->nullable();
            $table->string('vehicle_dimensions')->comment('L X B X H');
			$table->integer('vehicle_fixed_km')->nullable();
			$table->decimal('vehicle_fixed_km_delivery_charge', 20, 2);
            $table->decimal('vehicle_max_load_capacity', 20, 2)->comment('in kgs');
            $table->decimal('vehicle_per_km_delivery_charge', 20, 2);
            $table->decimal('vehicle_per_km_extra_delivery_charge', 20, 2);
            $table->text('vehicle_description')->nullable();
            $table->text('vehicle_rules')->nullable();
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
        Schema::dropIfExists('vehicles');
    }
};
