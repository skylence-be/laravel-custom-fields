<?php

namespace Xve\LaravelCustomFields\Observers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Xve\LaravelCustomFields\Models\Field;
use Xve\LaravelCustomFields\Models\FieldActivityLog;
use Xve\LaravelCustomFields\Services\FieldsColumnManager;

class FieldObserver
{
    /**
     * Handle the Field "created" event.
     */
    public function created(Field $field): void
    {
        // Create database column
        FieldsColumnManager::createColumn($field);

        // Log the creation
        $this->logActivity($field, 'created', null, $field->getAttributes(), [
            'description' => "Created custom field '{$field->name}' ({$field->code})",
        ]);
    }

    /**
     * Handle the Field "updated" event.
     */
    public function updated(Field $field): void
    {
        $changes = $field->getChanges();
        $original = $field->getOriginal();

        // Remove timestamps and internal fields from logging
        $ignoredFields = ['updated_at', 'updated_by'];
        $changedAttributes = array_diff(array_keys($changes), $ignoredFields);

        if (empty($changedAttributes)) {
            return;
        }

        // Update database column if needed
        FieldsColumnManager::updateColumn($field);

        // Prepare old and new values
        $oldValues = [];
        $newValues = [];
        foreach ($changedAttributes as $attribute) {
            $oldValues[$attribute] = $original[$attribute] ?? null;
            $newValues[$attribute] = $changes[$attribute] ?? null;
        }

        // Log the update
        $this->logActivity($field, 'updated', $oldValues, $newValues, [
            'changed_attributes' => array_values($changedAttributes),
            'description' => "Updated custom field '{$field->name}'",
        ]);
    }

    /**
     * Handle the Field "deleted" event (soft delete).
     */
    public function deleted(Field $field): void
    {
        // Log the soft deletion
        $this->logActivity($field, 'deleted', null, null, [
            'description' => "Soft deleted custom field '{$field->name}' ({$field->code})",
        ]);
    }

    /**
     * Handle the Field "restored" event.
     */
    public function restored(Field $field): void
    {
        // Log the restoration
        $this->logActivity($field, 'restored', null, null, [
            'description' => "Restored custom field '{$field->name}' ({$field->code})",
        ]);
    }

    /**
     * Handle the Field "force deleted" event.
     */
    public function forceDeleted(Field $field): void
    {
        // Delete database column
        FieldsColumnManager::deleteColumn($field);

        // Log the permanent deletion
        $this->logActivity($field, 'force_deleted', null, null, [
            'description' => "Permanently deleted custom field '{$field->name}' ({$field->code})",
        ]);
    }

    /**
     * Log an activity.
     */
    protected function logActivity(
        Field $field,
        string $event,
        ?array $oldValues = null,
        ?array $newValues = null,
        array $additional = []
    ): void {
        $data = array_merge([
            'field_id' => $field->id,
            'user_id' => Auth::id(),
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ], $additional);

        FieldActivityLog::create($data);
    }
}
