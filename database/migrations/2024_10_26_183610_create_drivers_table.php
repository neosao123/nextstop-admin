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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('driver_first_name');
            $table->string('driver_last_name');
            $table->string('driver_phone');
            $table->string('driver_gender')->nullable();
            $table->string('driver_email')->nullable();
            $table->string('driver_otp')->nullable();
            $table->string('driver_photo')->nullable();
            $table->text('driver_status')->nullable();
            $table->tinyInteger('is_driver_delete')->default(0);
            $table->tinyInteger('driver_document_verification_status')->default(0);
            $table->tinyInteger('driver_vehicle_verification_status')->default(0);
            $table->tinyInteger('driver_vehicle_document_verification_status')->default(0);
            $table->tinyInteger('driver_training_video_verification_status')->default(0);
            $table->bigInterger('driver_serviceable_location')->nullable();
            $table->tinyInteger('is_driver_block')->nullable();
            $table->string('driver_latitude')->nullable();
			$table->string('driver_longitude')->nullable();
			$table->tinyInteger('is_active')->default(0)->comment('1 is Active Account & 0 is Blocked Account');
            $table->tinyInteger('is_delete')->default(0);
			$table->tinyInteger('driver_online_offline_status')->default(0);
            $table->decimal('driver_wallet',20, 2)->nullable();
			$table->longText('driver_firebase_token')->nullable();
			$table->tinyInteger('admin_verification_status')->default(0);
			$table->longText('admin_verification_reason')->nullable();
			$table->string('minimum_wallet_amount')->nullable();
			$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
