<?php

namespace Skylence\LaravelCustomFields\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Skylence\LaravelCustomFields\Models\Field;

class FieldIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterType = '';

    public string $filterCustomizableType = '';

    public array $selectedFields = [];

    public bool $showDeleteModal = false;

    public ?int $fieldToDelete = null;

    public bool $forceDelete = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterCustomizableType' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCustomizableType(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $fieldId, bool $force = false): void
    {
        $this->fieldToDelete = $fieldId;
        $this->forceDelete = $force;
        $this->showDeleteModal = true;
    }

    public function deleteField(): void
    {
        if (! $this->fieldToDelete) {
            return;
        }

        $field = Field::withTrashed()->findOrFail($this->fieldToDelete);

        if ($this->forceDelete) {
            // Force delete - this will remove the column
            $field->forceDelete();
            session()->flash('message', 'Field permanently deleted successfully.');
        } else {
            // Soft delete - keeps the column
            $field->delete();
            session()->flash('message', 'Field deleted successfully.');
        }

        $this->reset(['fieldToDelete', 'showDeleteModal', 'forceDelete']);
    }

    public function restoreField(int $fieldId): void
    {
        $field = Field::withTrashed()->findOrFail($fieldId);
        $field->restore();

        session()->flash('message', 'Field restored successfully.');
    }

    public function bulkDelete(): void
    {
        if (empty($this->selectedFields)) {
            return;
        }

        Field::whereIn('id', $this->selectedFields)->delete();

        session()->flash('message', count($this->selectedFields).' fields deleted successfully.');
        $this->reset('selectedFields');
    }

    public function render()
    {
        $fields = Field::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('code', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterType, function ($query) {
                $query->where('type', $this->filterType);
            })
            ->when($this->filterCustomizableType, function ($query) {
                $query->where('customizable_type', $this->filterCustomizableType);
            })
            ->withTrashed()
            ->orderBy('sort')
            ->orderBy('created_at', 'desc')
            ->paginate(config('custom-fields.per_page', 15));

        $customizableTypes = Field::query()
            ->select('customizable_type')
            ->distinct()
            ->pluck('customizable_type', 'customizable_type')
            ->toArray();

        return view('laravel-custom-fields::livewire.field-index', [
            'fields' => $fields,
            'fieldTypes' => Field::getFieldTypes(),
            'customizableTypes' => $customizableTypes,
        ]);
    }
}
