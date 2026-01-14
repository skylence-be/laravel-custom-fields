<?php

declare(strict_types=1);

namespace Skylence\LaravelCustomFields\Filament\Tables\Columns;

use Filament\Support\Components\Component;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Collection;
use Skylence\LaravelCustomFields\Enums\FieldType;
use Skylence\LaravelCustomFields\Models\Field;

class CustomColumns extends Component
{
    protected array $include = [];

    protected array $exclude = [];

    protected ?string $resourceClass = null;

    final public function __construct(string $resource)
    {
        $this->resourceClass = $resource;
    }

    public static function make(string $resource): static
    {
        $static = app(static::class, ['resource' => $resource]);

        $static->configure();

        return $static;
    }

    public function include(array $fields): static
    {
        $this->include = $fields;

        return $this;
    }

    public function exclude(array $fields): static
    {
        $this->exclude = $fields;

        return $this;
    }

    public function getColumns(): array
    {
        $fields = $this->getFields();

        return $fields->map(function ($field) {
            return $this->createColumn($field);
        })->toArray();
    }

    protected function getFields(): Collection
    {
        $query = Field::query()
            ->where('customizable_type', $this->getResourceClass()::getModel())
            ->where('use_in_table', true);

        if (! empty($this->include)) {
            $query->whereIn('code', $this->include);
        }

        if (! empty($this->exclude)) {
            $query->whereNotIn('code', $this->exclude);
        }

        return $query->orderBy('sort')->get();
    }

    protected function getResourceClass(): string
    {
        return $this->resourceClass;
    }

    protected function createColumn(Field $field): Column
    {
        $columnClass = match ($field->type) {
            FieldType::TEXT, FieldType::TEXTAREA, FieldType::SELECT, FieldType::RADIO => TextColumn::class,
            FieldType::CHECKBOX, FieldType::TOGGLE => IconColumn::class,
            FieldType::CHECKBOX_LIST => TextColumn::class,
            FieldType::DATETIME => TextColumn::class,
            FieldType::EDITOR, FieldType::MARKDOWN => TextColumn::class,
            FieldType::COLOR => ColorColumn::class,
            default => TextColumn::class,
        };

        $column = $columnClass::make($field->code)
            ->label($field->getTranslatedName());

        if (in_array($field->type, [FieldType::CHECKBOX, FieldType::TOGGLE])) {
            $column->boolean();
        }

        if (! empty($field->table_settings)) {
            foreach ($field->table_settings as $setting) {
                $this->applySetting($column, $setting);
            }
        }

        return $column;
    }

    protected function applySetting(Column $column, array $setting): void
    {
        $name = $setting['setting'];
        $value = $setting['value'] ?? null;

        if (method_exists($column, $name)) {
            if ($value !== null) {
                if ($name === 'weight') {
                    $column->{$name}(constant(FontWeight::class.'::'.$value));
                } elseif ($name === 'size') {
                    $column->{$name}(constant(TextSize::class.'::'.$value));
                } else {
                    $column->{$name}($value);
                }
            } else {
                $column->{$name}();
            }
        }
    }
}
