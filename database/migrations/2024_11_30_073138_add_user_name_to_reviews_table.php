<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserNameToReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Check if the column already exists before adding it
            if (!Schema::hasColumn('reviews', 'user_name')) {
                $table->string('user_name')->nullable();
            }
        });
    }
      

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('user_name'); // Remove the 'user_name' column on rollback
        });
    }
}
