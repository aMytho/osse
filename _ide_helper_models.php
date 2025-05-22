<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property int|null $year
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Artist> $artists
 * @property-read int|null $artists_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Track> $tracks
 * @property-read int|null $tracks_count
 * @method static \Database\Factories\AlbumFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Album newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Album newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Album query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Album whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Album whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Album whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Album whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Album whereYear($value)
 * @mixin \Eloquent
 */
	class Album extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Album> $albums
 * @property-read int|null $albums_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Track> $tracks
 * @property-read int|null $tracks_count
 * @method static \Database\Factories\ArtistFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Artist whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Artist extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $hash
 * @property string $mime_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Track> $tracks
 * @property-read int|null $tracks_count
 * @method static \Database\Factories\CoverArtFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverArt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverArt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverArt query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverArt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverArt whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverArt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverArt whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoverArt whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class CoverArt extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $active_track_index
 * @property int $track_position
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlaybackSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlaybackSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlaybackSession query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlaybackSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlaybackSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlaybackSession whereActiveTrackIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlaybackSession whereTrackPosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlaybackSession whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlaybackSession whereUserId($value)
 * @property array<array-key, mixed>|null $tracks
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlaybackSession whereTracks($value)
 */
	class PlaybackSession extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Track> $tracks
 * @property-read int|null $tracks_count
 * @method static \Database\Factories\PlaylistFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Playlist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Playlist newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Playlist query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Playlist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Playlist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Playlist whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Playlist whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Playlist extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $scan_job_id
 * @property string $path
 * @property string $status
 * @property int $files_scanned
 * @property int $files_skipped
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property-read \App\Models\ScanJob $job
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory whereFilesScanned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory whereFilesSkipped($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory whereScanJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanDirectory whereStatus($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ScanError> $errors
 * @property-read int|null $errors_count
 */
	class ScanDirectory extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $scan_directory_id
 * @property string $error
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\ScanDirectory $directory
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanError newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanError newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanError query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanError whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanError whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanError whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanError whereScanDirectoryId($value)
 */
	class ScanError extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property string $status
 * @property int $total_dirs
 * @property int $scanned_dirs
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ScanDirectory> $directories
 * @property-read int|null $directories_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanJob newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanJob newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanJob query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanJob whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanJob whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanJob whereScannedDirs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanJob whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanJob whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScanJob whereTotalDirs($value)
 * @mixin \Eloquent
 */
	class ScanJob extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property int $duration
 * @property int $size
 * @property int|null $bitrate
 * @property string $location
 * @property int|null $album_id
 * @property int|null $year
 * @property int|null $track_number
 * @property int|null $disc_number
 * @property int|null $cover_art_id
 * @property \Illuminate\Support\Carbon $scanned_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Album|null $album
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Artist> $artists
 * @property-read int|null $artists_count
 * @property-read \App\Models\CoverArt|null $coverArt
 * @method static \Database\Factories\TrackFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereAlbumId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereBitrate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereCoverArtId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereDiscNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereScannedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereTrackNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereYear($value)
 * @mixin \Eloquent
 */
	class Track extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 * @mixin \Eloquent
 * @property-read \App\Models\PlaybackSession|null $playbackSession
 * @property-read \App\Models\UserSetting|null $settings
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $user_id
 * @property bool $enable_playback_session
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereEnablePlaybackSession($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSetting whereUserId($value)
 */
	class UserSetting extends \Eloquent {}
}

