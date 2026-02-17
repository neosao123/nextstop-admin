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
        Schema::create('driver_training_videos_details', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('driver_id');
			$table->unsignedBigInteger('training_video_id');
			$table->string('last_watched_time')->nullable();
			$table->tinyInteger('checked_status')->default(0);
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
        Schema::dropIfExists('driver_training_videos_details');
    }
};
