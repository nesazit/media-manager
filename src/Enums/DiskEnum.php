<?php

namespace Nesazit\MediaManager\Enums;

enum DiskEnum: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case CLOUD = 'cloud';

    public function label(): string
    {
        return match ($this) {
            self::PUBLIC => 'عمومی',
            self::PRIVATE => 'خصوصی',
            self::CLOUD => 'کلاود',
        };
    }

    public function isPublic(): bool
    {
        return $this === self::PUBLIC;
    }

    public function driver(): string
    {
        return match ($this) {
            self::PUBLIC => 'public',
            self::PRIVATE => 'local',
            self::CLOUD => 's3',
        };
    }
}
