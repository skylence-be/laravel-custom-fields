<?php

namespace Xve\LaravelCustomFields\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Xve\LaravelCustomFields\Enums\FieldType;
use Xve\LaravelCustomFields\Enums\TextInputType;

class Field extends Model implements Sortable
{
    use SoftDeletes, SortableTrait;

    protected $table = 'custom_fields';

    protected function casts(): array
    {
        return [
            'type' => FieldType::class,
            'input_type' => TextInputType::class,
            'is_multiselect' => 'boolean',
            'options' => 'array',
            'form_settings' => 'array',
            'validation_rules' => 'array',
            'table_settings' => 'array',
            'infolist_settings' => 'array',
            'use_in_table' => 'boolean',
        ];
    }

    protected $fillable = [
        'code',
        'name',
        'type',
        'input_type',
        'is_multiselect',
        'datalist',
        'options',
        'form_settings',
        'validation_rules',
        'use_in_table',
        'table_settings',
        'infolist_settings',
        'sort',
        'customizable_type',
    ];

    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    /**
     * Get the table name for the customizable model.
     */
    public function getCustomizableTable(): string
    {
        $model = app($this->customizable_type);

        return $model->getTable();
    }

    /**
     * Get all available field types.
     */
    public static function getFieldTypes(): array
    {
        return FieldType::options();
    }

    /**
     * Get available input types for text fields.
     */
    public static function getTextInputTypes(): array
    {
        return TextInputType::options();
    }
}
