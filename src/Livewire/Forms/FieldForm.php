<?php

namespace Skylence\LaravelCustomFields\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Form;
use Skylence\LaravelCustomFields\Enums\FieldType;
use Skylence\LaravelCustomFields\Enums\ValidationRule;
use Skylence\LaravelCustomFields\Models\Field;

class FieldForm extends Form
{
    public ?Field $field = null;

    public string $name = '';

    public string $code = '';

    public string $type = 'text';

    public string $input_type = 'text';

    public bool $is_multiselect = false;

    public array $options = [];

    public ?string $default_option = null;

    public int $nextOptionId = 1;

    public bool $use_in_table = false;

    public array $validation_rules = [];

    public array $form_settings = [];

    public string $customizable_type = '';

    public int $sort = 0;

    public array $translations = [];

    public function rules(): array
    {
        $fieldId = $this->field->id ?? 'NULL';

        return [
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/',
                'unique:custom_fields,code,'.$fieldId.',id,customizable_type,'.$this->customizable_type,
            ],
            'type' => ['required', Rule::enum(FieldType::class)],
            'input_type' => 'nullable|string',
            'is_multiselect' => 'boolean',
            'options' => 'nullable|array',
            'default_option' => 'nullable|string',
            'use_in_table' => 'boolean',
            'validation_rules' => 'nullable|array',
            'customizable_type' => 'required|string',
            'sort' => 'nullable|integer|min:0',
            'translations' => 'nullable|array',
            'translations.*.locale' => 'required|string|max:10',
            'translations.*.name' => 'required|string|max:255',
            'translations.*.options' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'Code must start with a letter or underscore and contain only letters, numbers, and underscores.',
            'code.unique' => 'This code already exists for the selected model.',
            'customizable_type.required' => 'Please select a model type.',
        ];
    }

    public function setField(Field $field): void
    {
        $this->field = $field;

        $this->name = $field->name;
        $this->code = $field->code;
        $this->type = $field->type->value ?? 'text';
        $this->input_type = $field->input_type->value ?? 'text';
        $this->is_multiselect = $field->is_multiselect;

        // Convert old array-based options to key-based structure
        $this->options = $this->convertOptionsToKeyBased($field->options ?? []);
        $this->nextOptionId = $this->getNextOptionId();

        $this->default_option = $field->default_option;
        $this->use_in_table = $field->use_in_table;
        $this->form_settings = $field->form_settings ?? [];
        $this->validation_rules = $field->form_settings['validation_rules'] ?? [];
        $this->customizable_type = $field->customizable_type;
        $this->sort = $field->sort;

        // Load existing translations and ensure all locales are represented
        $existingTranslations = $field->translations->keyBy('locale');
        $this->translations = [];

        foreach ($this->getAvailableLocales() as $locale) {
            if (isset($existingTranslations[$locale])) {
                $this->translations[] = [
                    'locale' => $locale,
                    'name' => $existingTranslations[$locale]->name,
                    'options' => $this->convertOptionsToKeyBased($existingTranslations[$locale]->options ?? []),
                ];
            } else {
                $this->translations[] = [
                    'locale' => $locale,
                    'name' => '',
                    'options' => [],
                ];
            }
        }
    }

    public function store(): Field
    {
        $this->validate();

        $field = Field::create($this->getFieldData());

        // Save translations
        $this->saveTranslations($field);

        return $field;
    }

    public function update(): void
    {
        $this->validate($this->updateRules());

        $this->field->update($this->getUpdateData());

        // Save translations
        $this->saveTranslations($this->field);
    }

    protected function saveTranslations(Field $field): void
    {
        if (empty($this->translations)) {
            return;
        }

        foreach ($this->translations as $translation) {
            // Save translation if name or options are provided
            $hasName = ! empty($translation['name']);
            $hasOptions = ! empty($translation['options']) && is_array($translation['options']) && count(array_filter($translation['options'])) > 0;

            if (! empty($translation['locale']) && ($hasName || $hasOptions)) {
                $field->setTranslation(
                    $translation['locale'],
                    $translation['name'] ?? '',
                    $hasOptions ? $translation['options'] : null
                );
            }
        }
    }

    protected function updateRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'input_type' => 'nullable|string',
            'is_multiselect' => 'boolean',
            'options' => 'nullable|array',
            'default_option' => 'nullable|string',
            'use_in_table' => 'boolean',
            'validation_rules' => 'nullable|array',
            'sort' => 'nullable|integer|min:0',
            'translations' => 'nullable|array',
            'translations.*.locale' => 'required|string|max:10',
            'translations.*.name' => 'required|string|max:255',
            'translations.*.options' => 'nullable|array',
        ];
    }

    protected function getUpdateData(): array
    {
        return [
            'name' => $this->name,
            'input_type' => $this->type === 'text' ? $this->input_type : null,
            'is_multiselect' => $this->is_multiselect,
            'options' => in_array($this->type, ['select', 'radio', 'checkbox_list']) ? $this->options : null,
            'default_option' => in_array($this->type, ['select', 'radio']) ? $this->default_option : null,
            'use_in_table' => $this->use_in_table,
            'form_settings' => array_merge($this->form_settings, [
                'validation_rules' => $this->validation_rules,
            ]),
            'sort' => $this->sort,
        ];
    }

    protected function getFieldData(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'input_type' => $this->type === 'text' ? $this->input_type : null,
            'is_multiselect' => $this->is_multiselect,
            'options' => in_array($this->type, ['select', 'radio', 'checkbox_list']) ? $this->options : null,
            'default_option' => in_array($this->type, ['select', 'radio']) ? $this->default_option : null,
            'use_in_table' => $this->use_in_table,
            'form_settings' => [
                'validation_rules' => $this->validation_rules,
            ],
            'customizable_type' => $this->customizable_type,
            'sort' => $this->sort,
        ];
    }

    public function addOption(string $newOption): void
    {
        if (! empty($newOption)) {
            $id = $this->nextOptionId++;
            $this->options[$id] = $newOption;
        }
    }

    public function removeOption(int $id): void
    {
        unset($this->options[$id]);

        // Also remove from all translations
        foreach ($this->translations as $index => $translation) {
            if (isset($this->translations[$index]['options'][$id])) {
                unset($this->translations[$index]['options'][$id]);
            }
        }
    }

    public function moveOption(int $id, string $direction): void
    {
        $keys = array_keys($this->options);
        $currentIndex = array_search($id, $keys);

        if ($currentIndex === false) {
            return;
        }

        if ($direction === 'up' && $currentIndex > 0) {
            $swapIndex = $currentIndex - 1;
        } elseif ($direction === 'down' && $currentIndex < count($keys) - 1) {
            $swapIndex = $currentIndex + 1;
        } else {
            return;
        }

        // Swap the keys
        $temp = $keys[$currentIndex];
        $keys[$currentIndex] = $keys[$swapIndex];
        $keys[$swapIndex] = $temp;

        // Rebuild options array with new order
        $newOptions = [];
        foreach ($keys as $key) {
            $newOptions[$key] = $this->options[$key];
        }
        $this->options = $newOptions;
    }

    public function addValidationRule(string $rule): void
    {
        if (! empty($rule) && ! in_array($rule, $this->validation_rules)) {
            $this->validation_rules[] = $rule;
        }
    }

    public function removeValidationRule(int $index): void
    {
        unset($this->validation_rules[$index]);
        $this->validation_rules = array_values($this->validation_rules);
    }

    public function getAvailableValidationRules(): array
    {
        return ValidationRule::options();
    }

    public function getParametrizedValidationRules(): array
    {
        return ValidationRule::parametrizedRules();
    }

    public function getAllValidationRules(): array
    {
        return ValidationRule::allRules();
    }

    public function getAvailableLocales(): array
    {
        return config('app.locales', ['en']);
    }

    public function initializeTranslations(): void
    {
        $locales = $this->getAvailableLocales();
        $this->translations = [];

        foreach ($locales as $locale) {
            $this->translations[] = [
                'locale' => $locale,
                'name' => '',
                'options' => [],
            ];
        }
    }

    protected function convertOptionsToKeyBased(array $options): array
    {
        // If already key-based, return as is
        if (empty($options)) {
            return [];
        }

        // Check if this is an old sequential array [0 => 'value1', 1 => 'value2']
        $keys = array_keys($options);
        $isSequential = $keys === range(0, count($options) - 1);

        if (! $isSequential) {
            // Already has proper keys, return as is
            return $options;
        }

        // Convert old sequential array to key-based using index + 1 as ID
        // This ensures backward compatibility while giving us stable IDs
        $keyBased = [];
        foreach ($options as $index => $value) {
            $keyBased[$index + 1] = $value;
        }

        return $keyBased;
    }

    protected function getNextOptionId(): int
    {
        if (empty($this->options)) {
            return 1;
        }

        return max(array_keys($this->options)) + 1;
    }
}
