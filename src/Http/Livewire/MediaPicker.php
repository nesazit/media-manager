<?php

namespace Nesazit\MediaManager\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Nesazit\MediaManager\Models\Directory;
use Nesazit\MediaManager\Models\File;

class MediaPicker extends Component
{
    public $selectedFile = null;
    public $currentDirectory = null;
    public $selectedDisk = 'public';
    public $allowedTypes = [];
    public $multiple = false;
    public $selectedFiles = [];
    public $showModal = false;

    // Data
    public $directories = [];
    public $files = [];
    public $breadcrumbs = [];

    protected $listeners = [
        'openMediaPicker' => 'openPicker',
        'selectFile' => 'selectFile'
    ];

    public function mount($allowedTypes = [], $multiple = false, $selectedFile = null)
    {
        $this->allowedTypes = $allowedTypes;
        $this->multiple = $multiple;
        $this->selectedFile = $selectedFile;

        if ($this->multiple && $this->selectedFile) {
            $this->selectedFiles = is_array($this->selectedFile) ? $this->selectedFile : [$this->selectedFile];
        }
    }

    public function openPicker()
    {
        $this->showModal = true;
        $this->loadContent();
    }

    public function closePicker()
    {
        $this->showModal = false;
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

        // Filter files by allowed types
        $this->files = collect($content['files'])->filter(function ($file) {
            if (empty($this->allowedTypes)) {
                return true;
            }
            return in_array($file->file_type, $this->allowedTypes);
        });

        $this->breadcrumbs = $content['breadcrumbs'];
    }

    public function navigateToDirectory($directoryId)
    {
        $this->currentDirectory = $directoryId;
        $this->loadContent();
    }

    public function navigateUp()
    {
        if ($this->currentDirectory) {
            $directory = Directory::find($this->currentDirectory);
            $this->currentDirectory = $directory?->parent_id;
            $this->loadContent();
        }
    }

    public function selectFile($fileId)
    {
        $file = File::find($fileId);

        if (!$file || !$file->canAccess(Auth::user())) {
            return;
        }

        if ($this->multiple) {
            if (!in_array($fileId, $this->selectedFiles)) {
                $this->selectedFiles[] = $fileId;
            }
        } else {
            $this->selectedFile = $fileId;
            $this->confirmSelection();
        }
    }

    public function deselectFile($fileId)
    {
        $this->selectedFiles = array_filter($this->selectedFiles, fn($id) => $id != $fileId);
    }

    public function confirmSelection()
    {
        if ($this->multiple) {
            $this->emit('filesSelected', $this->selectedFiles);
        } else {
            $this->emit('fileSelected', $this->selectedFile);
        }

        $this->closePicker();
    }

    public function getSelectedFileDetails()
    {
        if ($this->multiple) {
            return File::whereIn('id', $this->selectedFiles)->get();
        } else {
            return $this->selectedFile ? File::find($this->selectedFile) : null;
        }
    }

    public function render()
    {
        return view('media-manager::livewire.media-picker');
    }
}
