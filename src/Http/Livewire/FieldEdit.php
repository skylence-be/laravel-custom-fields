<?php

namespace Xve\LaravelCustomFields\Http\Livewire;

use Livewire\Component;
use Xve\LaravelCustomFields\Livewire\Forms\FieldForm;
use Xve\LaravelCustomFields\Models\Field;
use Xve\LaravelCustomFields\Services\FieldsColumnManager;

class FieldEdit extends Component
{
    public Field $field;

    public FieldForm $form;

    public string $newOption = '';

    public bool $showDeleteModal = false;

    public bool $forceDelete = false;

    public function mount(Field $field): void
    {
        $this->field = $field;
        $this->form->setField($field);
    }

    public function addOption(): void
    {
        if (! empty($this->newOption)) {
            $this->form->addOption($this->newOption);
            $this->newOption = '';
        }
    }

    public function removeOption(int $index): void
    {
        $this->form->removeOption($index);
    }

    public function save(): void
    {
        $this->form->update();

        // Update database column if needed
        FieldsColumnManager::updateColumn($this->field->fresh());

        session()->flash('message', 'Custom field updated successfully.');

        $this->redirect(route(config('custom-fields.route.name_prefix').'index'));
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

        $this->redirect(route(config('custom-fields.route.name_prefix').'index'));
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
