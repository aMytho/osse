<?php

namespace Database\Factories;

use App\Models\CoverArt;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends Factory<CoverArt>
 */
class CoverArtFactory extends Factory
{
    protected $model = CoverArt::class;

    public function definition(): array
    {
        $disk = Storage::disk('test_cover_art');

        // Generate fake image
        $fakeImage = UploadedFile::fake()->image('cover.jpg', 100, 100);

        // Get the hash of the file contents. We use a uuid in place of file contents since laravel fake files have the same content.
        $hash = hash('sha256', Str::uuid()->toString());
        $filename = "{$hash}";

        // Store it in the fake disk
        $disk->putFileAs('cover-art', $fakeImage, $filename);

        return [
            'hash' => $hash,
            'mime_type' => 'image/jpeg',
        ];
    }
}
