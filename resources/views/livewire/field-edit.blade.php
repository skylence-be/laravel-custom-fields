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
                    <div class="flex gap-2">
                        <input type="text"
                               wire:model.live.debounce.300ms="form.name"
                               class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 @error('form.name') border-red-500 @enderror">
                        @if(count($availableLocales) > 0)
                            <button type="button"
                                    wire:click="copyNameToTranslations"
                                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 whitespace-nowrap text-sm">
                                Copy to Translations
                            </button>
                        @endif
                    </div>
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

        @if(count($availableLocales) > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Translations</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Provide translations for field names in different languages.
                </p>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Translated Field Names
                    </label>
                    @php
                        $languageNames = [
                            'en' => 'English',
                            'nl' => 'Dutch',
                            'fr' => 'French',
                            'de' => 'German',
                            'es' => 'Spanish',
                        ];
                    @endphp
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                        @foreach($form->translations as $index => $translation)
                            <div>
                                <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">
                                    {{ $languageNames[$translation['locale']] ?? strtoupper($translation['locale']) }}
                                </label>
                                <input type="text"
                                       wire:model.live.debounce.300ms="form.translations.{{ $index }}.name"
                                       placeholder="Translation..."
                                       class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

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
                        <input type="text"
                               value="{{ $field->input_type?->label() ?? $textInputTypes[$form->input_type] ?? $form->input_type }}"
                               disabled
                               class="w-full px-4 py-2 border rounded-lg bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Input type cannot be changed after creation.</p>
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
                        <div class="space-y-3">
                            @foreach($form->options as $optionId => $option)
                                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-3">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="flex flex-col gap-1">
                                            <button type="button"
                                                    wire:click="moveOption({{ $optionId }}, 'up')"
                                                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-xs"
                                                    title="Move up">
                                                ▲
                                            </button>
                                            <button type="button"
                                                    wire:click="moveOption({{ $optionId }}, 'down')"
                                                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-xs"
                                                    title="Move down">
                                                ▼
                                            </button>
                                        </div>
                                        <span class="flex-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $option }}</span>
                                        @if(count($availableLocales) > 0)
                                            <button type="button"
                                                    wire:click="copyOptionToTranslations({{ $optionId }})"
                                                    class="px-3 py-1 bg-gray-600 text-white rounded hover:bg-gray-700 text-xs whitespace-nowrap">
                                                Copy to Translations
                                            </button>
                                        @endif
                                        <button type="button"
                                                wire:click="removeOption({{ $optionId }})"
                                                class="text-red-600 hover:text-red-800 text-sm">
                                            Remove
                                        </button>
                                    </div>

                                    @if(count($availableLocales) > 0)
                                        @php
                                            $languageNames = [
                                                'en' => 'English',
                                                'nl' => 'Dutch',
                                                'fr' => 'French',
                                                'de' => 'German',
                                                'es' => 'Spanish',
                                            ];
                                        @endphp
                                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2 mt-2">
                                            @foreach($form->translations as $translationIndex => $translation)
                                                <div>
                                                    <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">
                                                        {{ $languageNames[$translation['locale']] ?? strtoupper($translation['locale']) }}
                                                    </label>
                                                    <input type="text"
                                                           wire:model.live.debounce.300ms="form.translations.{{ $translationIndex }}.options.{{ $optionId }}"
                                                           placeholder="Translation..."
                                                           class="w-full px-2 py-1 text-sm border rounded focus:ring-1 focus:ring-blue-500 dark:bg-gray-600 dark:text-white dark:border-gray-500">
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @error('form.options')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            @if(in_array($form->type, ['select', 'radio']) && config('custom-fields.enable_default_options'))
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Default Option
                    </label>
                    <select wire:model.live="form.default_option"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        <option value="">None (no default)</option>
                        @foreach($form->options as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        This option will be pre-selected when the field is displayed.
                    </p>
                </div>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Validation Rules</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Add validation rules that will be applied when this field is used in forms.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Select Rule
                    </label>
                    <select wire:model.live="selectedValidationRule"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        <option value="">Choose a validation rule...</option>
                        <optgroup label="Simple Rules">
                            @foreach($simpleValidationRules as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Rules With Parameters">
                            @foreach($parametrizedValidationRules as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>

                @if(!empty($selectedValidationRule) && array_key_exists($selectedValidationRule, $parametrizedValidationRules))
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Parameter
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-normal">
                                ({{ $parametrizedValidationRules[$selectedValidationRule] }})
                            </span>
                        </label>
                        <input type="text"
                               wire:model="validationRuleParameter"
                               wire:keydown.enter.prevent="addValidationRule"
                               placeholder="Enter parameter value..."
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    </div>
                @endif
            </div>

            <div class="mb-4">
                <button type="button"
                        wire:click="addValidationRule"
                        :disabled="!selectedValidationRule"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    Add Validation Rule
                </button>
            </div>

            @if(!empty($form->validation_rules))
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Active Validation Rules
                    </label>
                    <div class="space-y-2">
                        @foreach($form->validation_rules as $index => $rule)
                            <div class="flex items-center gap-2 p-3 bg-gray-50 dark:bg-gray-700 rounded">
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ \Skylence\LaravelCustomFields\Enums\ValidationRule::toHumanReadable($rule) }}
                                    </span>
                                    <code class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $rule }}</code>
                                </div>
                                <button type="button"
                                        wire:click="removeValidationRule({{ $index }})"
                                        class="text-red-600 hover:text-red-800 text-sm font-medium">
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

        <!-- Metadata Section -->
        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Metadata</h2>
                <a href="{{ route(config('custom-fields.route.name_prefix') . 'activity', $field) }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    View Activity Log
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Created</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $field->created_at->format('M d, Y \a\t H:i') }}
                        @if($field->creator)
                            <span class="block mt-1">
                                by <span class="font-medium text-gray-900 dark:text-white">{{ $field->creator->name ?? $field->creator->email }}</span>
                            </span>
                        @endif
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last Updated</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $field->updated_at->format('M d, Y \a\t H:i') }}
                        @if($field->updater)
                            <span class="block mt-1">
                                by <span class="font-medium text-gray-900 dark:text-white">{{ $field->updater->name ?? $field->updater->email }}</span>
                            </span>
                        @endif
                    </p>
                </div>
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
