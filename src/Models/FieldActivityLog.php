<?php

namespace Xve\LaravelCustomFields\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldActivityLog extends Model
{
    protected $table = 'custom_field_activity_log';

    protected $fillable = [
        'field_id',
        'user_id',
        'event',
        'old_values',
        'new_values',
        'changed_attributes',
        'description',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'changed_attributes' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the field that was modified.
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Get a human-readable description of the changes.
     */
    public function getChangesSummary(): string
    {
        if ($this->description) {
            return $this->description;
        }

        if (empty($this->changed_attributes)) {
            return 'No changes recorded';
        }

        $changes = [];
        foreach ($this->changed_attributes as $attribute) {
            $old = $this->old_values[$attribute] ?? 'null';
            $new = $this->new_values[$attribute] ?? 'null';

            // Convert arrays/objects to readable format
            if (is_array($old)) {
                $old = json_encode($old);
            }
            if (is_array($new)) {
                $new = json_encode($new);
            }

            $changes[] = ucfirst(str_replace('_', ' ', $attribute)).": {$old} → {$new}";
        }

        return implode(', ', $changes);
    }

    /**
     * Get the event label.
     */
    public function getEventLabel(): string
    {
        return match ($this->event) {
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'restored' => 'Restored',
            'force_deleted' => 'Permanently Deleted',
            default => ucfirst($this->event),
        };
    }

    /**
     * Get the event color class for UI.
     */
    public function getEventColorClass(): string
    {
        return match ($this->event) {
            'created' => 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
            'updated' => 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
            'deleted' => 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
            'restored' => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
            'force_deleted' => 'bg-red-200 dark:bg-red-800 text-red-900 dark:text-red-100',
            default => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
        };
    }
}
