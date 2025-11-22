<div class="media-manager bg-white rounded-lg shadow-lg" x-data="{ selectedItems: @entangle('selectedItems') }"
x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('media-manager', package: 'nesazit/media-manager'))]">
    {{-- Header --}}
    <div class="border-b border-gray-200 p-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
            {{-- Disk Selector --}}
            <div class="flex items-center gap-2">
                <label for="diskSelect" class="text-sm font-medium text-gray-700">دیسک:</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select id="diskSelect" wire:model="selectedDisk" wire:change="changeDisk($event.target.value)">
                        @foreach ($availableDisks as $disk => $config)
                            <option value="{{ $disk }}">{{ $config['label'] }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2">
                <x-filament::modal width="xl">
                    <x-slot name="trigger">
                        <x-filament::button color="orange" icon="heroicon-o-folder-plus">
                            پوشه جدید
                        </x-filament::button>
                    </x-slot>

                    <x-slot name="heading">
                        ایجاد پوشه جدید
                    </x-slot>

                    {{-- content --}}
                    <div>
                        <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3 mb-2" for="folderName">
                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                نام پوشه
                                <sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                            </span>
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="text"
                                id="folderName"
                                wire:model="folderName"
                            />
                        </x-filament::input.wrapper>
                    </div>

                    <x-slot name="footer">
                        <x-filament::button wire:click="createFolder">
                            ثبت
                        </x-filament::button>
                        <x-filament::button x-on:click="close" color="gray">
                            لغو
                        </x-filament::button>
                    </x-slot>
                </x-filament::modal>

                <x-filament::modal width="xl">
                    <x-slot name="trigger">
                        <x-filament::button color="blue" icon="heroicon-o-cloud-arrow-up">
                            آپلود فایل
                        </x-filament::button>
                    </x-slot>

                    <x-slot name="heading">
                        آپلود فایل جدید
                    </x-slot>

                    {{-- content --}}
                    <div>
                        <form wire:submit.prevent="handleUploadFiles">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">انتخاب فایل‌ها</label>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center"
                                    x-data="{
                                        isDragOver: false,
                                        handleDrop(e) {
                                            this.isDragOver = false;
                                            $wire.set('uploadFiles', Array.from(e.dataTransfer.files));
                                        }
                                    }" @dragover.prevent="isDragOver = true" @dragleave="isDragOver = false"
                                    @drop.prevent="handleDrop" :class="{ 'border-blue-500 bg-blue-50': isDragOver }">

                                    <input type="file" wire:model="uploadFiles" multiple class="hidden" id="fileInput">

                                    <div class="mb-2">
                                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400"></i>
                                    </div>

                                    <p class="text-gray-600 mb-2">فایل‌ها را اینجا بکشید یا کلیک کنید</p>

                                    <label for="fileInput"
                                        class="bg-blue-600 text-white px-4 py-2 rounded-md cursor-pointer hover:bg-blue-700">
                                        انتخاب فایل‌ها
                                    </label>

                                    <p class="text-xs text-gray-500 mt-2">
                                        حداکثر اندازه: {{ config('media-manager.max_file_size') }} کیلوبایت
                                    </p>
                                </div>

                                @if ($uploadFiles)
                                    <div class="mt-4">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">فایل‌های انتخاب شده:</h4>
                                        <div class="space-y-2">
                                            @foreach ($uploadFiles as $index => $file)
                                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                                    <span class="text-sm text-gray-700">{{ $file->getClientOriginalName() }}</span>
                                                    <span
                                                        class="text-xs text-gray-500">{{ number_format($file->getSize() / 1024, 2) }}
                                                        KB</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @error('uploadFiles.*')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex justify-start gap-2">
                                <x-filament::button wire:click="handleUploadFiles">
                                    ثبت
                                </x-filament::button>
                                <x-filament::button x-on:click="close" color="gray">
                                    لغو
                                </x-filament::button>
                            </div>
                        </form>

                        {{-- Progress Bar --}}
                        @if ($uploadProgress > 0)
                            <div class="mt-4">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $uploadProgress }}%"></div>
                                </div>
                                <p class="text-sm text-gray-600 mt-1 text-center">{{ $uploadProgress }}%</p>
                            </div>
                        @endif
                    </div>
                </x-filament::modal>

                @if (count($selectedItems) > 0)

                <x-filament::modal width="xl">
                        <x-slot name="trigger">
                            <x-filament::button color="danger" icon="heroicon-o-trash">
                                حذف
                            </x-filament::button>
                        </x-slot>

                        <x-slot name="heading">
                            حذف فایل
                        </x-slot>
                        <x-slot name="description">
                            آیا از حذف آیتم مورد نظر مطمئن هستید؟
                        </x-slot>

                        <x-slot name="footer">
                            <div class="flex">
                                <x-filament::button wire:click="deleteSelectedItems">
                                    حذف
                                </x-filament::button>
                                <x-filament::button x-on:click="close" color="gray">
                                    لغو
                                </x-filament::button>
                            </div>
                        </x-slot>
                    </x-filament::modal>
                @endif

                {{-- View Mode Toggle --}}
                <div class="flex border rounded-md">
                    <button wire:click="changeViewMode('grid')"
                        class="rounded-md px-3 py-1 text-sm {{ $viewMode === 'grid' ? 'bg-gray-200' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-grid3x3-icon lucide-grid-3x3"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/><path d="M9 3v18"/><path d="M15 3v18"/></svg>
                    </button>
                    <button wire:click="changeViewMode('list')"
                        class="rounded-md px-3 py-1 text-sm {{ $viewMode === 'list' ? 'bg-gray-200' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-list-icon lucide-list"><path d="M3 12h.01"/><path d="M3 18h.01"/><path d="M3 6h.01"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M8 6h13"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Search Bar --}}
        <div class="mt-4 flex gap-2">
            <x-filament::input.wrapper inline-prefix prefix-icon="heroicon-m-magnifying-glass">
                <x-filament::input
                    type="text"
                    placeholder="جستجوی فایل"
                    wire:model.debounce.300ms="searchQuery" wire:keydown.enter="search"
                />
            </x-filament::input.wrapper>
            <x-filament::input.wrapper>
                <x-filament::input.select id="fileTypeSelect" wire:model="selectedFileType" wire:change="search">
                    <option value="">همه فایل‌ها</option>
                    <option value="image">تصاویر</option>
                    <option value="document">اسناد</option>
                    <option value="video">ویدیو</option>
                    <option value="audio">صوتی</option>
                </x-filament::input.select>
            </x-filament::input.wrapper>

            @if ($searchQuery)
                <x-filament::button wire:click="clearSearch" color="violet">
                    پاک کردن
                </x-filament::button>
            @endif

        </div>
    </div>

    {{-- Breadcrumbs --}}
    @if (count($breadcrumbs) > 0)
        <div class="p-4 border-b border-gray-100">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center justify-center space-x-reverse space-x-2">
                    <li class="mx-2">

                        @if ($currentDirectory)
                            <x-filament::button icon="heroicon-o-arrow-uturn-left" wire:click="navigateUp" color="sky">
                            </x-filament::button>
                        @endif

                        <x-filament::button icon="heroicon-o-home" wire:click="navigateToDirectory(null)" color="blue">
                        </x-filament::button>
                    </li>
                    @foreach ($breadcrumbs as $crumb)
                        <li class="flex items-center justify-center">
                            <button wire:click="navigateToDirectory({{ $crumb['id'] }})"
                                class="text-blue-600 hover:text-blue-800">
                                {{ $crumb['name'] }}
                            </button>

                            @if (!$loop->last)
                                <svg class="w-4 h-4 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M0 0h24v24H0V0z" fill="none"></path><path d="M17.51 3.87L15.73 2.1 5.84 12l9.9 9.9 1.77-1.77L9.38 12l8.13-8.13z"></path>
                                </svg>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        </div>
    @endif

    {{-- Content Area --}}
    <div class="p-4 min-h-96">
        @if ($viewMode === 'grid')
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4" x-data="{ openRenameModal: false, folderName: '', currentItem: null }">
                {{-- Directories --}}
                @foreach ($directories as $directory)
                    <div class="relative group" x-data="{ selected: selectedItems.includes('directory_{{ $directory->id }}') }">
                        <div class="absolute top-0 group-hover:opacity-100 group-hover:visible invisible opacity-0 bg-gradient-to-b from-gray-500/20 to-gray-500/10 h-1/5 w-full rounded-t-lg duration-200 transition-all p-1">
                            <input type="checkbox" x-model="selected" id="directory-id-{{ $directory->id }}"
                                @change="selected ? $wire.selectItem('directory', {{ $directory->id }}) : $wire.deselectItem('directory', {{ $directory->id }})"
                                class="absolute top-2 left-2">
                        </div>

                        <div class="border border-gray-200 rounded-lg p-4 text-center hover:bg-gray-50 cursor-pointer " @click="$wire.navigateToDirectory({{ $directory->id }})"
                            @contextmenu.prevent="$wire.selectItem('directory', {{ $directory->id }})">

                            <div class="flex justify-center">
                                <svg class="w-16 h-16 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"></path>
                                </svg>
                            </div>
                            <i class="fas fa-folder text-3xl text-yellow-500 mb-2"></i>
                            <p class="text-sm font-medium text-gray-700 truncate">{{ $directory->name }}</p>
                            <p class="text-xs text-gray-500">{{ $directory->files_count }} فایل</p>
                        </div>
                    </div>
                @endforeach

                {{-- Files --}}
                @foreach ($files as $file)
                    <div class="relative group" x-data="{ selected: selectedItems.includes('file_{{ $file->id }}') }">
                        <div class="absolute top-0 group-hover:opacity-100 group-hover:visible invisible opacity-0 bg-gradient-to-b from-gray-500/20 to-gray-500/10 h-1/5 w-full rounded-t-lg duration-200 transition-all p-1">
                            <input type="checkbox" x-model="selected" id="file-id-{{ $file->id }}"
                                @change="selected ? $wire.selectItem('file', {{ $file->id }}) : $wire.deselectItem('file', {{ $file->id }})"
                                class="top-2 left-2 absolute">

                            <x-filament::dropdown placement="bottom-end" class="w-16">
                                <x-slot name="trigger">
                                    <button class="absolute top-2 right-2 p-1 rounded hover:bg-gray-200 text-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-filament::dropdown.list.item wire:click="downloadFile({{ $file->id }})" color="blue" icon="heroicon-o-arrow-down-tray" >
                                    دانلود
                                </x-filament::dropdown.list.item>

                                <x-filament::dropdown.list.item x-on:click="$dispatch('open-modal', { id: 'editItemModal' }); $wire.showRename('file', {{ $file->id }})"  color="orange" icon="heroicon-o-pencil" >
                                    تغییر نام
                                </x-filament::dropdown.list.item>

                                <x-filament::dropdown.list.item wire:click="deleteFile({{ $file->id }})" color="danger" icon="heroicon-o-trash" >
                                    حذف
                                </x-filament::dropdown.list.item>
                            </x-filament::dropdown>
                        </div>

                        <div class="border border-gray-200 rounded-lg p-4 text-center hover:bg-gray-50 cursor-pointer" @click="$wire.downloadFile({{ $file->id }})"
                            @contextmenu.prevent="$wire.selectItem('file', {{ $file->id }})">

                            @if ($file->is_image)
                                <img src="{{ $file->url }}" alt="{{ $file->name }}"
                                    class="w-full h-16 object-cover rounded mb-2">
                            @else
                            <div class="flex justify-center">
                                <svg class="w-16 h-16 text-gray-400 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"></path>
                                </svg>
                            </div>

                            @endif

                            <p class="text-sm font-medium text-gray-700 truncate">{{ $file->original_name }}</p>
                            <p class="text-xs text-gray-500">{{ $file->formatted_size }}</p>
                        </div>
                    </div>
                @endforeach

                <x-filament::modal width="xl" id="editItemModal">

                    <x-slot name="trigger">
                    </x-slot>

                    <x-slot name="heading">
                        ویراش نام
                    </x-slot>

                    {{-- content --}}
                    <div>
                        <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3 mb-2" for="folderName">
                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                نام {{ $itemType === "folder" ? 'پوشه' : 'فایل'}}
                                <sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                            </span>
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="text"
                                id="newName"
                                wire:model="newName"
                            />
                        </x-filament::input.wrapper>
                    </div>

                    <x-slot name="footer">
                        <x-filament::button wire:click="renameItem">
                            ثبت
                        </x-filament::button>
                        <x-filament::button x-on:click="close" color="gray">
                            لغو
                        </x-filament::button>
                    </x-slot>
                </x-filament::modal>
            </div>
        @else
            {{-- List View --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                <input type="checkbox"
                                    @change="$event.target.checked ? $wire.selectAll() : $wire.deselectAll()">
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">نام</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">نوع</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">اندازه</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تاریخ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        {{-- Directories --}}
                        @foreach ($directories as $directory)
                            <tr class="hover:bg-gray-50 cursor-pointer" x-data="{ selected: selectedItems.includes('directory_{{ $directory->id }}') }"
                                wire:click="navigateToDirectory({{ $directory->id }})">
                                <td class="px-2 py-4">
                                    <input type="checkbox" x-model="selected"
                                        @change="selected ? $wire.selectItem('directory', {{ $directory->id }}) : $wire.deselectItem('directory', {{ $directory->id }})"
                                        @click.stop>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas fa-folder text-yellow-500 ml-2"></i>
                                        <span class="text-sm font-medium text-gray-900">{{ $directory->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">پوشه</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $directory->files_count }} فایل</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $directory->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach

                        {{-- Files --}}
                        @foreach ($files as $file)
                            <tr class="hover:bg-gray-50 cursor-pointer" x-data="{ selected: selectedItems.includes('file_{{ $file->id }}') }"
                                wire:click="downloadFile({{ $file->id }})">
                                <td class="px-2 py-4">
                                    <input type="checkbox" x-model="selected"
                                        @change="selected ? $wire.selectItem('file', {{ $file->id }}) : $wire.deselectItem('file', {{ $file->id }})"
                                        @click.stop>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if ($file->is_image)
                                            <img src="{{ $file->url }}" alt="{{ $file->name }}"
                                                class="w-8 h-8 rounded object-cover ml-2">
                                        @else
                                            <i class="fas fa-file text-gray-400 ml-2"></i>
                                        @endif
                                        <span
                                            class="text-sm font-medium text-gray-900">{{ $file->original_name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ ucfirst($file->file_type) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $file->formatted_size }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $file->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Empty State --}}
        @if (count($directories) === 0 && count($files) === 0)
            <div class="text-center py-12">
                <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">هیچ فایلی یافت نشد</h3>
                <p class="text-gray-500">برای شروع، فایل یا پوشه‌ای اضافه کنید</p>
            </div>
        @endif
    </div>

    {{-- Modals --}}
    @include('media-manager::modals.upload')
    @include('media-manager::modals.create-folder')
    @include('media-manager::modals.rename')
    @include('media-manager::modals.delete')
</div>

{{-- FontAwesome icons removed - expecting parent project to include them --}}
