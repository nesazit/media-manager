<?php

namespace Nesazit\MediaManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Nesazit\MediaManager\Models\Directory;
use Nesazit\MediaManager\Models\File;
use Nesazit\MediaManager\Services\MediaManagerService;

class MediaController
{
    protected MediaManagerService $mediaService;

    public function __construct(MediaManagerService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function index()
    {
        return view('media-manager::index');
    }

    public function serveFile(Request $request, int $fileId, string $name)
    {
        $file = File::find($fileId);

        if (!$file || !$file->canAccess(Auth::user())) {
            abort(404);
        }

        $storage = Storage::disk($file->disk);

        if (!$storage->exists($file->path)) {
            abort(404);
        }

        $file->incrementDownloadCount();

        return response()->file(
            $storage->path($file->path),
            [
                'Content-Type' => $file->mime_type,
                'Content-Disposition' => 'inline; filename="' . $file->original_name . '"'
            ]
        );
    }

    public function serveThumbnail(Request $request, int $fileId, string $size)
    {
        $file = File::find($fileId);

        if (!$file || !$file->canAccess(Auth::user()) || !$file->is_image) {
            abort(404);
        }

        $thumbnailPath = $file->thumbnails[$size] ?? null;

        if (!$thumbnailPath) {
            // Fallback to original image
            $thumbnailPath = $file->path;
        }

        $storage = Storage::disk($file->disk);

        if (!$storage->exists($thumbnailPath)) {
            abort(404);
        }

        return response()->file(
            $storage->path($thumbnailPath),
            [
                'Content-Type' => $file->mime_type,
                'Cache-Control' => 'public, max-age=86400'
            ]
        );
    }

    public function upload(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:' . config('media-manager.max_file_size', 10240),
            'directory_id' => 'nullable|exists:media_directories,id',
            'disk' => 'nullable|string'
        ]);

        $directory = $request->directory_id ? MediaDirectory::find($request->directory_id) : null;
        $disk = $request->disk ?? config('media-manager.default_disk');
        $uploadedFiles = [];

        foreach ($request->file('files') as $file) {
            $errors = $this->mediaService->validateFileUpload($file);

            if (!empty($errors)) {
                return response()->json(['errors' => $errors], 422);
            }

            $uploadedFile = $this->mediaService->uploadFile(
                $file,
                $directory,
                $disk,
                Auth::user()
            );

            $uploadedFiles[] = $uploadedFile;
        }

        return response()->json([
            'message' => 'فایل‌ها با موفقیت آپلود شدند',
            'files' => $uploadedFiles
        ]);
    }

    public function createDirectory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:media_directories,id',
            'disk' => 'nullable|string'
        ]);

        $parent = $request->parent_id ? MediaDirectory::find($request->parent_id) : null;
        $disk = $request->disk ?? config('media-manager.default_disk');

        $directory = $this->mediaService->createDirectory(
            $request->name,
            $parent,
            $disk,
            Auth::user()
        );

        return response()->json([
            'message' => 'پوشه با موفقیت ایجاد شد',
            'directory' => $directory
        ]);
    }

    public function rename(Request $request, string $type, int $id)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        if ($type === 'directory') {
            $item = MediaDirectory::find($id);
        } else {
            $item = MediaFile::find($id);
        }

        if (!$item || !$item->canModify(Auth::user())) {
            abort(403);
        }

        $item->update(['name' => $request->name]);

        return response()->json([
            'message' => 'نام با موفقیت تغییر یافت',
            'item' => $item
        ]);
    }

    public function delete(Request $request, string $type, int $id)
    {
        if ($type === 'directory') {
            $item = MediaDirectory::find($id);
            if (!$item || !$item->canModify(Auth::user())) {
                abort(403);
            }
            $this->mediaService->deleteDirectory($item);
        } else {
            $item = MediaFile::find($id);
            if (!$item || !$item->canModify(Auth::user())) {
                abort(403);
            }
            $this->mediaService->deleteFile($item);
        }

        return response()->json([
            'message' => 'آیتم با موفقیت حذف شد'
        ]);
    }

    public function move(Request $request, string $type, int $id)
    {
        $request->validate([
            'destination_id' => 'nullable|exists:media_directories,id'
        ]);

        $destination = $request->destination_id ? MediaDirectory::find($request->destination_id) : null;

        if ($type === 'directory') {
            $item = MediaDirectory::find($id);
            if (!$item || !$item->canModify(Auth::user())) {
                abort(403);
            }
            $this->mediaService->moveDirectory($item, $destination);
        } else {
            $item = MediaFile::find($id);
            if (!$item || !$item->canModify(Auth::user())) {
                abort(403);
            }
            $this->mediaService->moveFile($item, $destination);
        }

        return response()->json([
            'message' => 'آیتم با موفقیت منتقل شد'
        ]);
    }

    public function browse(Request $request)
    {
        $request->validate([
            'directory_id' => 'nullable|exists:media_directories,id',
            'disk' => 'nullable|string'
        ]);

        $directory = $request->directory_id ? MediaDirectory::find($request->directory_id) : null;
        $disk = $request->disk ?? config('media-manager.default_disk');

        $content = $this->mediaService->getDirectoryContents(
            $directory,
            $disk,
            Auth::user()
        );

        return response()->json($content);
    }

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1',
            'file_type' => 'nullable|string',
            'disk' => 'nullable|string'
        ]);

        $results = $this->mediaService->searchFiles(
            $request->query,
            $request->file_type,
            $request->disk ?? config('media-manager.default_disk'),
            Auth::user()
        );

        return response()->json([
            'files' => $results
        ]);
    }
}