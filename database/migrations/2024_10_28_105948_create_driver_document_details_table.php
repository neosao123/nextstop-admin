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
        Schema::create('driver_document_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->string('document_type');
            $table->string('document_number');
            $table->string('document_1_file_type')->nullable();
            $table->string('document_2_file_type')->nullable();
            $table->text('document_1')->nullable();
            $table->text('document_2')->nullable();
            $table->tinyInteger('document_verification_status')->default(0)->comment('0-pending, 1-approved, 2-rejected');
            $table->dateTime('document_uploaded_at');
            $table->unsignedBigInteger('document_verified_by')->nullable();
            $table->dateTime('document_verified_at')->nullable();
            $table->text('document_verification_reason')->nullable();
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
        Schema::dropIfExists('driver_document_details');
    }
};
