<?php

declare(strict_types=1);

namespace Xve\LaravelCustomFields\Filament\Resources;

use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Xve\LaravelCustomFields\Filament\Resources\FieldResource\Pages\CreateField;
use Xve\LaravelCustomFields\Filament\Resources\FieldResource\Pages\EditField;
use Xve\LaravelCustomFields\Filament\Resources\FieldResource\Pages\ListFields;
use Xve\LaravelCustomFields\Filament\Resources\FieldResource\Schemas\FieldForm;
use Xve\LaravelCustomFields\Filament\Resources\FieldResource\Tables\FieldsTable;
use Xve\LaravelCustomFields\Filament\Traits\HasCustomFields;
use Xve\LaravelCustomFields\Models\Field;

class FieldResource extends Resource
{
    protected static ?string $model = Field::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $modelLabel = 'Custom Field';

    protected static ?string $pluralModelLabel = 'Custom Fields';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 99;

    public static function form(Schema $schema): Schema
    {
        return FieldForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FieldsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFields::route('/'),
            'create' => CreateField::route('/create'),
            'edit' => EditField::route('/{record}/edit'),
        ];
    }

    /**
     * Get all resources that have the HasCustomFields trait.
     *
     * @return array<string, string>
     */
    public static function getCustomizableResources(): array
    {
        return collect(Filament::getResources())
            ->filter(fn ($resource) => in_array(HasCustomFields::class, class_uses_recursive($resource)))
            ->mapWithKeys(fn ($resource) => [
                $resource::getModel() => str($resource)->afterLast('\\')->beforeLast('Resource')->toString(),
            ])
            ->toArray();
    }

    /**
     * Get the resource class for a given model class.
     *
     * @return class-string|null
     */
    public static function getResourceForModel(?string $modelClass): ?string
    {
        if (! $modelClass) {
            return null;
        }

        return collect(Filament::getResources())
            ->filter(fn ($resource) => in_array(HasCustomFields::class, class_uses_recursive($resource)))
            ->first(fn ($resource) => $resource::getModel() === $modelClass);
    }

    /**
     * Get available form sections for a given model class.
     *
     * @return array<string, string>
     */
    public static function getSectionsForModel(?string $modelClass): array
    {
        $resource = static::getResourceForModel($modelClass);

        if (! $resource) {
            return [];
        }

        return $resource::getCustomFieldSections();
    }
}
