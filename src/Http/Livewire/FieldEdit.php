<?php

namespace Xve\LaravelCustomFields\Http\Livewire;

use Livewire\Component;
use Xve\LaravelCustomFields\Models\Field;
use Xve\LaravelCustomFields\Services\FieldsColumnManager;

class FieldEdit extends Component
{
    public Field $field;

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

    public bool $showDeleteModal = false;

    public bool $forceDelete = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:'.implode(',', array_keys(Field::getFieldTypes())),
            'input_type' => 'nullable|string',
            'is_multiselect' => 'boolean',
            'options' => 'nullable|array',
            'use_in_table' => 'boolean',
            'customizable_type' => 'required|string',
            'sort' => 'nullable|integer|min:0',
        ];
    }

    public function mount(Field $field): void
    {
        $this->field = $field;
        $this->name = $field->name;
        $this->code = $field->code;
        $this->type = $field->type;
        $this->input_type = $field->input_type ?? 'text';
        $this->is_multiselect = $field->is_multiselect;
        $this->options = $field->options ?? [];
        $this->use_in_table = $field->use_in_table;
        $this->customizable_type = $field->customizable_type;
        $this->sort = $field->sort ?? 0;
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

        $this->field->update([
            'name' => $this->name,
            'type' => $this->type,
            'input_type' => $this->type === 'text' ? $this->input_type : null,
            'is_multiselect' => $this->is_multiselect,
            'options' => in_array($this->type, ['select', 'radio', 'checkbox_list']) ? $this->options : null,
            'use_in_table' => $this->use_in_table,
            'customizable_type' => $this->customizable_type,
            'sort' => $this->sort,
        ]);

        // Update database column if needed
        FieldsColumnManager::updateColumn($this->field);

        session()->flash('message', 'Custom field updated successfully.');

        return $this->redirect(route(config('custom-fields.route.name_prefix').'index'));
    }

    public function confirmDelete(bool $force = false): void
    {
        $this->forceDelete = $force;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->forceDelete) {
            // Permanently delete and remove column
            $this->field->forceDelete();
            session()->flash('message', 'Field permanently deleted successfully.');
        } else {
            // Soft delete (keeps column)
            $this->field->delete();
            session()->flash('message', 'Field deleted successfully.');
        }

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

        return view('laravel-custom-fields::livewire.field-edit', [
            'fieldTypes' => Field::getFieldTypes(),
            'textInputTypes' => Field::getTextInputTypes(),
            'customizableTypes' => $customizableTypes,
        ]);
    }
}
