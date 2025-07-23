{{-- Delete Modal --}}
<div x-show="$wire.showDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto">

    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
            wire:click="$set('showDeleteModal', false)"></div>

        <div
            class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-right align-middle transition-all transform bg-white shadow-xl rounded-lg">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 text-red-600">تأیید حذف</h3>
                <button wire:click="$set('showDeleteModal', false)" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-6">
                <div class="flex items-center mb-4">
                    <div
                        class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                </div>

                <p class="text-sm text-gray-500 text-center">
                    آیا از حذف {{ count($selectedItems) }} آیتم انتخاب شده اطمینان دارید؟
                    <br>
                    <span class="text-red-600 font-medium">این عمل قابل بازگشت نیست.</span>
                </p>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" wire:click="$set('showDeleteModal', false)"
                    class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                    انصراف
                </button>

                <button wire:click="deleteSelectedItems"
                    class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                    <i class="fas fa-trash mr-1"></i>
                    حذف
                </button>
            </div>
        </div>
    </div>
</div>
