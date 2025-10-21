<?php

namespace Xve\LaravelCustomFields\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Schema;
use Xve\LaravelCustomFields\Models\Field;
use Xve\LaravelCustomFields\Services\FieldsColumnManager;

class FieldCreate extends Component
{
    public string $name = '';

    public string $code = '';

    public string $type = 'text';

    public string $input_type = 'text';

    public bool $is_multiselect = false;

    public array $options = [];

    public string $newOption = '';

    public bool $use_in_table = false;

    public string $customizable_type = '';

    public int $sort = 0;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/',
                'unique:custom_fields,code,NULL,id,customizable_type,'.$this->customizable_type,
            ],
            'type' => 'required|in:'.implode(',', array_keys(Field::getFieldTypes())),
            'input_type' => 'nullable|string',
            'is_multiselect' => 'boolean',
            'options' => 'nullable|array',
            'use_in_table' => 'boolean',
            'customizable_type' => 'required|string',
            'sort' => 'nullable|integer|min:0',
        ];
    }

    protected $messages = [
        'code.regex' => 'Code must start with a letter or underscore and contain only letters, numbers, and underscores.',
        'code.unique' => 'This code already exists for the selected model.',
        'customizable_type.required' => 'Please select a model type.',
    ];

    public function mount(): void
    {
        $this->sort = Field::max('sort') + 1 ?? 0;
    }

    public function updatedCode(): void
    {
        // Auto-generate code from name if empty
        if (empty($this->code) && ! empty($this->name)) {
            $this->code = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $this->name));
        }
    }

    public function addOption(): void
    {
        if (! empty($this->newOption)) {
            $this->options[] = $this->newOption;
            $this->newOption = '';
        }
    }

    public function removeOption(int $index): void
    {
        unset($this->options[$index]);
        $this->options = array_values($this->options);
    }

    public function save(): void
    {
        $this->validate();

        // Check if code conflicts with existing database columns
        if (! FieldsColumnManager::canCreateColumn($this->code, $this->customizable_type)) {
            $this->addError('code', 'This code conflicts with an existing database column.');

            return;
        }

        $field = Field::create([
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'input_type' => $this->type === 'text' ? $this->input_type : null,
            'is_multiselect' => $this->is_multiselect,
            'options' => in_array($this->type, ['select', 'radio', 'checkbox_list']) ? $this->options : null,
            'use_in_table' => $this->use_in_table,
            'customizable_type' => $this->customizable_type,
            'sort' => $this->sort,
        ]);

        // Create database column
        FieldsColumnManager::createColumn($field);

        session()->flash('message', 'Custom field created successfully.');

        return $this->redirect(route(config('custom-fields.route.name_prefix').'index'));
    }

    public function render()
    {
        $customizableTypes = config('custom-fields.customizable_types', []);

        // If no types configured, get all existing types from database
        if (empty($customizableTypes)) {
            $customizableTypes = Field::select('customizable_type')
                ->distinct()
                ->pluck('customizable_type', 'customizable_type')
                ->toArray();
        }

        return view('laravel-custom-fields::livewire.field-create', [
            'fieldTypes' => Field::getFieldTypes(),
            'textInputTypes' => Field::getTextInputTypes(),
            'customizableTypes' => $customizableTypes,
        ]);
    }
}
