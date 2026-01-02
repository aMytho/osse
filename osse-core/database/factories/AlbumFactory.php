<?php

namespace Database\Factories;

use App\Models\Album;
use App\Models\Artist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Album>
 */
class AlbumFactory extends Factory
{
    protected $model = Album::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'year' => $this->faker->year,
        ];
    }

    /**
     * @param  mixed  $count
     */
    public function withArtists($count = 1): AlbumFactory
    {
        return $this->afterCreating(function (Album $album) use ($count) {
            $artists = Artist::factory()->count($count)->create();
            foreach ($artists as $index => $artist) {
                $album->artists()->attach($artist->id, ['artist_order' => $index + 1]);
            }
        });
    }
}
