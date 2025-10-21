<?php

namespace Xve\LaravelCustomFields\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Form;
use Xve\LaravelCustomFields\Enums\FieldType;
use Xve\LaravelCustomFields\Models\Field;

class FieldForm extends Form
{
    public ?Field $field = null;

    public string $name = '';

    public string $code = '';

    public string $type = 'text';

    public string $input_type = 'text';

    public bool $is_multiselect = false;

    public array $options = [];

    public bool $use_in_table = false;

    public string $customizable_type = '';

    public int $sort = 0;

    public function rules(): array
    {
        $fieldId = $this->field?->id ?? 'NULL';

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
            'use_in_table' => 'boolean',
            'customizable_type' => 'required|string',
            'sort' => 'nullable|integer|min:0',
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
        $this->type = $field->type?->value ?? 'text';
        $this->input_type = $field->input_type?->value ?? 'text';
        $this->is_multiselect = $field->is_multiselect;
        $this->options = $field->options ?? [];
        $this->use_in_table = $field->use_in_table;
        $this->customizable_type = $field->customizable_type;
        $this->sort = $field->sort;
    }

    public function store(): Field
    {
        $this->validate();

        return Field::create($this->getFieldData());
    }

    public function update(): void
    {
        $this->validate($this->updateRules());

        $this->field->update($this->getUpdateData());
    }

    protected function updateRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'input_type' => 'nullable|string',
            'is_multiselect' => 'boolean',
            'options' => 'nullable|array',
            'use_in_table' => 'boolean',
            'sort' => 'nullable|integer|min:0',
        ];
    }

    protected function getUpdateData(): array
    {
        return [
            'name' => $this->name,
            'input_type' => $this->type === 'text' ? $this->input_type : null,
            'is_multiselect' => $this->is_multiselect,
            'options' => in_array($this->type, ['select', 'radio', 'checkbox_list']) ? $this->options : null,
            'use_in_table' => $this->use_in_table,
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
            'use_in_table' => $this->use_in_table,
            'customizable_type' => $this->customizable_type,
            'sort' => $this->sort,
        ];
    }

    public function addOption(string $newOption): void
    {
        if (! empty($newOption)) {
            $this->options[] = $newOption;
        }
    }

    public function removeOption(int $index): void
    {
        unset($this->options[$index]);
        $this->options = array_values($this->options);
    }
}
