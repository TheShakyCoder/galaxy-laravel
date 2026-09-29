<?php

namespace Database\Factories;

use App\Models\GameServer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<GameServer>
 */
class GameServerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slug = fake()->unique()->slug(2);

        return [
            'slug' => $slug,
            'name' => Str::headline($slug),
            'nakama_host' => $slug.'.example.test',
            'nakama_port' => 443,
            'nakama_ssl' => true,
            'nakama_server_key' => 'defaultkey',
            'play_token_secret' => Str::random(64),
            'http_key' => Str::random(32),
            'is_open' => true,
            'sort' => 0,
        ];
    }

    /**
     * Indicate that the server isn't accepting players.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_open' => false,
        ]);
    }
}
