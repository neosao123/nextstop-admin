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
        Schema::create('trip_statuses', function (Blueprint $table) {
            $table->id();
			$table->integer("trip_id");
			$table->string("trip_status_title");
			$table->string("trip_status_short");
			$table->string("trip_status_reason");
			$table->longText("trip_status_description");
			$table->integer("trip_action_by")->nullable();
			$table->string("trip_action_type")->nullable();
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
        Schema::dropIfExists('trip_statuses');
    }
};
