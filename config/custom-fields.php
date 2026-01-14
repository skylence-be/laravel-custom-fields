<?php

// config for Skylence/LaravelCustomFields
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
     * Enable default option selection for select/radio/checkbox fields.
     * When true, fields can have a pre-selected default option.
     */
    'enable_default_options' => true,
];
