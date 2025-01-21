<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Review;
use App\Models\Location;
use App\Models\User;
use Ramsey\Uuid\Uuid;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Review::class;

    public function definition()
    {
        return [
            'review_id' => Uuid::uuid4()->toString(),
            'user_id' =>  User::factory(),
            'reviewer_name' => $this->faker->name(),
            'profile_photo_url' => $this->faker->imageUrl(100, 100, 'people', true),
            'star_rating' => $this->faker->randomElement(['ONE', 'TWO', 'THREE', 'FOUR', 'FIVE']),
            'comment' => $this->faker->sentence(10),
            'create_time' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'update_time' => $this->faker->optional()->dateTimeBetween('-6 months', 'now'),
            'reply_comment' => $this->faker->optional()->sentence(15),
            'reply_update_time' => $this->faker->optional()->dateTimeBetween('-3 months', 'now'),
            'review_name' => $this->faker->sentence(3),
            'location_id' => Location::factory(), // Assuming a `Location` factory exists
        ];
    }
}
