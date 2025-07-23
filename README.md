# Media Manager Package

یک پکیج قدرتمند Laravel برای مدیریت فایل‌ها و رسانه‌ها با پشتیبانی از Livewire و Filament

## ویژگی‌ها

- ✅ مدیریت کامل فایل‌ها و پوشه‌ها
- ✅ سیستم کنترل دسترسی (ACL) پیشرفته
- ✅ پشتیبانی از چندین دیسک ذخیره‌سازی
- ✅ کامپوننت Livewire کامل
- ✅ فیلد سفارشی Filament
- ✅ آپلود با drag & drop
- ✅ تولید thumbnail برای تصاویر
- ✅ جستجوی پیشرفته
- ✅ رابط کاربری زیبا و ریسپانسیو
- ✅ پشتیبانی کامل از RTL

## نصب

### 1. نصب پکیج

```bash
composer require nesazit/media-manager
```

### 2. انتشار فایل‌ها

```bash
# انتشار config
php artisan vendor:publish --tag=media-manager-config

# انتشار migrations
php artisan vendor:publish --tag=media-manager-migrations

# انتشار views (اختیاری)
php artisan vendor:publish --tag=media-manager-views

# انتشار assets (اختیاری)
php artisan vendor:publish --tag=media-manager-assets
```

### 3. اجرای Migration

```bash
php artisan migrate
```

### 4. تنظیم دیسک‌ها

در فایل `config/filesystems.php` دیسک‌های مورد نیاز را اضافه کنید:

```php
'disks' => [
    // ... سایر دیسک‌ها

    'private' => [
        'driver' => 'local',
        'root' => storage_path('app/private'),
        'url' => env('APP_URL').'/storage/private',
        'visibility' => 'private',
    ],
],
```

## استفاده

### 1. در Livewire

```php
// در view خود
@livewire('media-manager')

// یا با پارامترها
@livewire('media-manager', [
    'disk' => 'public',
    'directory' => null
])
```

### 2. در Filament Forms

```php
use Nesazit\MediaManager\Filament\Fields\MediaPicker;

public static function form(Form $form): Form
{
    return $form->schema([
        MediaPicker::make('featured_image')
            ->label('تصویر شاخص')
            ->acceptImages()
            ->disk('public'),

        MediaPicker::make('gallery_images')
            ->label('گالری تصاویر')
            ->multiple()
            ->acceptImages(),

        MediaPicker::make('documents')
            ->label('فایل‌های پیوست')
            ->multiple()
            ->acceptDocuments()
            ->disk('private'),
    ]);
}
```

### 3. در Controller

```php
use Nesazit\MediaManager\Services\MediaManagerService;

class DocumentController extends Controller
{
    public function upload(Request $request, MediaManagerService $mediaService)
    {
        $directory = MediaDirectory::where('name', 'documents')->first();

        $file = $mediaService->uploadFile(
            $request->file('document'),
            $directory,
            'private',
            auth()->user()
        );

        return response()->json($file);
    }
}
```

### 4. کار با مدل‌ها

```php
use Nesazit\MediaManager\Models\File;
use Nesazit\MediaManager\Models\Directory;

// دریافت فایل‌ها
$files = File::where('file_type', 'image')
    ->where('is_public', true)
    ->get();

// ایجاد پوشه
$directory = Directory::create([
    'name' => 'my-folder',
    'path' => '/',
    'disk' => 'public',
    'owner_id' => auth()->id(),
    'owner_type' => get_class(auth()->user()),
]);

// بررسی دسترسی
if ($file->canAccess(auth()->user())) {
    return $file->url;
}
```

## تنظیمات

### فایل config/media-manager.php

```php
return [
    // دیسک پیش‌فرض
    'default_disk' => 'public',

    // دیسک‌های قابل استفاده
    'disks' => [
        'public' => ['label' => 'عمومی'],
        'private' => ['label' => 'خصوصی'],
        'cloud' => ['label' => 'کلاود'],
    ],

    // انواع فایل مجاز
    'allowed_file_types' => [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
        'video' => ['mp4', 'avi', 'mov'],
        'audio' => ['mp3', 'wav', 'ogg'],
    ],

    // حداکثر سایز فایل (کیلوبایت)
    'max_file_size' => 10240, // 10MB

    // تنظیمات ACL
    'acl' => [
        'enabled' => true,
        'admin_full_access' => true,
        'user_only_own_files' => true,
    ],

    // تنظیمات پردازش تصویر
    'image_processing' => [
        'create_thumbnails' => true,
        'thumbnail_sizes' => [
            'small' => [150, 150],
            'medium' => [300, 300],
            'large' => [600, 600],
        ],
    ],
];
```

## امنیت و کنترل دسترسی

### 1. تنظیم مجوزها برای فایل

```php
$file = MediaFile::find(1);

// اعطای دسترسی به کاربر خاص
$file->shareWithUser($user, ['read', 'write']);

// اعطای دسترسی خواندن فقط
$file->grantPermission($user, 'read');

// لغو دسترسی
$file->revokePermission($user, 'write');
```

### 2. بررسی دسترسی

```php
// بررسی دسترسی خواندن
if ($file->canAccess($user)) {
    // نمایش فایل
}

// بررسی دسترسی تغییر
if ($file->canModify($user)) {
    // اجازه ویرایش/حذف
}
```

### 3. فایل‌های خصوصی

برای فایل‌های خصوصی، دسترسی از طریق route محافظت شده صورت می‌گیرد:

```php
// URL فایل خصوصی
$privateFile->url; // /media-manager/file/123/filename.pdf
```

## API Routes

```php
// آپلود فایل
POST /media-manager/upload

// ایجاد پوشه
POST /media-manager/create-directory

// تغییر نام
PATCH /media-manager/rename/{type}/{id}

// حذف
DELETE /media-manager/delete/{type}/{id}

// انتقال
PATCH /media-manager/move/{type}/{id}

// مرور پوشه‌ها
GET /media-manager/browse

// جستجو
GET /media-manager/search
```

## نمونه‌های پیشرفته

### 1. کامپوننت Livewire سفارشی

```php
class MyMediaManager extends Component
{
    use WithFileUploads;

    public $selectedFiles = [];

    public function mount()
    {
        // تنظیمات اولیه
    }

    public function handleFileSelection($fileIds)
    {
        $this->selectedFiles = $fileIds;
        $this->emit('filesSelected', $fileIds);
    }

    public function render()
    {
        return view('livewire.my-media-manager');
    }
}
```

### 2. استفاده در Model

```php
class Post extends Model
{
    public function featuredImage()
    {
        return $this->belongsTo(File::class, 'featured_image_id');
    }

    public function galleryImages()
    {
        return $this->belongsToMany(File::class, 'post_media');
    }

    public function getFeaturedImageUrlAttribute()
    {
        return $this->featuredImage?->url;
    }
}
```

## مشکلات رایج و راه‌حل

### 1. خطای دسترسی فایل

```bash
# اعطای دسترسی مناسب
chmod -R 755 storage/
chown -R www-data:www-data storage/
```

### 2. مشکل در آپلود فایل‌های بزرگ

```php
// در php.ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
```

### 3. مشکل thumbnail

پکیج Intervention Image را نصب کنید:

```bash
composer require intervention/image
```

## مشارکت

برای مشارکت در توسعه این پکیج:

1. Repository را Fork کنید
2. برنچ جدید ایجاد کنید
3. تغییرات خود را Commit کنید
4. Pull Request ایجاد کنید

## لایسنس

این پکیج تحت لایسنس MIT منتشر شده است.

## پشتیبانی

برای پشتیبانی و گزارش باگ:

- ایمیل: info@nesazit.com
- GitHub Issues: [لینک repository]

---

**نکته:** این پکیج برای Laravel 10+ و PHP 8.2+ طراحی شده است.
