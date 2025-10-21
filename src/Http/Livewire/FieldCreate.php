<?php

namespace Xve\LaravelCustomFields\Http\Livewire;

use Livewire\Component;
use Xve\LaravelCustomFields\Livewire\Forms\FieldForm;
use Xve\LaravelCustomFields\Models\Field;
use Xve\LaravelCustomFields\Services\FieldsColumnManager;

class FieldCreate extends Component
{
    public FieldForm $form;

    public string $newOption = '';

    public function mount(): void
    {
        $this->form->sort = Field::max('sort') + 1 ?? 0;
    }

    public function updatedFormName(): void
    {
        // Auto-generate code from name if code is empty
        if (empty($this->form->code) && ! empty($this->form->name)) {
            $this->form->code = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $this->form->name));
        }
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
        // Check if code conflicts with existing database columns
        if (! FieldsColumnManager::canCreateColumn($this->form->code, $this->form->customizable_type)) {
            $this->addError('form.code', 'This code conflicts with an existing database column.');

            return;
        }

        $field = $this->form->store();

        // Create database column
        FieldsColumnManager::createColumn($field);

        session()->flash('message', 'Custom field created successfully.');

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

        return view('laravel-custom-fields::livewire.field-create', [
            'fieldTypes' => Field::getFieldTypes(),
            'textInputTypes' => Field::getTextInputTypes(),
            'customizableTypes' => $customizableTypes,
        ]);
    }
}
