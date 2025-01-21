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
        User::factory(10)->create();

        // Ensure User with ID 2 exists
        $user = User::find(3);

        if (!$user) {
            // Exit or log an error if User ID 2 is missing
            $this->command->error('User with ID 2 does not exist. Please ensure the user is created before running this seeder.');
            return;
        }

        // Ensure Location with ID 1 exists
        $location = Location::firstOrCreate(
            ['id' => 1], // Ensure location with ID 1
            [
                'uuid' => Uuid::uuid4()->toString(),
                'name' => 'Sample Business',
                'title' => 'Sample Title',
                'user_id' => $user->id,
            ]
        );

        // Seed Reviews under Location with ID 1
        Review::factory(10)->create([
            'location_id' => $location->id, // Associate reviews with the specific location
        ]);

        // Seed 10 additional locations for User ID 2
        Location::factory(10)->create([
            'user_id' => $user->id,
        ]);
    }
}
