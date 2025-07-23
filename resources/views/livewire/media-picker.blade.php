<div class="media-picker h-full">
    <div class="flex flex-col h-full">
        {{-- Header --}}
        <div class="border-b border-gray-200 p-4 flex-shrink-0">
            <div class="flex items-center justify-between">
                {{-- Breadcrumbs --}}
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
                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                    {{ $crumb['name'] }}
                                </button>
                            </li>
                        @endforeach
                    </ol>
                </nav>
            </div>
        </div>

        {{-- Content --}}
        <div class="flex-1 overflow-auto p-4">
            <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                {{-- Navigate Up Button --}}
                @if ($currentDirectory)
                    <div class="border border-gray-200 rounded-lg p-3 text-center hover:bg-gray-50 cursor-pointer"
                        wire:click="navigateUp">
                        <i class="fas fa-level-up-alt text-2xl text-gray-400 mb-2"></i>
                        <p class="text-xs text-gray-600">بازگشت</p>
                    </div>
                @endif

                {{-- Directories --}}
                @foreach ($directories as $directory)
                    <div class="border border-gray-200 rounded-lg p-3 text-center hover:bg-gray-50 cursor-pointer"
                        wire:click="navigateToDirectory({{ $directory->id }})">
                        <i class="fas fa-folder text-2xl text-yellow-500 mb-2"></i>
                        <p class="text-xs font-medium text-gray-700 truncate">{{ $directory->name }}</p>
                    </div>
                @endforeach

                {{-- Files --}}
                @foreach ($files as $file)
                    <div class="border border-gray-200 rounded-lg p-3 text-center hover:bg-gray-50 cursor-pointer relative"
                        wire:click="selectFile({{ $file->id }})" x-data="{ selected: @if ($multiple) $wire.selectedFiles.includes({{ $file->id }}) @else $wire.selectedFile == {{ $file->id }} @endif }"
                        :class="{ 'ring-2 ring-blue-500 bg-blue-50': selected }">

                        @if ($file->is_image)
                            <img src="{{ $file->url }}" alt="{{ $file->name }}"
                                class="w-full h-12 object-cover rounded mb-2">
                        @else
                            <i class="fas fa-file text-2xl text-gray-400 mb-2"></i>
                        @endif

                        <p class="text-xs font-medium text-gray-700 truncate">{{ $file->original_name }}</p>

                        {{-- Multiple selection indicator --}}
                        @if ($multiple)
                            <div class="absolute top-1 left-1">
                                <input type="checkbox" x-model="selected"
                                    @change="selected ? $wire.selectFile({{ $file->id }}) : $wire.deselectFile({{ $file->id }})"
                                    class="rounded">
                            </div>
                        @endif

                        {{-- Single selection indicator --}}
                        @if (!$multiple && $selectedFile == $file->id)
                            <div class="absolute top-1 left-1">
                                <i class="fas fa-check-circle text-blue-600"></i>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Empty State --}}
            @if (count($directories) === 0 && count($files) === 0)
                <div class="text-center py-8">
                    <i class="fas fa-folder-open text-4xl text-gray-300 mb-3"></i>
                    <h3 class="text-sm font-medium text-gray-900 mb-1">هیچ فایلی یافت نشد</h3>
                    <p class="text-xs text-gray-500">این پوشه خالی است</p>
                </div>
            @endif
        </div>

        {{-- Footer --}}
        @if ($multiple && count($selectedFiles) > 0)
            <div class="border-t border-gray-200 p-4 flex-shrink-0">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">
                        {{ count($selectedFiles) }} فایل انتخاب شده
                    </span>
                    <button wire:click="confirmSelection"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">
                        تأیید انتخاب
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
