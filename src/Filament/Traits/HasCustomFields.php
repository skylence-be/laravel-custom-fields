<?php

declare(strict_types=1);

namespace Xve\LaravelCustomFields\Filament\Traits;

use Xve\LaravelCustomFields\Filament\Forms\Components\CustomFields;
use Xve\LaravelCustomFields\Filament\Infolists\Components\CustomEntries;
use Xve\LaravelCustomFields\Filament\Tables\Columns\CustomColumns;
use Xve\LaravelCustomFields\Filament\Tables\Filters\CustomFilters;

trait HasCustomFields
{
    /**
     * Get the available form sections for custom fields.
     * Override this method in your resource to define available sections.
     *
     * @return array<string, string> Key => Label pairs
     */
    public static function getCustomFieldSections(): array
    {
        return [];
    }

    /**
     * Merge custom form fields into the base schema.
     *
     * @param  array<int, mixed>  $baseSchema
     * @param  array<int, string>  $include
     * @param  array<int, string>  $exclude
     * @return array<int, mixed>
     */
    protected static function mergeCustomFormFields(array $baseSchema, array $include = [], array $exclude = [], ?string $section = null): array
    {
        return array_merge($baseSchema, static::getCustomFormFields($include, $exclude, $section));
    }

    /**
     * Merge custom table columns into the base columns.
     *
     * @param  array<int, mixed>  $baseColumns
     * @param  array<int, string>  $include
     * @param  array<int, string>  $exclude
     * @return array<int, mixed>
     */
    protected static function mergeCustomTableColumns(array $baseColumns, array $include = [], array $exclude = []): array
    {
        return array_merge($baseColumns, static::getCustomTableColumns($include, $exclude));
    }

    /**
     * Merge custom table filters into the base filters.
     *
     * @param  array<int, mixed>  $baseFilters
     * @param  array<int, string>  $include
     * @param  array<int, string>  $exclude
     * @return array<int, mixed>
     */
    protected static function mergeCustomTableFilters(array $baseFilters, array $include = [], array $exclude = []): array
    {
        return array_merge($baseFilters, static::getCustomTableFilters($include, $exclude));
    }

    /**
     * Merge custom query builder constraints into the base constraints.
     *
     * @param  array<int, mixed>  $baseConstraints
     * @param  array<int, string>  $include
     * @param  array<int, string>  $exclude
     * @return array<int, mixed>
     */
    protected static function mergeCustomTableQueryBuilderConstraints(array $baseConstraints, array $include = [], array $exclude = []): array
    {
        return array_merge($baseConstraints, static::getTableQueryBuilderConstraints($include, $exclude));
    }

    /**
     * Merge custom infolist entries into the base schema.
     *
     * @param  array<int, mixed>  $baseSchema
     * @param  array<int, string>  $include
     * @param  array<int, string>  $exclude
     * @return array<int, mixed>
     */
    protected static function mergeCustomInfolistEntries(array $baseSchema, array $include = [], array $exclude = []): array
    {
        return array_merge($baseSchema, static::getCustomInfolistEntries($include, $exclude));
    }

    /**
     * Get custom form fields for this resource.
     *
     * @param  array<int, string>  $include
     * @param  array<int, string>  $exclude
     * @return array<int, mixed>
     */
    protected static function getCustomFormFields(array $include = [], array $exclude = [], ?string $section = null): array
    {
        return CustomFields::make(static::class)
            ->include($include)
            ->exclude($exclude)
            ->section($section)
            ->getSchema();
    }

    /**
     * Get custom table columns for this resource.
     *
     * @param  array<int, string>  $include
     * @param  array<int, string>  $exclude
     * @return array<int, mixed>
     */
    protected static function getCustomTableColumns(array $include = [], array $exclude = []): array
    {
        return CustomColumns::make(static::class)
            ->include($include)
            ->exclude($exclude)
            ->getColumns();
    }

    /**
     * Get custom table filters for this resource.
     *
     * @param  array<int, string>  $include
     * @param  array<int, string>  $exclude
     * @return array<int, mixed>
     */
    protected static function getCustomTableFilters(array $include = [], array $exclude = []): array
    {
        return CustomFilters::make(static::class)
            ->include($include)
            ->exclude($exclude)
            ->getFilters();
    }

    /**
     * Get custom query builder constraints for this resource.
     *
     * @param  array<int, string>  $include
     * @param  array<int, string>  $exclude
     * @return array<int, mixed>
     */
    protected static function getTableQueryBuilderConstraints(array $include = [], array $exclude = []): array
    {
        return CustomFilters::make(static::class)
            ->include($include)
            ->exclude($exclude)
            ->getQueryBuilderConstraints();
    }

    /**
     * Get custom infolist entries for this resource.
     *
     * @param  array<int, string>  $include
     * @param  array<int, string>  $exclude
     * @return array<int, mixed>
     */
    protected static function getCustomInfolistEntries(array $include = [], array $exclude = []): array
    {
        return CustomEntries::make(static::class)
            ->include($include)
            ->exclude($exclude)
            ->getSchema();
    }
}
