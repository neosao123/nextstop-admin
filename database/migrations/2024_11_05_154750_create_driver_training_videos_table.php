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
        Schema::create('driver_training_videos', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('driver_id');
			$table->tinyInteger('is_active')->default(1);
            $table->tinyInteger('is_delete')->default(0);
			$table->text('training_video_verification_reason');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_training_videos');
    }
};
