<?php

namespace Nesazit\MediaManager\Filament\Fields;

use Filament\Forms\Components\Field;
use Nesazit\MediaManager\Models\File;

class MediaPicker extends Field
{
    protected string $view = 'media-manager::filament.media-picker';

    protected bool $multiple = false;
    protected array $allowedTypes = [];
    protected string $disk = 'public';

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;
        return $this;
    }

    public function allowedTypes(array $types): static
    {
        $this->allowedTypes = $types;
        return $this;
    }

    public function disk(string $disk): static
    {
        $this->disk = $disk;
        return $this;
    }

    public function acceptImages(): static
    {
        return $this->allowedTypes(['image']);
    }

    public function acceptDocuments(): static
    {
        return $this->allowedTypes(['document']);
    }

    public function acceptVideos(): static
    {
        return $this->allowedTypes(['video']);
    }

    public function getSelectedFileUrls(): array
    {
        $value = $this->getState();

        if (!$value) {
            return [];
        }

        $fileIds = is_array($value) ? $value : [$value];

        return File::whereIn('id', $fileIds)
            ->get()
            ->pluck('url')
            ->toArray();
    }

    public function getSelectedFiles()
    {
        $value = $this->getState();

        if (!$value) {
            return collect();
        }

        $fileIds = is_array($value) ? $value : [$value];

        return File::whereIn('id', $fileIds)->get();
    }

    public function getMultiple(): bool
    {
        return $this->multiple;
    }

    public function getAllowedTypes(): array
    {
        return $this->allowedTypes;
    }

    public function getDisk(): string
    {
        return $this->disk;
    }
}
