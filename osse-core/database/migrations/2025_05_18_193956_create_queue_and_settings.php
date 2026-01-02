<?php

use App\Models\PlaybackSession;
use App\Models\UserSetting;
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
        Schema::create('playback_sessions', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
            $table->integer('active_track_index')->nullable(); // Index representing the active track
            $table->json('tracks')->nullable(); // JSON array of track ids.
            $table->integer('track_position')->default(0);
            $table->timestamps();
        });

        Schema::create('user_settings', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
            $table->boolean('enable_playback_session');
        });

        // Enable queue for osse.
        UserSetting::insert([
            'user_id' => 1,
            'enable_playback_session' => 1,
        ]);

        // Make playback session for osse.
        $now = now();
        PlaybackSession::insert([
            'user_id' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playback_sessions');
        Schema::dropIfExists('user_settings');
    }
};
