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
        Schema::create('driver_bank_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->string('driver_bank_name');
            $table->string('driver_bank_account_number');
            $table->string('driver_bank_ifsc_code');
            $table->string('driver_bank_branch_name');
            $table->tinyInteger('is_bank_account_verified')->default(0);
          	$table->tinyInteger('bank_verified_by')->nullable();
          	$table->dateTime('bank_verified_at')->nullable();
          	$table->text('bank_verification_reason')->nullable();
            $table->tinyInteger('is_bank_account_active')->default(1);
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
        Schema::dropIfExists('driver_bank_details');
    }
};
