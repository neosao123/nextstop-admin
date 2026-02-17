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
        Schema::create('training_videos', function (Blueprint $table) {
            $table->id();
			$table->longText("video_title")->nullable();
			$table->longText("video_path")->nullable();
			$table->longText("thumbnail")->nullable();
			$table->tinyInteger("is_active")->nullable();
			$table->tinyInteger("is_delete")->nullable();
			$table->string("total_video_time_length")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_videos');
    }
};
