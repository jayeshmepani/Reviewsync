<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->string('name');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('remember_token', 100)->nullable();
            $table->string('profile_picture')->nullable();

            // Google OAuth specific fields
            $table->string('google_id')->nullable();
            $table->text('google_token')->nullable();
            $table->boolean('email_verified')->default(false);
            $table->enum('role', ['user', 'superadmin'])->default('user');
            $table->enum('subscription', ['trial', 'standard', 'premium'])->nullable()->default('trial');
            $table->timestamp('subscription_billing_start')->nullable();
            $table->timestamp('subscription_billing_end')->nullable();
            $table->boolean('payment_pending')->default(false);
            $table->enum('pending_subscription', ['trial', 'standard', 'premium'])->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
