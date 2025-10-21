<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-2">
            <a href="{{ route(config('custom-fields.route.name_prefix') . 'index') }}"
               class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                ← Back to Custom Fields
            </a>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Custom Field</h1>
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
                        Code <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           wire:model.live.debounce.300ms="form.code"
                           class="w-full px-4 py-2 border rounded-lg font-mono focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('form.code') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Must start with a letter or underscore, contain only letters, numbers, and underscores.</p>
                    @error('form.code')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Model Type <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="form.customizable_type"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('form.customizable_type') border-red-500 @enderror">
                        <option value="">Select a model...</option>
                        @foreach($customizableTypes as $class => $label)
                            <option value="{{ $class }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('form.customizable_type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
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
                        Field Type <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="form.type"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('form.type') border-red-500 @enderror">
                        @foreach($fieldTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('form.type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
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
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Validation Rules</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Add validation rules that will be applied when this field is used in forms.
            </p>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Quick Add - Common Rules
                </label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    @foreach($availableValidationRules as $value => $label)
                        <button type="button"
                                wire:click="addValidationRule('{{ $value }}')"
                                class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-200 dark:hover:bg-gray-600 text-left">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Rules With Parameters
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                    These rules require parameters. Enter the full rule with its parameters below.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach($parametrizedValidationRules as $example => $description)
                        <div class="text-xs text-gray-600 dark:text-gray-400 p-2 bg-gray-50 dark:bg-gray-700 rounded">
                            <code class="font-mono text-blue-600 dark:text-blue-400">{{ $example }}</code>
                            <span class="ml-2">- {{ $description }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Custom Rule or Rule With Parameters
                </label>
                <div class="flex gap-2">
                    <input type="text"
                           wire:model="newValidationRule"
                           wire:keydown.enter.prevent="addValidationRule"
                           placeholder="Enter a validation rule (e.g., min:5, max:100)..."
                           class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    <button type="button"
                            wire:click="addValidationRule"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Add Rule
                    </button>
                </div>
            </div>

            @if(!empty($form->validation_rules))
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Active Validation Rules
                    </label>
                    <div class="space-y-2">
                        @foreach($form->validation_rules as $index => $rule)
                            <div class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-gray-700 rounded">
                                <code class="flex-1 text-sm font-mono text-gray-700 dark:text-gray-300">{{ $rule }}</code>
                                <button type="button"
                                        wire:click="removeValidationRule({{ $index }})"
                                        class="text-red-600 hover:text-red-800 text-sm">
                                    Remove
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @error('form.validation_rules')
                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
            @enderror
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

        <div class="flex justify-end gap-4">
            <a href="{{ route(config('custom-fields.route.name_prefix') . 'index') }}"
               class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Create Field
            </button>
        </div>
    </form>
</div>
