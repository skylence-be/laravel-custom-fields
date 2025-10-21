<?php

namespace Xve\LaravelCustomFields;

use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Xve\LaravelCustomFields\Commands\LaravelCustomFieldsCommand;
use Xve\LaravelCustomFields\Http\Livewire\FieldCreate;
use Xve\LaravelCustomFields\Http\Livewire\FieldEdit;
use Xve\LaravelCustomFields\Http\Livewire\FieldIndex;
use Xve\LaravelCustomFields\Models\Field;
use Xve\LaravelCustomFields\Services\FieldsColumnManager;

class LaravelCustomFieldsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-custom-fields')
            ->hasConfigFile('custom-fields')
            ->hasViews('laravel-custom-fields')
            ->hasMigrations([
                'create_custom_fields_table',
                'create_custom_field_translations_table',
                'add_default_option_to_custom_fields_table',
            ])
            ->hasRoute('web')
            ->hasCommand(LaravelCustomFieldsCommand::class);
    }

    public function packageBooted(): void
    {
        // Register Livewire components
        Livewire::component('custom-fields.index', FieldIndex::class);
        Livewire::component('custom-fields.create', FieldCreate::class);
        Livewire::component('custom-fields.edit', FieldEdit::class);

        // Register model observers
        Field::observe(new class
        {
            public function created(Field $field): void
            {
                FieldsColumnManager::createColumn($field);
            }

            public function forceDeleted(Field $field): void
            {
                FieldsColumnManager::deleteColumn($field);
            }
        });
    }
}
