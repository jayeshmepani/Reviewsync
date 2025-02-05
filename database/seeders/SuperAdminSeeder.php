<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $superadminEmail = 'superadmin@mail.com';

        // Check if the superadmin already exists
        $existingSuperadmin = User::where('email', $superadminEmail)->first();

        if (!$existingSuperadmin) {
            // Create a superadmin
            User::create([
                'id' => 1,
                'name' => 'Super Admin',
                'email' => $superadminEmail,
                'password' => Hash::make('superadmin123'),
                'role' => 'superadmin',
                'subscription' => null,
                'email_verified' => true,
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'uuid' => Str::uuid()
            ]);

            $this->command->info('Superadmin created successfully with email: ' . $superadminEmail);
        } else {
            $this->command->info('Superadmin already exists with email: ' . $superadminEmail);
        }
    }
}
