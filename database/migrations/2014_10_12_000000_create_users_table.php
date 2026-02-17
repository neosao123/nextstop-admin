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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
			$table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone_number')->nullable();
            $table->string('avatar')->nullable();
            $table->string('role_id')->nullable();
			$table->string('zone_id')->nullable();
			$table->text('firebase_id')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->integer('is_active')->default(1);
            $table->integer('is_delete')->default(0);
			$table->integer('is_block')->default(0);
            $table->bigInteger('add_id')->nullable();
            $table->string('add_ip')->nullable();
			$table->timestamp('add_date')->nullable();
            $table->bigInteger('edit_id')->nullable();
            $table->string('edit_ip')->nullable();
			$table->timestamp('edit_date')->nullable();
            $table->bigInteger('delete_id')->nullable();
            $table->string('delete_ip')->nullable();
			$table->timestamp('delete_date')->nullable();
			$table->text('reset_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
