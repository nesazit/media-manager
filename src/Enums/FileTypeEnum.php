<?php

namespace Nesazit\MediaManager\Enums;

enum FileTypeEnum: string
{
    case IMAGE = 'image';
    case DOCUMENT = 'document';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case ARCHIVE = 'archive';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::IMAGE => 'تصویر',
            self::DOCUMENT => 'سند',
            self::VIDEO => 'ویدیو',
            self::AUDIO => 'صوت',
            self::ARCHIVE => 'آرشیو',
            self::OTHER => 'سایر',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::IMAGE => 'heroicon-o-photo',
            self::DOCUMENT => 'heroicon-o-document-text',
            self::VIDEO => 'heroicon-o-video-camera',
            self::AUDIO => 'heroicon-o-musical-note',
            self::ARCHIVE => 'heroicon-o-archive-box',
            self::OTHER => 'heroicon-o-document',
        };
    }

    public function extensions(): array
    {
        return match ($this) {
            self::IMAGE => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp'],
            self::DOCUMENT => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'],
            self::VIDEO => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'],
            self::AUDIO => ['mp3', 'wav', 'ogg', 'aac', 'flac', 'm4a'],
            self::ARCHIVE => ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'],
            self::OTHER => [],
        };
    }

    public static function fromExtension(string $extension): self
    {
        $extension = strtolower($extension);

        foreach (self::cases() as $type) {
            if (in_array($extension, $type->extensions())) {
                return $type;
            }
        }

        return self::OTHER;
    }
}
