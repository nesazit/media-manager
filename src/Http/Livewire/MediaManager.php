<?php

namespace Nesazit\MediaManager\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Nesazit\MediaManager\Models\Directory;
use Nesazit\MediaManager\Models\File;
use Nesazit\MediaManager\Services\MediaManagerService;

class MediaManager extends Component
{
    use WithFileUploads, WithPagination;

    // Properties
    public $currentDirectory = null;
    public $selectedDisk = 'public';
    public $viewMode = 'grid';
    public $searchQuery = '';
    public $selectedFileType = '';
    public $showUploadModal = false;
    public $showCreateFolderModal = false;
    public $showRenameModal = false;
    public $showMoveModal = false;
    public $showDeleteModal = false;

    // Upload properties
    public $uploadFiles = [];
    public $uploadProgress = 0;

    // Modal properties
    public $folderName = '';
    public $selectedItems = [];
    public $itemToRename = null;
    public $newName = '';
    public $moveDestination = null;

    // Data properties
    public $directories = [];
    public $files = [];
    public $breadcrumbs = [];
    public $availableDisks = [];

    protected $listeners = [
        'refreshContent' => 'loadContent',
        'fileUploaded' => 'loadContent',
        'itemSelected' => 'selectItem',
        'itemDeselected' => 'deselectItem'
    ];

    public function mount($disk = 'public', $directory = null)
    {
        $this->selectedDisk = $disk;
        $this->currentDirectory = $directory;
        $this->viewMode = config('media-manager.ui.default_view', 'grid');
        $this->availableDisks = app('media-manager')->getAvailableDisks();
        $this->loadContent();
    }

    public function loadContent()
    {
        $directory = $this->currentDirectory ? Directory::find($this->currentDirectory) : null;

        $content = app('media-manager')->getDirectoryContents(
            $directory,
            $this->selectedDisk,
            Auth::user()
        );

        $this->directories = $content['directories'];
        $this->files = $content['files'];
        $this->breadcrumbs = $content['breadcrumbs'];
    }

    public function changeDisk($disk)
    {
        $this->selectedDisk = $disk;
        $this->currentDirectory = null;
        $this->resetPage();
        $this->loadContent();
    }

    public function changeViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function navigateToDirectory($directoryId)
    {
        $this->currentDirectory = $directoryId;
        $this->selectedItems = [];
        $this->resetPage();
        $this->loadContent();
    }

    public function navigateUp()
    {
        if ($this->currentDirectory) {
            $directory = Directory::find($this->currentDirectory);
            $this->currentDirectory = $directory?->parent_id;
            $this->selectedItems = [];
            $this->resetPage();
            $this->loadContent();
        }
    }

    public function selectItem($type, $id)
    {
        $key = $type . '_' . $id;
        if (!in_array($key, $this->selectedItems)) {
            $this->selectedItems[] = $key;
        }
    }

    public function deselectItem($type, $id)
    {
        $key = $type . '_' . $id;
        $this->selectedItems = array_filter($this->selectedItems, fn($item) => $item !== $key);
    }

    public function selectAll()
    {
        $this->selectedItems = [];

        foreach ($this->directories as $dir) {
            $this->selectedItems[] = 'directory_' . $dir->id;
        }

        foreach ($this->files as $file) {
            $this->selectedItems[] = 'file_' . $file->id;
        }
    }

    public function deselectAll()
    {
        $this->selectedItems = [];
    }

    public function showCreateFolder()
    {
        $this->folderName = '';
        $this->showCreateFolderModal = true;
    }

    public function createFolder()
    {
        $this->validate([
            'folderName' => 'required|string|max:255'
        ], [
            'folderName.required' => 'نام پوشه الزامی است'
        ]);

        try {
            $parentDirectory = $this->currentDirectory ? Directory::find($this->currentDirectory) : null;

            app('media-manager')->createDirectory(
                $this->folderName,
                $parentDirectory,
                $this->selectedDisk,
                auth()->user()
            );

            $this->showCreateFolderModal = false;
            $this->folderName = '';
            $this->loadContent();

            session()->flash('success', 'پوشه با موفقیت ایجاد شد');
        } catch (\Exception $e) {
            session()->flash('error', 'خطا در ایجاد پوشه: ' . $e->getMessage());
        }
    }

    public function showUpload()
    {
        $this->uploadFiles = [];
        $this->showUploadModal = true;
    }

    public function handleUploadFiles()
    {
        $this->validate([
            'uploadFiles.*' => 'required|file|max:' . config('media-manager.max_file_size', 10240)
        ]);

        try {
            $parentDirectory = $this->currentDirectory ? Directory::find($this->currentDirectory) : null;

            foreach ($this->uploadFiles as $file) {
                $errors = app('media-manager')->validateFileUpload($file);
                if (!empty($errors)) {
                    session()->flash('error', implode(', ', $errors));
                    return;
                }

                app('media-manager')->uploadFile(
                    $file,
                    $parentDirectory,
                    $this->selectedDisk,
                    auth()->user()
                );
            }

            $this->showUploadModal = false;
            $this->uploadFiles = [];
            $this->loadContent();

            session()->flash('success', 'فایل‌ها با موفقیت آپلود شدند');
        } catch (\Exception $e) {
            session()->flash('error', 'خطا در آپلود فایل: ' . $e->getMessage());
        }
    }

    public function showRename($type, $id)
    {
        $this->itemToRename = $type . '_' . $id;

        if ($type === 'directory') {
            $item = Directory::find($id);
        } else {
            $item = File::find($id);
        }

        $this->newName = $item?->name ?? '';
        $this->showRenameModal = true;
    }

    public function renameItem()
    {
        $this->validate([
            'newName' => 'required|string|max:255'
        ]);

        try {
            [$type, $id] = explode('_', $this->itemToRename, 2);

            if ($type === 'directory') {
                $item = Directory::find($id);
                if ($item && $item->canModify(auth()->user())) {
                    $item->update(['name' => $this->newName]);
                }
            } else {
                $item = File::find($id);
                if ($item && $item->canModify(auth()->user())) {
                    $pathInfo = pathinfo($this->newName);
                    $newFilename = $pathInfo['filename'] . '.' . $item->extension;
                    $item->update(['name' => $newFilename]);
                }
            }

            $this->showRenameModal = false;
            $this->loadContent();

            session()->flash('success', 'نام با موفقیت تغییر یافت');
        } catch (\Exception $e) {
            session()->flash('error', 'خطا در تغییر نام: ' . $e->getMessage());
        }
    }

    public function showDelete()
    {
        if (empty($this->selectedItems)) {
            session()->flash('error', 'هیچ آیتمی انتخاب نشده است');
            return;
        }

        $this->showDeleteModal = true;
    }

    public function deleteSelectedItems()
    {
        try {
            foreach ($this->selectedItems as $item) {
                [$type, $id] = explode('_', $item, 2);

                if ($type === 'directory') {
                    $directory = Directory::find($id);
                    if ($directory && $directory->canModify(auth()->user())) {
                        app('media-manager')->deleteDirectory($directory);
                    }
                } else {
                    $file = File::find($id);
                    if ($file && $file->canModify(auth()->user())) {
                        app('media-manager')->deleteFile($file);
                    }
                }
            }

            $this->showDeleteModal = false;
            $this->selectedItems = [];
            $this->loadContent();

            session()->flash('success', 'آیتم‌ها با موفقیت حذف شدند');
        } catch (\Exception $e) {
            session()->flash('error', 'خطا در حذف: ' . $e->getMessage());
        }
    }

    public function downloadFile($fileId)
    {
        $file = File::find($fileId);

        if (!$file || !$file->canAccess(auth()->user())) {
            session()->flash('error', 'دسترسی به فایل مجاز نیست');
            return;
        }

        $file->incrementDownloadCount();

        return response()->download(
            storage_path("app/{$file->disk}/{$file->path}"),
            $file->original_name
        );
    }

    public function search()
    {
        if (empty($this->searchQuery)) {
            $this->loadContent();
            return;
        }

        $results = app('media-manager')->searchFiles(
            $this->searchQuery,
            $this->selectedFileType ?: null,
            $this->selectedDisk,
            auth()->user()
        );

        $this->files = collect($results);
        $this->directories = collect([]);
    }

    public function clearSearch()
    {
        $this->searchQuery = '';
        $this->selectedFileType = '';
        $this->loadContent();
    }

    public function render()
    {
        return view('media-manager::livewire.media-manager');
    }
}