<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <a href="{{ route(config('custom-fields.route.name_prefix') . 'index') }}"
               class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                ← Back to Custom Fields
            </a>
            <button wire:click="confirmDelete(false)"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                Delete Field
            </button>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Custom Field</h1>
    </div>

    <form wire:submit="save">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">General Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           wire:model.live.debounce.300ms="form.name"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('form.name') border-red-500 @enderror">
                    @error('form.name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Code
                    </label>
                    <input type="text"
                           value="{{ $form->code }}"
                           disabled
                           class="w-full px-4 py-2 border rounded-lg font-mono bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Code cannot be changed after creation.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Model Type
                    </label>
                    <input type="text"
                           value="{{ $customizableTypes[$form->customizable_type] ?? $form->customizable_type }}"
                           disabled
                           class="w-full px-4 py-2 border rounded-lg bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Model type cannot be changed after creation.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Sort Order
                    </label>
                    <input type="number"
                           wire:model.live.debounce.300ms="form.sort"
                           min="0"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('form.sort') border-red-500 @enderror">
                    @error('form.sort')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Field Type Configuration</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Field Type
                    </label>
                    <input type="text"
                           value="{{ $field->type?->label() ?? $fieldTypes[$form->type] ?? $form->type }}"
                           disabled
                           class="w-full px-4 py-2 border rounded-lg bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Field type cannot be changed after creation.</p>
                </div>

                @if($form->type === 'text')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Input Type
                        </label>
                        <select wire:model.live="form.input_type"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                            @foreach($textInputTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if($form->type === 'select')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Allow Multiple Selection
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox"
                                   wire:model.live="form.is_multiselect"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable multiselect</span>
                        </label>
                    </div>
                @endif
            </div>

            @if(in_array($form->type, ['select', 'radio', 'checkbox_list']))
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Options <span class="text-red-500">*</span>
                    </label>

                    <div class="flex gap-2 mb-3">
                        <input type="text"
                               wire:model="newOption"
                               wire:keydown.enter.prevent="addOption"
                               placeholder="Enter an option..."
                               class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        <button type="button"
                                wire:click="addOption"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Add Option
                        </button>
                    </div>

                    @if(!empty($form->options))
                        <div class="space-y-2">
                            @foreach($form->options as $index => $option)
                                <div class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-gray-700 rounded">
                                    <span class="flex-1 text-sm text-gray-700 dark:text-gray-300">{{ $option }}</span>
                                    <button type="button"
                                            wire:click="removeOption({{ $index }})"
                                            class="text-red-600 hover:text-red-800 text-sm">
                                        Remove
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @error('form.options')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Display Settings</h2>

            <div>
                <label class="flex items-center">
                    <input type="checkbox"
                           wire:model.live="form.use_in_table"
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Show in table columns</span>
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ml-6">
                    When enabled, this field will appear as a column in list views.
                </p>
            </div>
        </div>

        <div class="flex justify-between">
            <button type="button"
                    wire:click="confirmDelete(true)"
                    class="px-6 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800">
                Delete Forever
            </button>

            <div class="flex gap-4">
                <a href="{{ route(config('custom-fields.route.name_prefix') . 'index') }}"
                   class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Update Field
                </button>
            </div>
        </div>
    </form>

    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                    Confirm {{ $forceDelete ? 'Permanent' : '' }} Deletion
                </h3>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    @if($forceDelete)
                        Are you sure you want to permanently delete this field? This will also remove the database column and cannot be undone.
                    @else
                        Are you sure you want to delete this field? The database column will be kept and can be restored later.
                    @endif
                </p>
                <div class="flex justify-end gap-4">
                    <button wire:click="$set('showDeleteModal', false)"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button wire:click="delete"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        {{ $forceDelete ? 'Delete Forever' : 'Delete' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
