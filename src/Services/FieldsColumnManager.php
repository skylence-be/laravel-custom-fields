<?php

namespace Skylence\LaravelCustomFields\Services;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Skylence\LaravelCustomFields\Enums\FieldType;
use Skylence\LaravelCustomFields\Enums\TextInputType;
use Skylence\LaravelCustomFields\Models\Field;

class FieldsColumnManager
{
    /**
     * Create a new column for the field.
     */
    public static function createColumn(Field $field): void
    {
        $table = static::getTableName($field);

        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($field) {
            if (Schema::hasColumn($table->getTable(), $field->code)) {
                return;
            }

            static::addColumn($table, $field);
        });
    }

    /**
     * Update an existing column.
     */
    public static function updateColumn(Field $field): void
    {
        $table = static::getTableName($field);

        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($field) {
            if (! Schema::hasColumn($table->getTable(), $field->code)) {
                static::createColumn($field);

                return;
            }

            // For now, we don't modify columns to prevent data loss
            // In future, could add logic to change column types safely
        });
    }

    /**
     * Delete a column.
     */
    public static function deleteColumn(Field $field): void
    {
        $table = static::getTableName($field);

        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($field) {
            if (! Schema::hasColumn($table->getTable(), $field->code)) {
                return;
            }

            $table->dropColumn($field->code);
        });
    }

    /**
     * Add column to table based on field type.
     */
    protected static function addColumn(Blueprint $table, Field $field): void
    {
        $typeMethod = static::getColumnType($field);

        // Create the column
        $column = $table->$typeMethod($field->code);

        // Apply common column attributes
        $column->nullable();  // All custom fields are nullable by default
    }

    /**
     * Determine the appropriate column type for the field.
     */
    protected static function getColumnType(Field $field): string
    {
        return match ($field->type) {
            FieldType::TEXT => static::getTextColumnType($field),
            FieldType::TEXTAREA, FieldType::EDITOR, FieldType::MARKDOWN => 'text',
            FieldType::RADIO => 'string',
            FieldType::SELECT => $field->is_multiselect ? 'json' : 'string',
            FieldType::CHECKBOX, FieldType::TOGGLE => 'boolean',
            FieldType::CHECKBOX_LIST => 'json',
            FieldType::DATETIME => 'datetime',
            FieldType::COLOR => 'string',
            default => 'string'
        };
    }

    /**
     * Determine the appropriate column type for text fields.
     */
    protected static function getTextColumnType(Field $field): string
    {
        return match ($field->input_type) {
            TextInputType::INTEGER => 'integer',
            TextInputType::NUMERIC => 'decimal',
            default => 'string'
        };
    }

    /**
     * Get the table name for the customizable model.
     */
    protected static function getTableName(Field $field): string
    {
        $model = app($field->customizable_type);

        return $model->getTable();
    }

    /**
     * Ensure column exists for the field, creating it if needed.
     */
    public static function ensureColumnExists(Field $field): void
    {
        $table = static::getTableName($field);

        if (! Schema::hasTable($table)) {
            return;
        }

        if (! Schema::hasColumn($table, $field->code)) {
            static::createColumn($field);
        }
    }

    /**
     * Check if a column can be safely created (doesn't conflict with existing columns).
     */
    public static function canCreateColumn(string $code, string $customizableType): bool
    {
        try {
            $model = app($customizableType);
            $table = $model->getTable();

            if (! Schema::hasTable($table)) {
                return false;
            }

            return ! Schema::hasColumn($table, $code);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get all existing column names for a customizable type.
     */
    public static function getExistingColumns(string $customizableType): array
    {
        try {
            $model = app($customizableType);
            $table = $model->getTable();

            if (! Schema::hasTable($table)) {
                return [];
            }

            return Schema::getColumnListing($table);
        } catch (\Exception $e) {
            return [];
        }
    }
}
