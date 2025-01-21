<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationsTable extends Migration
{

    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // Unique identifier
            $table->string('store_code')->nullable(); // Store code
            $table->string('name')->nullable(); // business id
            $table->string('title')->nullable(); // Title or name of the business
            $table->string('website_uri')->nullable(); // Website URL
            $table->string('primary_phone')->nullable(); // Primary phone number
            $table->string('primary_category')->nullable(); // Primary category
            $table->string('address_lines')->nullable(); // Address lines
            $table->string('locality')->nullable(); // City/Locality
            $table->string( 'region')->nullable(); // State/Region
            $table->string('postal_code')->nullable(); // Postal code
            $table->string('country_code', 2)->nullable(); // Country code
            $table->decimal('latitude', 10, 7)->nullable(); // Latitude
            $table->decimal('longitude', 10, 7)->nullable(); // Longitude
            $table->string('status')->nullable(); // Status of the location
            $table->text('description')->nullable(); // Business description
            $table->string('place_id')->nullable(); // Google Maps place ID
            $table->text('maps_uri')->nullable(); // Google Maps URI
            $table->text('new_review_uri')->nullable(); // New review URI
            $table->text('formatted_address')->nullable(); // Formatted address
            $table->unsignedBigInteger('user_id'); // Foreign key for user
            $table->timestamps();
            $table->boolean('is_visible')->default(true);
    
            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('locations');
    }
}
