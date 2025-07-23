<div class="media-manager bg-white rounded-lg shadow-lg" x-data="{ selectedItems: @entangle('selectedItems') }">
    {{-- Header --}}
    <div class="border-b border-gray-200 p-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            {{-- Disk Selector --}}
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">دیسک:</label>
                <select wire:model="selectedDisk" wire:change="changeDisk($event.target.value)"
                    class="border border-gray-300 rounded-md px-3 py-1 text-sm">
                    @foreach ($availableDisks as $disk => $config)
                        <option value="{{ $disk }}">{{ $config['label'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2">
                <button wire:click="showUpload"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">
                    <i class="fas fa-upload mr-1"></i>
                    آپلود فایل
                </button>

                <button wire:click="showCreateFolder"
                    class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                    <i class="fas fa-folder-plus mr-1"></i>
                    پوشه جدید
                </button>

                @if (count($selectedItems) > 0)
                    <button wire:click="showDelete"
                        class="bg-red-600 text-white px-4 py-2 rounded-md text-sm hover:bg-red-700">
                        <i class="fas fa-trash mr-1"></i>
                        حذف
                    </button>
                @endif

                {{-- View Mode Toggle --}}
                <div class="flex border rounded-md">
                    <button wire:click="changeViewMode('grid')"
                        class="px-3 py-1 text-sm {{ $viewMode === 'grid' ? 'bg-gray-200' : '' }}">
                        <i class="fas fa-th"></i>
                    </button>
                    <button wire:click="changeViewMode('list')"
                        class="px-3 py-1 text-sm {{ $viewMode === 'list' ? 'bg-gray-200' : '' }}">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Search Bar --}}
        <div class="mt-4 flex gap-2">
            <div class="flex-1 relative">
                <input wire:model.debounce.300ms="searchQuery" wire:keydown.enter="search" type="text"
                    placeholder="جستجوی فایل..." class="w-full border border-gray-300 rounded-md px-3 py-2 pr-10">
                <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
            </div>

            <select wire:model="selectedFileType" wire:change="search"
                class="border border-gray-300 rounded-md px-3 py-2">
                <option value="">همه فایل‌ها</option>
                <option value="image">تصاویر</option>
                <option value="document">اسناد</option>
                <option value="video">ویدیو</option>
                <option value="audio">صوتی</option>
            </select>

            @if ($searchQuery)
                <button wire:click="clearSearch" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                    پاک کردن
                </button>
            @endif
        </div>
    </div>

    {{-- Breadcrumbs --}}
    @if (count($breadcrumbs) > 0)
        <div class="p-4 border-b border-gray-100">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-reverse space-x-2">
                    <li>
                        <button wire:click="navigateToDirectory(null)" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-home"></i>
                        </button>
                    </li>
                    @foreach ($breadcrumbs as $crumb)
                        <li class="flex items-center">
                            <i class="fas fa-chevron-left text-gray-400 mx-2"></i>
                            <button wire:click="navigateToDirectory({{ $crumb['id'] }})"
                                class="text-blue-600 hover:text-blue-800">
                                {{ $crumb['name'] }}
                            </button>
                        </li>
                    @endforeach
                </ol>
            </nav>
        </div>
    @endif

    {{-- Content Area --}}
    <div class="p-4 min-h-96">
        @if ($viewMode === 'grid')
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                {{-- Navigate Up Button --}}
                @if ($currentDirectory)
                    <div class="border border-gray-200 rounded-lg p-4 text-center hover:bg-gray-50 cursor-pointer"
                        wire:click="navigateUp">
                        <i class="fas fa-level-up-alt text-2xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-600">بازگشت</p>
                    </div>
                @endif

                {{-- Directories --}}
                @foreach ($directories as $directory)
                    <div class="border border-gray-200 rounded-lg p-4 text-center hover:bg-gray-50 cursor-pointer relative"
                        x-data="{ selected: selectedItems.includes('directory_{{ $directory->id }}') }" @click="$wire.navigateToDirectory({{ $directory->id }})"
                        @contextmenu.prevent="$wire.selectItem('directory', {{ $directory->id }})">

                        <input type="checkbox" x-model="selected"
                            @change="selected ? $wire.selectItem('directory', {{ $directory->id }}) : $wire.deselectItem('directory', {{ $directory->id }})"
                            class="absolute top-2 left-2">

                        <i class="fas fa-folder text-3xl text-yellow-500 mb-2"></i>
                        <p class="text-sm font-medium text-gray-700 truncate">{{ $directory->name }}</p>
                        <p class="text-xs text-gray-500">{{ $directory->files_count }} فایل</p>
                    </div>
                @endforeach

                {{-- Files --}}
                @foreach ($files as $file)
                    <div class="border border-gray-200 rounded-lg p-4 text-center hover:bg-gray-50 cursor-pointer relative"
                        x-data="{ selected: selectedItems.includes('file_{{ $file->id }}') }" @click="$wire.downloadFile({{ $file->id }})"
                        @contextmenu.prevent="$wire.selectItem('file', {{ $file->id }})">

                        <input type="checkbox" x-model="selected"
                            @change="selected ? $wire.selectItem('file', {{ $file->id }}) : $wire.deselectItem('file', {{ $file->id }})"
                            class="absolute top-2 left-2">

                        @if ($file->is_image)
                            <img src="{{ $file->url }}" alt="{{ $file->name }}"
                                class="w-full h-16 object-cover rounded mb-2">
                        @else
                            <i class="fas fa-file text-3xl text-gray-400 mb-2"></i>
                        @endif

                        <p class="text-sm font-medium text-gray-700 truncate">{{ $file->original_name }}</p>
                        <p class="text-xs text-gray-500">{{ $file->formatted_size }}</p>
                    </div>
                @endforeach
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
                        {{-- Navigate Up --}}
                        @if ($currentDirectory)
                            <tr class="hover:bg-gray-50 cursor-pointer" wire:click="navigateUp">
                                <td class="px-2 py-4"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas fa-level-up-alt text-gray-400 ml-2"></i>
                                        <span class="text-sm font-medium text-gray-900">بازگشت</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">پوشه</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                            </tr>
                        @endif

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
