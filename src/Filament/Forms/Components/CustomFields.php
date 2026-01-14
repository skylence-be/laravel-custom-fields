<?php

declare(strict_types=1);

namespace Skylence\LaravelCustomFields\Filament\Forms\Components;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Collection;
use Skylence\LaravelCustomFields\Enums\FieldType;
use Skylence\LaravelCustomFields\Models\Field;

class CustomFields extends Component
{
    protected array $include = [];

    protected array $exclude = [];

    protected ?string $resourceClass = null;

    protected ?string $section = null;

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

    public function section(?string $section): static
    {
        $this->section = $section;

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
            return $this->createField($field);
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

        if ($this->section !== null) {
            $query->where('form_section', $this->section);
        }

        return $query->orderBy('sort')->get();
    }

    protected function createField(Field $field): Component
    {
        $componentClass = match ($field->type) {
            FieldType::TEXT => TextInput::class,
            FieldType::TEXTAREA => Textarea::class,
            FieldType::SELECT => Select::class,
            FieldType::CHECKBOX => Checkbox::class,
            FieldType::RADIO => Radio::class,
            FieldType::TOGGLE => Toggle::class,
            FieldType::CHECKBOX_LIST => CheckboxList::class,
            FieldType::DATETIME => DateTimePicker::class,
            FieldType::EDITOR => RichEditor::class,
            FieldType::MARKDOWN => MarkdownEditor::class,
            FieldType::COLOR => ColorPicker::class,
            default => TextInput::class,
        };

        $component = $componentClass::make($field->code)
            ->label($field->getTranslatedName());

        if (! empty($field->form_settings['validations'])) {
            foreach ($field->form_settings['validations'] as $validation) {
                $this->applyValidation($component, $validation);
            }
        }

        if (! empty($field->form_settings['settings'])) {
            foreach ($field->form_settings['settings'] as $setting) {
                $this->applySetting($component, $setting);
            }
        }

        if ($field->type === FieldType::TEXT && $field->input_type && $field->input_type->value !== 'text') {
            $inputMethod = $field->input_type->value;
            if (method_exists($component, $inputMethod)) {
                $component->{$inputMethod}();
            }
        }

        if (in_array($field->type, [FieldType::SELECT, FieldType::RADIO, FieldType::CHECKBOX_LIST]) && ! empty($field->options)) {
            $component->options(function () use ($field) {
                return collect($field->getTranslatedOptions())
                    ->mapWithKeys(fn ($option) => [$option => $option])
                    ->toArray();
            });

            if ($field->is_multiselect && method_exists($component, 'multiple')) {
                $component->multiple();
            }
        }

        if (in_array($field->type, [FieldType::SELECT, FieldType::DATETIME])) {
            $component->native(false);
        }

        if ($field->default_option !== null) {
            $component->default($field->default_option);
        }

        return $component;
    }

    protected function applyValidation(Component $component, array $validation): void
    {
        $rule = $validation['validation'];
        $field = $validation['field'] ?? null;
        $value = $validation['value'] ?? null;

        if (method_exists($component, $rule)) {
            if ($field) {
                $component->{$rule}($field, $value);
            } elseif ($value) {
                $component->{$rule}($value);
            } else {
                $component->{$rule}();
            }
        }
    }

    protected function applySetting(Component $component, array $setting): void
    {
        $name = $setting['setting'];
        $value = $setting['value'] ?? null;

        if (method_exists($component, $name)) {
            if ($value !== null) {
                $component->{$name}($value);
            } else {
                $component->{$name}();
            }
        }
    }
}
