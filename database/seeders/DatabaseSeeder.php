<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Location;
use App\Models\Review;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed the superadmin
        $this->call(SuperAdminSeeder::class);

        // Seed 10 random users
        // User::factory(3)->create();

        // // Ensure User with ID 2 exists
        // $user = User::find(2);

        // if (!$user) {
        //     // Exit or log an error if User ID 2 is missing
        //     $this->command->error('User with ID 2 does not exist. Please ensure the user is created before running this seeder.');
        //     return;
        // }

        // // Seed 10 locations for User ID 3
        // Location::factory(15)->create([
        //     'user_id' => 2,  // Associate locations with User ID 3
        // ]);

        // // Seed Reviews under Location with ID 1 for User ID 3
        // Review::factory(10)->create([
        //     'location_id' => 1,  // Associate reviews with Location ID 1
        //     'user_id' => 2,      // Associate reviews with User ID 3
        // ]);
    }
}
