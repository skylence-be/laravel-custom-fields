<?php

declare(strict_types=1);

namespace Skylence\LaravelCustomFields\Filament\Resources\FieldResource\Pages;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Schema as DbSchema;
use Illuminate\Validation\Rules\Unique;
use Skylence\LaravelCustomFields\Enums\FieldType;
use Skylence\LaravelCustomFields\Enums\TextInputType;
use Skylence\LaravelCustomFields\Filament\Resources\FieldResource;
use Skylence\LaravelCustomFields\Services\FieldsColumnManager;

class CreateField extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = FieldResource::class;

    public function getMaxContentWidth(): Width
    {
        return Width::FourExtraLarge;
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Target Resource')
                ->icon('heroicon-o-cube')
                ->description('Select which entity this field belongs to')
                ->schema([
                    Section::make()
                        ->schema([
                            Select::make('customizable_type')
                                ->label('Resource')
                                ->required()
                                ->searchable()
                                ->native(false)
                                ->live()
                                ->options(fn () => FieldResource::getCustomizableResources())
                                ->helperText('Choose the resource where this custom field will be added. This cannot be changed later.'),
                        ]),
                ]),

            Step::make('Field Type')
                ->icon('heroicon-o-rectangle-stack')
                ->description('Choose the type of field')
                ->schema([
                    Section::make()
                        ->schema([
                            Select::make('type')
                                ->label('Field Type')
                                ->required()
                                ->searchable()
                                ->native(false)
                                ->live()
                                ->options(FieldType::class)
                                ->helperText('Select the type of input for this field. This cannot be changed later.'),
                            Select::make('input_type')
                                ->label('Input Type')
                                ->native(false)
                                ->visible(fn (Get $get): bool => $get('type') === FieldType::TEXT)
                                ->options(TextInputType::class)
                                ->default(TextInputType::TEXT)
                                ->helperText('Specify the HTML input type for text fields.'),
                            Toggle::make('is_multiselect')
                                ->label('Allow Multiple Selections')
                                ->visible(fn (Get $get): bool => $get('type') === FieldType::SELECT)
                                ->live()
                                ->helperText('Enable to allow users to select multiple options.'),
                        ])
                        ->columns(1),
                ]),

            Step::make('Field Details')
                ->icon('heroicon-o-pencil-square')
                ->description('Configure the field name and code')
                ->schema([
                    Section::make()
                        ->schema([
                            TextInput::make('name')
                                ->label('Display Label')
                                ->required()
                                ->maxLength(255)
                                ->helperText('The label shown to users in forms and tables.'),
                            TextInput::make('code')
                                ->label('Field Code')
                                ->required()
                                ->maxLength(255)
                                ->helperText('Unique identifier used as the database column name. Use lowercase letters, numbers, and underscores only.')
                                ->unique(
                                    table: 'custom_fields',
                                    modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('customizable_type', $get('customizable_type')),
                                    ignoreRecord: true,
                                )
                                ->notIn(function (Get $get) {
                                    if (! $get('customizable_type')) {
                                        return [];
                                    }

                                    $table = app($get('customizable_type'))->getTable();

                                    return DbSchema::getColumnListing($table);
                                })
                                ->rules([
                                    'regex:/^[a-z][a-z0-9_]*$/',
                                ]),
                        ])
                        ->columns(1),
                ]),

            Step::make('Options')
                ->icon('heroicon-o-list-bullet')
                ->description('Define available options')
                ->visible(fn (Get $get): bool => in_array($get('type'), [
                    FieldType::SELECT,
                    FieldType::CHECKBOX_LIST,
                    FieldType::RADIO,
                ]))
                ->schema([
                    Section::make()
                        ->schema([
                            Repeater::make('options')
                                ->label('Available Options')
                                ->live()
                                ->simple(
                                    TextInput::make('option')
                                        ->required()
                                        ->maxLength(255)
                                        ->placeholder('Enter option value'),
                                )
                                ->addActionLabel('Add Option')
                                ->reorderable()
                                ->reorderableWithButtons()
                                ->collapsible()
                                ->defaultItems(2)
                                ->helperText('Add the options that users can choose from.'),
                        ]),
                ]),

            Step::make('Default Value')
                ->icon('heroicon-o-clipboard-document-check')
                ->description('Set the default value')
                ->schema([
                    Section::make()
                        ->schema([
                            // Text input for text-based fields
                            TextInput::make('default_option')
                                ->label('Default Value')
                                ->helperText('Default value when creating new records')
                                ->visible(fn (Get $get): bool => in_array($get('type'), [
                                    FieldType::TEXT,
                                    FieldType::TEXTAREA,
                                    FieldType::EDITOR,
                                    FieldType::MARKDOWN,
                                    FieldType::COLOR,
                                    FieldType::DATETIME,
                                    null,
                                ]) || ! $get('type')),

                            // Toggle for boolean fields
                            Toggle::make('default_option')
                                ->label('Default Value')
                                ->helperText('Default state when creating new records')
                                ->visible(fn (Get $get): bool => in_array($get('type'), [
                                    FieldType::CHECKBOX,
                                    FieldType::TOGGLE,
                                ])),

                            // Single select for radio and single-select
                            Select::make('default_option')
                                ->label('Default Value')
                                ->helperText('Default selection when creating new records')
                                ->native(false)
                                ->searchable()
                                ->options(function (Get $get): array {
                                    $options = $get('options') ?? [];

                                    return collect($options)
                                        ->mapWithKeys(fn ($option) => [$option => $option])
                                        ->toArray();
                                })
                                ->visible(fn (Get $get): bool => $get('type') === FieldType::RADIO
                                    || ($get('type') === FieldType::SELECT && ! $get('is_multiselect'))),

                            // Multi-select for checkbox list and multi-select
                            CheckboxList::make('default_option')
                                ->label('Default Values')
                                ->helperText('Default selections when creating new records')
                                ->options(function (Get $get): array {
                                    $options = $get('options') ?? [];

                                    return collect($options)
                                        ->mapWithKeys(fn ($option) => [$option => $option])
                                        ->toArray();
                                })
                                ->visible(fn (Get $get): bool => $get('type') === FieldType::CHECKBOX_LIST
                                    || ($get('type') === FieldType::SELECT && $get('is_multiselect'))),
                        ]),
                ]),

            Step::make('Display Settings')
                ->icon('heroicon-o-table-cells')
                ->description('Configure how the field appears')
                ->schema([
                    Section::make()
                        ->schema([
                            Select::make('form_section')
                                ->label('Form Section')
                                ->native(false)
                                ->searchable()
                                ->options(function (Get $get): array {
                                    $sections = FieldResource::getSectionsForModel($get('customizable_type'));

                                    return empty($sections) ? [] : $sections;
                                })
                                ->placeholder('Default (Custom Fields)')
                                ->helperText('Select which section this field appears in for the primary model. Fields created on related models will use the default Custom Fields section.'),
                            Toggle::make('use_in_table')
                                ->label('Show in Table')
                                ->default(false)
                                ->helperText('Display this field as a column in the resource table view.'),
                            TextInput::make('sort')
                                ->label('Sort Order')
                                ->numeric()
                                ->default(0)
                                ->helperText('Lower numbers appear first. Fields with the same order are sorted alphabetically.'),
                        ])
                        ->columns(1),
                ]),
        ];
    }

    public function hasSkippableSteps(): bool
    {
        return false;
    }

    protected function getCreatedNotification(): Notification
    {
        return Notification::make()
            ->success()
            ->title('Custom field created')
            ->body('The custom field has been created and the database column has been added.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Convert empty arrays to null for default_option
        if (isset($data['default_option']) && is_array($data['default_option']) && empty($data['default_option'])) {
            $data['default_option'] = null;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var \Skylence\LaravelCustomFields\Models\Field $record */
        $record = $this->record;
        FieldsColumnManager::createColumn($record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
