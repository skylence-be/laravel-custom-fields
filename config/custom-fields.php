<?php

// config for Xve/LaravelCustomFields
return [

    /**
     * Route configuration for custom fields management.
     */
    'route' => [
        'prefix' => 'admin/fields',
        'middleware' => ['web', 'auth'],
        'name_prefix' => 'custom-fields.',
    ],

    /**
     * Database table name for custom fields.
     */
    'table_name' => 'custom_fields',

    /**
     * Available field types.
     */
    'field_types' => [
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
    ],

    /**
     * Input types for text fields.
     */
    'text_input_types' => [
        'text' => 'Text',
        'email' => 'Email',
        'numeric' => 'Numeric',
        'integer' => 'Integer',
        'password' => 'Password',
        'tel' => 'Telephone',
        'url' => 'URL',
    ],

    /**
     * Models that can have custom fields.
     * Add your models here.
     */
    'customizable_types' => [
        // Example: App\Models\User::class => 'Users',
    ],

    /**
     * Pagination settings.
     */
    'per_page' => 15,

    /**
     * Enable soft deletes for custom fields.
     */
    'enable_soft_deletes' => true,

    /**
     * Views configuration.
     */
    'views' => [
        'layout' => 'components.layouts.app',
    ],
];
