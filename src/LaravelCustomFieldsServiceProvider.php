<?php

namespace Xve\LaravelCustomFields;

use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Xve\LaravelCustomFields\Commands\LaravelCustomFieldsCommand;
use Xve\LaravelCustomFields\Http\Livewire\FieldActivityLog;
use Xve\LaravelCustomFields\Http\Livewire\FieldCreate;
use Xve\LaravelCustomFields\Http\Livewire\FieldEdit;
use Xve\LaravelCustomFields\Http\Livewire\FieldIndex;
use Xve\LaravelCustomFields\Models\Field;
use Xve\LaravelCustomFields\Observers\FieldObserver;

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
        Livewire::component('custom-fields.activity-log', FieldActivityLog::class);

        // Register model observers
        Field::observe(FieldObserver::class);
    }
}
