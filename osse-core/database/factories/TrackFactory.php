<?php

namespace Database\Factories;

use App\Models\Album;
use App\Models\Artist;
use App\Models\CoverArt;
use App\Models\Track;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Track>
 */
class TrackFactory extends Factory
{
    protected $model = Track::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'duration' => $this->faker->numberBetween(120, 600),
            'size' => $this->faker->numberBetween(1000000, 10000000),
            'bitrate' => $this->faker->randomElement([128, 192, 256, 320]),
            'location' => $this->faker->filePath(),
            'album_id' => null,
            'year' => $this->faker->year,
            'track_number' => null,
            'disc_number' => null,
            'cover_art_id' => null,
            'scanned_at' => now(),
        ];
    }

    /**
     * @param  mixed  $count
     */
    public function withArtists($count = 1): TrackFactory
    {
        return $this->afterCreating(function (Track $track) use ($count) {
            $artists = Artist::factory()->count($count)->create();
            foreach ($artists as $index => $artist) {
                $track->artists()->attach($artist->id, ['artist_order' => $index + 1]);
            }
        });
    }

    public function withAlbum(): TrackFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'album_id' => Album::factory(),
            ];
        });
    }

    public function withCoverArt(): TrackFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'cover_art_id' => CoverArt::factory(),
            ];
        });
    }
}
