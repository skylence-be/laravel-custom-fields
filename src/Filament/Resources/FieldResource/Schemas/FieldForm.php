<?php

declare(strict_types=1);

namespace Xve\LaravelCustomFields\Filament\Resources\FieldResource\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Schema as DbSchema;
use Xve\LaravelCustomFields\Enums\FieldType;
use Xve\LaravelCustomFields\Enums\TextInputType;
use Xve\LaravelCustomFields\Filament\Resources\FieldResource;

class FieldForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Field Details')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Label')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('The display label for this field'),
                                TextInput::make('code')
                                    ->label('Code')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabledOn('edit')
                                    ->helperText('Unique identifier (column name). Cannot be changed after creation.')
                                    ->unique(ignoreRecord: true)
                                    ->notIn(function (Get $get) {
                                        if ($get('id') || ! $get('customizable_type')) {
                                            return [];
                                        }

                                        $table = app($get('customizable_type'))->getTable();

                                        return DbSchema::getColumnListing($table);
                                    })
                                    ->rules([
                                        'regex:/^[a-z][a-z0-9_]*$/',
                                    ]),
                            ])
                            ->columns(2),

                        Section::make('Options')
                            ->visible(fn (Get $get): bool => in_array($get('type'), [
                                FieldType::SELECT,
                                FieldType::CHECKBOX_LIST,
                                FieldType::RADIO,
                            ]))
                            ->schema([
                                Repeater::make('options')
                                    ->hiddenLabel()
                                    ->live()
                                    ->simple(
                                        TextInput::make('option')
                                            ->required()
                                            ->maxLength(255),
                                    )
                                    ->addActionLabel('Add Option')
                                    ->reorderable()
                                    ->collapsible(),
                            ]),

                        Section::make('Table Settings')
                            ->schema([
                                Toggle::make('use_in_table')
                                    ->label('Show in Table')
                                    ->helperText('Display this field as a column in the table view')
                                    ->live(),
                            ])
                            ->collapsed(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Field Type')
                            ->schema([
                                Select::make('type')
                                    ->label('Type')
                                    ->required()
                                    ->disabledOn('edit')
                                    ->searchable()
                                    ->native(false)
                                    ->live()
                                    ->options(FieldType::class),
                                Select::make('input_type')
                                    ->label('Input Type')
                                    ->disabledOn('edit')
                                    ->native(false)
                                    ->visible(fn (Get $get): bool => $get('type') === FieldType::TEXT)
                                    ->options(TextInputType::class)
                                    ->default(TextInputType::TEXT),
                                Toggle::make('is_multiselect')
                                    ->label('Allow Multiple')
                                    ->visible(fn (Get $get): bool => $get('type') === FieldType::SELECT)
                                    ->live(),
                                TextInput::make('sort')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0),
                            ]),

                        Section::make('Target Resource')
                            ->schema([
                                Select::make('customizable_type')
                                    ->label('Resource')
                                    ->required()
                                    ->searchable()
                                    ->native(false)
                                    ->disabledOn('edit')
                                    ->live()
                                    ->options(fn () => FieldResource::getCustomizableResources())
                                    ->helperText('The resource this field belongs to'),
                                Select::make('form_section')
                                    ->label('Form Section')
                                    ->native(false)
                                    ->searchable()
                                    ->options(function (Get $get): array {
                                        $sections = FieldResource::getSectionsForModel($get('customizable_type'));

                                        return empty($sections) ? [] : $sections;
                                    })
                                    ->placeholder('Default (Custom Fields)')
                                    ->helperText('Select which section this field appears in'),
                            ]),

                        Section::make('Default Value')
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
                                    ]) || $get('type') === null),

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
                            ])
                            ->collapsed(),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
