<?php

namespace Skylence\LaravelCustomFields;

use Livewire\Livewire;
use Skylence\LaravelCustomFields\Commands\LaravelCustomFieldsCommand;
use Skylence\LaravelCustomFields\Commands\SyncSystemFieldsCommand;
use Skylence\LaravelCustomFields\Http\Livewire\FieldActivityLog;
use Skylence\LaravelCustomFields\Http\Livewire\FieldCreate;
use Skylence\LaravelCustomFields\Http\Livewire\FieldEdit;
use Skylence\LaravelCustomFields\Http\Livewire\FieldIndex;
use Skylence\LaravelCustomFields\Models\Field;
use Skylence\LaravelCustomFields\Observers\FieldObserver;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
                'add_user_tracking_to_custom_fields_table',
                'create_custom_field_activity_log_table',
                'add_form_section_to_custom_fields_table',
                'add_system_and_api_fields_to_custom_fields_table',
            ])
            ->hasRoute('web')
            ->hasCommand(LaravelCustomFieldsCommand::class)
            ->hasCommand(SyncSystemFieldsCommand::class);
    }

    public function packageBooted(): void
    {
        // Register Livewire components
        Livewire::component('custom-fields.index', FieldIndex::class);
        Livewire::component('custom-fields.create', FieldCreate::class);
        Livewire::component('custom-fields.edit', FieldEdit::class);
        Livewire::component('custom-fields.activity-log', FieldActivityLog::class);

        // Register model observers
        Field::observe(FieldObserver::class);
    }
}
