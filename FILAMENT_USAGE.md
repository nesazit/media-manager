# استفاده در Filament Panel

## 1. اضافه کردن صفحه مدیریت فایل‌ها

```php
// app/Filament/Pages/MediaManagerPage.php
<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class MediaManagerPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static string $view = 'filament.pages.media-manager-page';
    protected static ?string $navigationLabel = 'مدیریت فایل‌ها';
    protected static ?string $title = 'مدیریت فایل‌ها';
    protected static ?string $navigationGroup = 'محتوا';
}
```

```blade
{{-- resources/views/filament/pages/media-manager-page.blade.php --}}
<x-filament-panels::page>
    <div class="bg-white rounded-lg border">
        @livewire('media-manager')
    </div>
</x-filament-panels::page>
```

## 2. استفاده MediaPicker در Resource

```php
use Nesazit\MediaManager\Filament\Fields\MediaPicker;

public static function form(Form $form): Form
{
    return $form->schema([
        // تصویر واحد
        MediaPicker::make('featured_image_id')
            ->label('تصویر شاخص')
            ->acceptImages()
            ->disk('public'),

        // چندین تصویر
        MediaPicker::make('gallery_images')
            ->label('گالری تصاویر')
            ->multiple()
            ->acceptImages(),

        // فقط اسناد
        MediaPicker::make('documents')
            ->label('فایل‌های پیوست')
            ->multiple()
            ->acceptDocuments()
            ->disk('private'),

        // فقط ویدیو
        MediaPicker::make('video_file')
            ->label('فایل ویدیو')
            ->acceptVideos(),
    ]);
}
```

## 3. نمایش در Table

```php
Tables\Columns\ImageColumn::make('featuredImage.url')
    ->label('تصویر شاخص')
    ->size(60)
    ->square(),

Tables\Columns\TextColumn::make('gallery_images')
    ->label('تعداد تصاویر')
    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) : 0),
```

## 4. کار با روابط در Model

```php
class Post extends Model
{
    protected $casts = [
        'gallery_images' => 'array'
    ];

    public function featuredImage()
    {
        return $this->belongsTo(MediaFile::class, 'featured_image_id');
    }

    public function galleryImages()
    {
        if (!$this->gallery_images) {
            return collect();
        }
        return MediaFile::whereIn('id', $this->gallery_images)->get();
    }
}
```

## 5. گزینه‌های پیشرفته MediaPicker

```php
MediaPicker::make('files')
    ->label('فایل‌ها')
    ->multiple(true)                    // انتخاب چندگانه
    ->allowedTypes(['image', 'document']) // انواع مجاز
    ->disk('public')                    // دیسک مورد استفاده
    ->acceptImages()                    // فقط تصاویر
    ->acceptDocuments()                 // فقط اسناد
    ->acceptVideos()                    // فقط ویدیو
```

## 6. دسترسی به فایل‌های انتخابی

```php
// در Action یا مکان دیگر
$selectedFiles = $field->getSelectedFiles();
$selectedUrls = $field->getSelectedFileUrls();

// در Model
$post = Post::find(1);
$featuredImageUrl = $post->featuredImage?->url;
$galleryUrls = $post->galleryImages()->pluck('url')->toArray();
```

## 7. Customization رابط کاربری

برای سفارشی کردن ظاهر MediaPicker:

```php
// انتشار view ها
php artisan vendor:publish --tag=media-manager-views

// ویرایش فایل:
// resources/views/vendor/media-manager/filament/media-picker.blade.php
```
