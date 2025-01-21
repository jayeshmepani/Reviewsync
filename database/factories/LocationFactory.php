<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ramsey\Uuid\Uuid;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'uuid' => Uuid::uuid4()->toString(),
            'store_code' => $this->faker->bothify('SC-####'),
            'name' => $this->faker->company(),
            'title' => $this->faker->companySuffix(),
            'website_uri' => $this->faker->url(),
            'primary_phone' => $this->faker->phoneNumber(),
            'primary_category' => $this->faker->word(),
            'address_lines' => $this->faker->streetAddress(),
            'locality' => $this->faker->city(),
            'region' => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'country_code' => $this->faker->countryCode(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'status' => $this->faker->randomElement(['Active', 'Inactive']),
            'description' => $this->faker->paragraph(),
            'place_id' => Uuid::uuid4()->toString(),
            'maps_uri' => $this->faker->url(),
            'new_review_uri' => $this->faker->url(),
            'formatted_address' => $this->faker->address(),
            'user_id' => User::factory(), // Link to a user
        ];
    }
}
