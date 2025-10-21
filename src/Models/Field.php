<?php

namespace Xve\LaravelCustomFields\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Field extends Model implements Sortable
{
    use SoftDeletes, SortableTrait;

    protected $table = 'custom_fields';

    protected function casts(): array
    {
        return [
            'is_multiselect' => 'boolean',
            'options' => 'array',
            'form_settings' => 'array',
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
        return [
            'text' => 'Text Input',
            'textarea' => 'Textarea',
            'select' => 'Select Dropdown',
            'radio' => 'Radio Buttons',
            'checkbox' => 'Checkbox',
            'toggle' => 'Toggle',
            'checkbox_list' => 'Checkbox List',
            'datetime' => 'Date & Time',
            'editor' => 'Rich Text Editor',
            'markdown' => 'Markdown Editor',
            'color' => 'Color Picker',
        ];
    }

    /**
     * Get available input types for text fields.
     */
    public static function getTextInputTypes(): array
    {
        return [
            'text' => 'Text',
            'email' => 'Email',
            'numeric' => 'Numeric',
            'integer' => 'Integer',
            'password' => 'Password',
            'tel' => 'Telephone',
            'url' => 'URL',
        ];
    }
}
