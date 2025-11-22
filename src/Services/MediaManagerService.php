<?php

namespace Nesazit\MediaManager\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Nesazit\MediaManager\Models\Directory;
use Nesazit\MediaManager\Models\File;

class MediaManagerService
{
    public function uploadFile(
        UploadedFile $file,
        ?Directory $directory = null,
        ?string $disk = null,
        $owner = null
    ): File {
        $disk = $disk ?? config('media-manager.default_disk');
        $storage = Storage::disk($disk);

        // Generate unique filename
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = $this->generateUniqueFilename($originalName, $extension, $directory, $disk);

        // Determine upload path
        $uploadPath = $directory ? $directory->full_path : '/';
        $filePath = $uploadPath . $filename;

        // Store the file
        $storedPath = $file->storeAs($uploadPath, $filename, $disk);

        // Get file metadata
        $metadata = $this->getFileMetadata($file, $storedPath, $disk);

        // Create database record
        $File = File::create([
            'name' => $filename,
            'original_name' => $originalName,
            'file_type' => $this->determineFileType($extension),
            'mime_type' => $file->getMimeType(),
            'extension' => $extension,
            'size' => $file->getSize(),
            'disk' => $disk,
            'path' => $storedPath,
            'directory_id' => $directory?->id,
            'owner_id' => $owner?->id,
            'owner_type' => $owner ? get_class($owner) : null,
            'metadata' => $metadata,
            'is_public' => $disk === 'public',
        ]);

        // Generate thumbnails for images
        if ($File->is_image) {
            $File->generateThumbnails();
        }

        return $File;
    }

    public function createDirectory(
        string $name,
        ?Directory $parent = null,
        ?string $disk = null,
        $owner = null
    ): Directory {
        $disk = $disk ?? config('media-manager.default_disk');

        // Sanitize directory name
        $name = $this->sanitizeDirectoryName($name);

        // Determine path
        $path = $parent ? $parent->full_path : '/';

        // Create directory record
        $directory = Directory::create([
            'name' => $name,
            'path' => $path,
            'disk' => $disk,
            'parent_id' => $parent?->id,
            'owner_id' => $owner?->id,
            'owner_type' => $owner ? get_class($owner) : null,
            'is_public' => $disk === 'public',
        ]);

        // Create physical directory
        $directory->createDirectory();

        return $directory;
    }

    public function moveFile(File $file, ?Directory $destination): bool
    {
        return $file->moveToDirectory($destination);
    }

    public function moveDirectory(Directory $directory, ?Directory $destination): bool
    {
        return $directory->moveToDirectory($destination);
    }

    public function deleteFile(File $file): bool
    {
        return $file->deleteFile();
    }

    public function deleteDirectory(Directory $directory): bool
    {
        return $directory->deleteDirectory();
    }

    public function getDirectoryContents(
        ?Directory $directory = null,
        ?string $disk = null,
        $user = null
    ): array {
        $query = Directory::query();

        if ($directory) {
            $query->where('parent_id', $directory->id);
        } else {
            $query->whereNull('parent_id');
        }

        if ($disk) {
            $query->where('disk', $disk);
        }

        $directories = $query->get()->filter(function ($dir) use ($user) {
            return !$user || $dir->canAccess($user);
        });

        $fileQuery = File::query();

        if ($directory) {
            $fileQuery->where('directory_id', $directory->id);
        } else {
            $fileQuery->whereNull('directory_id');
        }

        if ($disk) {
            $fileQuery->where('disk', $disk);
        }

        $files = $fileQuery->get()->filter(function ($file) use ($user) {
            return !$user || $file->canAccess($user);
        });

        return [
            'directories' => $directories,
            'files' => $files,
            'breadcrumbs' => $directory ? $directory->getBreadcrumbs() : []
        ];
    }

    public function searchFiles(
        string $query,
        ?string $fileType = null,
        ?string $disk = null,
        $user = null
    ): array {
        $filesQuery = File::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('original_name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });

        if ($fileType) {
            $filesQuery->where('file_type', $fileType);
        }

        if ($disk) {
            $filesQuery->where('disk', $disk);
        }

        $files = $filesQuery->get()->filter(function ($file) use ($user) {
            return !$user || $file->canAccess($user);
        });

        return $files->toArray();
    }

    public function getAvailableDisks(): array
    {
        return config('media-manager.disks', []);
    }

    public function validateFileUpload(UploadedFile $file): array
    {
        $errors = [];

        // Check file size
        $maxSize = config('media-manager.max_file_size', 10240) * 1024; // Convert KB to bytes
        if ($file->getSize() > $maxSize) {
            $errors[] = "فایل بیش از حد مجاز ({$maxSize} کیلوبایت) است";
        }

        // Check file type
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedTypes = collect(config('media-manager.allowed_file_types', []))
            ->flatten()
            ->toArray();

        if (!in_array($extension, $allowedTypes)) {
            $errors[] = "نوع فایل مجاز نیست";
        }

        return $errors;
    }

    private function generateUniqueFilename(
        string $originalName,
        string $extension,
        ?Directory $directory,
        string $disk
    ): string {
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $basename = Str::slug($basename);

        $filename = $basename . '.' . $extension;
        $counter = 1;

        while ($this->fileExists($filename, $directory, $disk)) {
            $filename = $basename . '_' . $counter . '.' . $extension;
            $counter++;
        }

        return $filename;
    }

    private function fileExists(string $filename, ?Directory $directory, string $disk): bool
    {
        return File::where('name', $filename)
            ->where('directory_id', $directory?->id)
            ->where('disk', $disk)
            ->exists();
    }

    private function sanitizeDirectoryName(string $name): string
    {
        return Str::slug($name);
    }

    private function determineFileType(string $extension): string
    {
        $extension = strtolower($extension);

        foreach (config('media-manager.allowed_file_types', []) as $type => $extensions) {
            if (in_array($extension, $extensions)) {
                return $type;
            }
        }

        return 'other';
    }

    private function getFileMetadata(UploadedFile $file, string $storedPath, string $disk): array
    {
        $metadata = [];

        // For images, get dimensions
        if (Str::startsWith($file->getMimeType(), 'image/')) {
            try {
                $imagePath = Storage::disk($disk)->path($storedPath);
                $imageSize = getimagesize($imagePath);

                if ($imageSize) {
                    $metadata['width'] = $imageSize[0];
                    $metadata['height'] = $imageSize[1];
                }
            } catch (\Exception $e) {
                // Ignore errors
            }
        }

        return $metadata;
    }
}
