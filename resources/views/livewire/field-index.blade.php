<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Custom Fields</h1>
        <a href="{{ route(config('custom-fields.route.name_prefix') . 'create') }}"
           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Create New Field
        </a>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Search by name or code..."
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Type</label>
                <select wire:model.live="filterType"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">All Types</option>
                    @foreach($fieldTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Model</label>
                <select wire:model.live="filterCustomizableType"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <option value="">All Models</option>
                    @foreach($customizableTypes as $type)
                        <option value="{{ $type }}">{{ class_basename($type) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if(!empty($selectedFields))
            <div class="mb-4">
                <button wire:click="bulkDelete"
                        onclick="return confirm('Are you sure you want to delete {{ count($selectedFields) }} selected fields?')"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    Delete Selected ({{ count($selectedFields) }})
                </button>
            </div>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox"
                                   wire:model="selectAll"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Model</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">In Table</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($fields as $field)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 {{ $field->trashed() ? 'opacity-60' : '' }}">
                            <td class="px-6 py-4">
                                <input type="checkbox"
                                       wire:model="selectedFields"
                                       value="{{ $field->id }}"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                {{ $field->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 font-mono">
                                {{ $field->code }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded">
                                    {{ $fieldTypes[$field->type] ?? $field->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ class_basename($field->customizable_type) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                @if($field->use_in_table)
                                    <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded">Yes</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300 rounded">No</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($field->trashed())
                                    <span class="px-2 py-1 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded">Deleted</span>
                                @else
                                    <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded">Active</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-sm space-x-2">
                                @if($field->trashed())
                                    <button wire:click="restoreField({{ $field->id }})"
                                            class="text-green-600 hover:text-green-900 dark:text-green-400">
                                        Restore
                                    </button>
                                    <button wire:click="confirmDelete({{ $field->id }}, true)"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400">
                                        Delete Forever
                                    </button>
                                @else
                                    <a href="{{ route(config('custom-fields.route.name_prefix') . 'edit', $field) }}"
                                       class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
                                        Edit
                                    </a>
                                    <button wire:click="confirmDelete({{ $field->id }})"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400">
                                        Delete
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                No custom fields found. <a href="{{ route(config('custom-fields.route.name_prefix') . 'create') }}" class="text-blue-600 hover:underline">Create your first field</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $fields->links() }}
        </div>
    </div>

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
                    <button wire:click="deleteField"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        {{ $forceDelete ? 'Delete Forever' : 'Delete' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
