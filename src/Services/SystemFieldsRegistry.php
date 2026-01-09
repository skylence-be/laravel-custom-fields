<?php

declare(strict_types=1);

namespace Xve\LaravelCustomFields\Services;

use Xve\LaravelCustomFields\Enums\FieldType;
use Xve\LaravelCustomFields\Models\Field;

class SystemFieldsRegistry
{
    /**
     * Registered system fields grouped by model class.
     *
     * @var array<class-string, array<array<string, mixed>>>
     */
    protected static array $fields = [];

    /**
     * Register system fields for a model.
     *
     * @param  class-string  $modelClass  The model class (e.g., App\Models\Sales\Order::class)
     * @param  array<array<string, mixed>>  $fields  Array of field definitions
     *
     * Example:
     * ```php
     * SystemFieldsRegistry::register(Order::class, [
     *     [
     *         'code' => 'customer_reference',
     *         'name' => 'Customer Reference',
     *         'type' => FieldType::TEXT,
     *         'is_required' => false,
     *         'show_in_api' => true,
     *         'api_required' => false,
     *     ],
     * ]);
     * ```
     */
    public static function register(string $modelClass, array $fields): void
    {
        static::$fields[$modelClass] = array_merge(
            static::$fields[$modelClass] ?? [],
            $fields
        );
    }

    /**
     * Get all registered system fields.
     *
     * @return array<class-string, array<array<string, mixed>>>
     */
    public static function all(): array
    {
        return static::$fields;
    }

    /**
     * Get system fields for a specific model.
     *
     * @param  class-string  $modelClass
     * @return array<array<string, mixed>>
     */
    public static function for(string $modelClass): array
    {
        return static::$fields[$modelClass] ?? [];
    }

    /**
     * Clear all registered fields (useful for testing).
     */
    public static function clear(): void
    {
        static::$fields = [];
    }

    /**
     * Sync all registered system fields to the database.
     * Creates new fields and updates existing ones.
     * Does not delete fields that are no longer registered.
     *
     * @return array{created: int, updated: int}
     */
    public static function sync(): array
    {
        $created = 0;
        $updated = 0;

        foreach (static::$fields as $modelClass => $fields) {
            foreach ($fields as $fieldData) {
                $result = static::syncField($modelClass, $fieldData);
                if ($result === 'created') {
                    $created++;
                } elseif ($result === 'updated') {
                    $updated++;
                }
            }
        }

        return compact('created', 'updated');
    }

    /**
     * Sync a single field to the database.
     *
     * @param  class-string  $modelClass
     * @param  array<string, mixed>  $fieldData
     * @return string|null 'created', 'updated', or null if unchanged
     */
    protected static function syncField(string $modelClass, array $fieldData): ?string
    {
        $code = $fieldData['code'];
        $type = $fieldData['type'] instanceof FieldType
            ? $fieldData['type']
            : FieldType::from($fieldData['type']);

        $existing = Field::withTrashed()
            ->where('code', $code)
            ->where('customizable_type', $modelClass)
            ->first();

        $attributes = [
            'code' => $code,
            'name' => $fieldData['name'],
            'type' => $type,
            'input_type' => $fieldData['input_type'] ?? null,
            'is_multiselect' => $fieldData['is_multiselect'] ?? false,
            'options' => $fieldData['options'] ?? null,
            'default_option' => $fieldData['default_option'] ?? null,
            'form_settings' => $fieldData['form_settings'] ?? null,
            'use_in_table' => $fieldData['use_in_table'] ?? false,
            'table_settings' => $fieldData['table_settings'] ?? null,
            'infolist_settings' => $fieldData['infolist_settings'] ?? null,
            'form_section' => $fieldData['form_section'] ?? null,
            'customizable_type' => $modelClass,
            'is_system' => true,
            'is_required' => $fieldData['is_required'] ?? false,
            'show_in_api' => $fieldData['show_in_api'] ?? true,
            'api_required' => $fieldData['api_required'] ?? false,
        ];

        if ($existing) {
            // Only update non-user-editable fields
            // Users can modify: name, is_required, show_in_api, api_required, use_in_table, form_section, sort
            $systemAttributes = [
                'type' => $attributes['type'],
                'input_type' => $attributes['input_type'],
                'is_multiselect' => $attributes['is_multiselect'],
                'options' => $attributes['options'],
                'is_system' => true,
            ];

            // Restore if soft-deleted
            if ($existing->trashed()) {
                $existing->restore();
            }

            $existing->update($systemAttributes);

            // Create column if it doesn't exist
            FieldsColumnManager::ensureColumnExists($existing);

            return 'updated';
        }

        $field = Field::create($attributes);

        // Create the database column
        FieldsColumnManager::createColumn($field);

        return 'created';
    }
}
