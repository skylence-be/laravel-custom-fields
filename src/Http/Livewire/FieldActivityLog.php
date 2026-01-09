<?php

namespace Xve\LaravelCustomFields\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Xve\LaravelCustomFields\Models\Field;

class FieldActivityLog extends Component
{
    use WithPagination;

    public Field $field;

    public $filterEvent = '';

    public $filterUserId = '';

    protected $queryString = [
        'filterEvent' => ['except' => ''],
        'filterUserId' => ['except' => ''],
    ];

    public function mount(Field $field): void
    {
        $this->field = $field->load(['creator', 'updater']);
    }

    public function updatingFilterEvent()
    {
        $this->resetPage();
    }

    public function updatingFilterUserId()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->filterEvent = '';
        $this->filterUserId = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = $this->field->activityLogs()
            ->with('user')
            ->orderBy('created_at', 'desc');

        if ($this->filterEvent) {
            $query->where('event', $this->filterEvent);
        }

        if ($this->filterUserId) {
            $query->where('user_id', $this->filterUserId);
        }

        $activityLogs = $query->paginate(20);

        // Get unique users for filter dropdown
        $users = $this->field->activityLogs()
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->sortBy('name');

        // Get available event types
        $eventTypes = [
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'restored' => 'Restored',
            'force_deleted' => 'Permanently Deleted',
        ];

        return view('laravel-custom-fields::livewire.field-activity-log', [
            'activityLogs' => $activityLogs,
            'users' => $users,
            'eventTypes' => $eventTypes,
        ]);
    }
}
