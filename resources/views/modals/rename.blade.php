{{-- Rename Modal --}}
<div x-show="$wire.showRenameModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto">

    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
            wire:click="$set('showRenameModal', false)"></div>

        <div
            class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-right align-middle transition-all transform bg-white shadow-xl rounded-lg">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">تغییر نام</h3>
                <button wire:click="$set('showRenameModal', false)" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form wire:submit.prevent="renameItem">
                <div class="mb-4">
                    <label for="newName" class="block text-sm font-medium text-gray-700 mb-2">نام جدید</label>
                    <input type="text" id="newName" wire:model="newName"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="نام جدید را وارد کنید..." required>

                    @error('newName')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="$set('showRenameModal', false)"
                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                        انصراف
                    </button>

                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        تغییر نام
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
