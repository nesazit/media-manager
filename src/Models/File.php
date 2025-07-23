<?php

namespace Nesazit\MediaManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use Nesazit\MediaManager\Traits\HasMediaPermissions;

class File extends Model
{
    use HasMediaPermissions;

    protected $table = 'files';

    protected $fillable = [
        'name',
        'original_name',
        'file_type',
        'mime_type',
        'extension',
        'size',
        'disk',
        'path',
        'directory_id',
        'owner_id',
        'owner_type',
        'metadata',
        'permissions',
        'is_public',
        'description',
        'alt_text',
        'thumbnails',
        'is_locked',
        'last_accessed_at',
        'download_count'
    ];

    protected $casts = [
        'metadata' => 'array',
        'permissions' => 'array',
        'thumbnails' => 'array',
        'is_public' => 'boolean',
        'is_locked' => 'boolean',
        'last_accessed_at' => 'datetime',
        'download_count' => 'integer',
    ];

    protected $appends = [
        'full_path',
        'url',
        'formatted_size',
        'is_image'
    ];

    // Relations
    public function directory(): BelongsTo
    {
        return $this->belongsTo(Directory::class, 'directory_id');
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    // Accessors
    public function getFullPathAttribute(): string
    {
        return $this->path;
    }

    public function getUrlAttribute(): string
    {
        if ($this->disk === 'public') {
            return Storage::disk($this->disk)->url($this->path);
        }

        // For private files, return a route that handles access control
        return route('media-manager.file.serve', [
            'file' => $this->id,
            'name' => $this->name
        ]);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getIsImageAttribute(): bool
    {
        return $this->file_type === 'image';
    }

    // Methods
    public function moveToDirectory(?Directory $destination): bool
    {
        $oldPath = $this->path;
        $newPath = ($destination ? $destination->full_path : '/') . $this->name;

        if (Storage::disk($this->disk)->move($oldPath, $newPath)) {
            $this->update([
                'path' => $newPath,
                'directory_id' => $destination?->id
            ]);
            return true;
        }

        return false;
    }

    public function deleteFile(): bool
    {
        // Delete thumbnails first
        if ($this->thumbnails) {
            foreach ($this->thumbnails as $thumbnail) {
                Storage::disk($this->disk)->delete($thumbnail);
            }
        }

        // Delete the main file
        if (Storage::disk($this->disk)->delete($this->path)) {
            $this->delete();
            return true;
        }

        return false;
    }

    public function generateThumbnails(): void
    {
        if (!$this->is_image) {
            return;
        }

        $thumbnails = [];
        $sizes = config('media-manager.image_processing.thumbnail_sizes', [
            'small' => [150, 150],
            'medium' => [300, 300],
            'large' => [600, 600],
        ]);

        foreach ($sizes as $size => $dimensions) {
            $thumbnailPath = $this->generateThumbnailPath($size);

            // Here you would use Intervention Image or similar package
            // to create the thumbnail and save it to $thumbnailPath

            $thumbnails[$size] = $thumbnailPath;
        }

        $this->update(['thumbnails' => $thumbnails]);
    }

    private function generateThumbnailPath(string $size): string
    {
        $pathInfo = pathinfo($this->path);
        return $pathInfo['dirname'] . '/thumbnails/' . $pathInfo['filename'] . '_' . $size . '.' . $pathInfo['extension'];
    }

    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
        $this->update(['last_accessed_at' => now()]);
    }

    public function canAccess($user): bool
    {
        if ($this->is_public) {
            return true;
        }

        if (!$user) {
            return false;
        }

        // Owner can always access
        if ($this->owner_id == $user->id && $this->owner_type == get_class($user)) {
            return true;
        }

        // Check specific permissions
        return $this->hasPermission($user, 'read');
    }

    public function canModify($user): bool
    {
        if (!$user) {
            return false;
        }

        // Owner can always modify
        if ($this->owner_id == $user->id && $this->owner_type == get_class($user)) {
            return true;
        }

        // Check specific permissions
        return $this->hasPermission($user, 'write');
    }

    public function shareWithUser($user, array $permissions): void
    {
        foreach ($permissions as $permission) {
            $this->grantPermission($user, $permission);
        }
    }
}
