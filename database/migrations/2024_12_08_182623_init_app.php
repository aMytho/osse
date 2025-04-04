<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('artists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('year')->nullable();
            $table->timestamps();
        });

        Schema::create('album_artist', function (Blueprint $table) {
            $table->foreignId('album_id');
            $table->foreignId('artist_id');
            $table->integer('artist_order');
        });

        Schema::create('playlists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('duration');
            $table->bigInteger('size');
            $table->integer('bitrate')->nullable();
            $table->string('location');
            $table->foreignId('album_id')->nullable();
            $table->integer('year')->nullable();
            $table->integer('track_number')->nullable();
            $table->integer('disc_number')->nullable();
            $table->integer('cover_art_id')->nullable();
            // This is used for comparing changes to the tag.
            $table->timestamp('scanned_at');
            // Standard timestamp. If we ever implement db modifications, this would be useful.
            $table->timestamps();
        });

        Schema::create('track_artist', function (Blueprint $table) {
            $table->foreignId('track_id');
            $table->foreignId('artist_id');
            $table->integer('artist_order');
        });

        Schema::create('cover_art', function (Blueprint $table) {
            $table->id();
            // 64 length SHA-256 hash of the file. This is also the filename
            $table->string('hash', 64);
            $table->string('mime_type');
            $table->timestamps();
        });

        Schema::create('playlist_track', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id');
            $table->foreignId('playlist_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
