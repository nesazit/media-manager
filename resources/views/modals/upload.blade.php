{{-- Upload Modal --}}
<div x-show="$wire.showUploadModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto">

    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
            wire:click="$set('showUploadModal', false)"></div>

        <div
            class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-right align-middle transition-all transform bg-white shadow-xl rounded-lg">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">آپلود فایل‌ها</h3>
                <button wire:click="$set('showUploadModal', false)" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>


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

                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="$set('showUploadModal', false)"
                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                        انصراف
                    </button>

                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
                        :disabled="!$wire.uploadFiles.length">
                        آپلود
                    </button>
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
    </div>
</div>
