<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{
        showPicker: false,
        selectedFiles: @entangle($applyStateBindingModifiers('state')),
        multiple: {{ $getMultiple() ? 'true' : 'false' }},
        allowedTypes: @js($getAllowedTypes()),
        disk: '{{ $getDisk() }}'
    }">
        {{-- Display Selected Files --}}
        <div class="mb-3">
            @if ($getMultiple())
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3"
                    x-show="selectedFiles && selectedFiles.length > 0">
                    <template x-for="(fileId, index) in selectedFiles" :key="fileId">
                        <div class="relative border border-gray-300 rounded-lg p-2 bg-gray-50">
                            <div class="aspect-square bg-gray-200 rounded mb-2 flex items-center justify-center">
                                <i class="fas fa-file text-gray-400"></i>
                            </div>
                            <button type="button" @click="selectedFiles.splice(index, 1)"
                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">
                                ×
                            </button>
                        </div>
                    </template>
                </div>
            @else
                <div x-show="selectedFiles"
                    class="border border-gray-300 rounded-lg p-3 bg-gray-50 flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-file text-gray-400 mr-2"></i>
                        <span class="text-sm text-gray-700">فایل انتخاب شده</span>
                    </div>
                    <button type="button" @click="selectedFiles = null" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
        </div>

        {{-- Select Button --}}
        <button type="button" @click="showPicker = true"
            class="w-full border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <i class="fas fa-plus text-2xl text-gray-400 mb-2"></i>
            <p class="text-sm text-gray-600">
                @if ($getMultiple())
                    انتخاب فایل‌ها
                @else
                    انتخاب فایل
                @endif
            </p>
        </button>

        {{-- Media Picker Modal --}}
        <div x-show="showPicker" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto">

            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showPicker = false">
                </div>

                <div
                    class="inline-block w-full max-w-6xl p-6 my-8 overflow-hidden text-right align-middle transition-all transform bg-white shadow-xl rounded-lg">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">انتخاب فایل</h3>
                        <button @click="showPicker = false" class="text-gray-400 hover:text-gray-500">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    {{-- Embed MediaPicker Livewire Component --}}
                    <div class="h-96 overflow-hidden">
                        @livewire('media-picker', [
                            'allowedTypes' => $getAllowedTypes(),
                            'multiple' => $getMultiple(),
                            'selectedFile' => $getState(),
                        ])
                    </div>

                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="showPicker = false"
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                            انصراف
                        </button>

                        <button type="button" @click="showPicker = false"
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            تأیید انتخاب
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Listen for file selection events from Livewire
            window.addEventListener('fileSelected', event => {
                const component = Alpine.$data(document.querySelector('[x-data*="selectedFiles"]'));
                if (component && !component.multiple) {
                    component.selectedFiles = event.detail;
                    component.showPicker = false;
                }
            });

            window.addEventListener('filesSelected', event => {
                const component = Alpine.$data(document.querySelector('[x-data*="selectedFiles"]'));
                if (component && component.multiple) {
                    component.selectedFiles = event.detail;
                    component.showPicker = false;
                }
            });
        </script>
    @endpush
</x-dynamic-component>
