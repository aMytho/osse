<?php

namespace Database\Factories;

use App\Models\Playlist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Playlist>
 */
class PlaylistFactory extends Factory
{
    protected $model = Playlist::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
        ];
    }

    /**
     * @param  mixed  $count
     */
    public function withTracks($count = 5): PlaylistFactory
    {
        return $this->afterCreating(function (Playlist $playlist) use ($count) {
            $tracks = \App\Models\Track::factory()->count($count)->create();
            $playlist->tracks()->attach($tracks->pluck('id'));
        });
    }
}
