<?php

namespace Nesazit\MediaManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use Nesazit\MediaManager\Traits\HasMediaPermissions;

class Directory extends Model
{
    use HasMediaPermissions;

    protected $table = 'directories';

    protected $fillable = [
        'name',
        'path',
        'disk',
        'parent_id',
        'owner_id',
        'owner_type',
        'permissions',
        'is_public',
        'description'
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_public' => 'boolean',
    ];

    protected $appends = [
        'full_path',
        'size',
        'files_count',
        'subdirectories_count'
    ];

    // Relations
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'directory_id');
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    // Accessors
    public function getFullPathAttribute(): string
    {
        return $this->path . $this->name . '/';
    }

    public function getSizeAttribute(): int
    {
        return $this->files()->sum('size') +
            $this->children()->get()->sum('size');
    }

    public function getFilesCountAttribute(): int
    {
        return $this->files()->count();
    }

    public function getSubdirectoriesCountAttribute(): int
    {
        return $this->children()->count();
    }

    // Methods
    public function createDirectory(): bool
    {
        $storage = Storage::disk($this->disk);
        return $storage->makeDirectory($this->full_path);
    }

    public function deleteDirectory(): bool
    {
        // Delete all files in this directory
        foreach ($this->files as $file) {
            $file->deleteFile();
        }

        // Delete all subdirectories recursively
        foreach ($this->children as $subdirectory) {
            $subdirectory->deleteDirectory();
        }

        // Delete the physical directory
        Storage::disk($this->disk)->deleteDirectory($this->full_path);

        // Delete the database record
        $this->delete();

        return true;
    }

    public function moveToDirectory(?Directory $destination): bool
    {
        $oldPath = $this->full_path;
        $newParentPath = $destination ? $destination->full_path : '/';
        $newPath = $newParentPath . $this->name . '/';

        if (Storage::disk($this->disk)->move($oldPath, $newPath)) {
            $this->update([
                'path' => $newParentPath,
                'parent_id' => $destination?->id
            ]);
            return true;
        }

        return false;
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        $current = $this;

        while ($current) {
            array_unshift($breadcrumbs, [
                'id' => $current->id,
                'name' => $current->name,
                'path' => $current->full_path
            ]);
            $current = $current->parent;
        }

        return $breadcrumbs;
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
}
