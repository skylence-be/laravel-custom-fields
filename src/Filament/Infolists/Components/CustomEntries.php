<?php

declare(strict_types=1);

namespace Xve\LaravelCustomFields\Filament\Infolists\Components;

use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\Entry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\Collection;
use Xve\LaravelCustomFields\Enums\FieldType;
use Xve\LaravelCustomFields\Models\Field;

class CustomEntries extends Component
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

    protected function getResourceClass(): string
    {
        return $this->resourceClass;
    }

    public function getSchema(): array
    {
        $fields = $this->getFields();

        return $fields->map(function ($field) {
            return $this->createEntry($field);
        })->toArray();
    }

    protected function getFields(): Collection
    {
        $query = Field::query()
            ->where('customizable_type', $this->getResourceClass()::getModel());

        if (! empty($this->include)) {
            $query->whereIn('code', $this->include);
        }

        if (! empty($this->exclude)) {
            $query->whereNotIn('code', $this->exclude);
        }

        return $query->orderBy('sort')->get();
    }

    protected function createEntry(Field $field): Component
    {
        $entryClass = match ($field->type) {
            FieldType::TEXT, FieldType::TEXTAREA, FieldType::SELECT, FieldType::RADIO => TextEntry::class,
            FieldType::CHECKBOX, FieldType::TOGGLE => IconEntry::class,
            FieldType::CHECKBOX_LIST => TextEntry::class,
            FieldType::DATETIME => TextEntry::class,
            FieldType::EDITOR, FieldType::MARKDOWN => TextEntry::class,
            FieldType::COLOR => ColorEntry::class,
            default => TextEntry::class,
        };

        $entry = $entryClass::make($field->code)
            ->label($field->getTranslatedName());

        if (in_array($field->type, [FieldType::CHECKBOX, FieldType::TOGGLE])) {
            $entry->boolean();
        }

        if (! empty($field->infolist_settings)) {
            foreach ($field->infolist_settings as $setting) {
                $this->applySetting($entry, $setting);
            }
        }

        return $entry;
    }

    protected function applySetting(Entry $entry, array $setting): void
    {
        $name = $setting['setting'];
        $value = $setting['value'] ?? null;

        if (method_exists($entry, $name)) {
            if ($value !== null) {
                if ($name === 'weight') {
                    $entry->{$name}(constant(FontWeight::class.'::'.$value));
                } elseif ($name === 'size') {
                    $entry->{$name}(constant(TextSize::class.'::'.$value));
                } else {
                    $entry->{$name}($value);
                }
            } else {
                $entry->{$name}();
            }
        }
    }
}
