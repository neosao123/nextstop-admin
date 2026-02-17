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
        Schema::create('driver_vehicle_document_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->string('document_type');
            $table->string('document_number');
            $table->date('document_expiry_date');
            $table->string('document_file_type');
            $table->text('document_file_path');
            $table->tinyInteger('document_verification_status')->default(0);
            $table->dateTime('document_uploaded_at');
            $table->unsignedBigInteger('document_verified_by')->nullable();
            $table->dateTime('document_verified_at')->nullable();
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
        Schema::dropIfExists('driver_vehicle_document_details');
    }
};
