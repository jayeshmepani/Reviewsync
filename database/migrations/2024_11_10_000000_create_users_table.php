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
            $table->string('phone')->nullable();
            $table->string('password');
            $table->string('profile_picture')->nullable();
            $table->string('google_avatar_original')->nullable();
            
            // Google OAuth specific fields
            $table->string('google_id')->nullable();
            $table->string('google_token')->nullable();
            $table->text('google_refresh_token')->nullable();
            $table->integer('google_expires_in')->nullable();
            $table->text('google_scopes')->nullable();
            $table->boolean('email_verified')->default(false);
            
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}