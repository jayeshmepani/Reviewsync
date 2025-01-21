<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('local_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('review_id')->unique();
            $table->string('reviewer_name');
            $table->enum('star_rating', ['ONE', 'TWO', 'THREE']);
            $table->text('comment')->nullable();
            $table->timestamp('create_time');
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('local_reviews');
    }
};