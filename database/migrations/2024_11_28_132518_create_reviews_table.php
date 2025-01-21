<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('review_id')->unique(); // Unique Google Review ID
            $table->string('reviewer_name'); // Reviewer's display name
            $table->string('profile_photo_url')->nullable(); // URL of reviewer's profile photo
            $table->enum('star_rating', ['ONE', 'TWO', 'THREE', 'FOUR', 'FIVE']); // Star rating
            $table->text('comment')->nullable(); // Review comment
            $table->timestamp('create_time'); // Creation time of the review
            $table->timestamp('update_time')->nullable(); // Update time of the review
            $table->text('reply_comment')->nullable(); // Reply to the review
            $table->timestamp('reply_update_time')->nullable(); // Reply update time
            $table->string('review_name'); // Full review name
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade'); // Foreign key to locations
            $table->timestamps(); // Laravel's created_at and updated_at

            $table->index('review_id');
            $table->index('user_id');
            $table->index('location_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}
