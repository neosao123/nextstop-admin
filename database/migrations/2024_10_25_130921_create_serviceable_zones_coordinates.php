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
        Schema::create('serviceable_zones_coordinates', function (Blueprint $table) {
            $table->id();
			$table->integer('zonal_id');
			$table->string('latitude')->nullable();
			$table->string('longitude')->nullable();			
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serviceable_zones_coordinates');
    }
};
