<?php

namespace Skylence\LaravelCustomFields\Enums;

enum FieldType: string
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case SELECT = 'select';
    case RADIO = 'radio';
    case CHECKBOX = 'checkbox';
    case TOGGLE = 'toggle';
    case CHECKBOX_LIST = 'checkbox_list';
    case DATETIME = 'datetime';
    case EDITOR = 'editor';
    case MARKDOWN = 'markdown';
    case COLOR = 'color';

    public function label(): string
    {
        return match ($this) {
            self::TEXT => 'Text Input',
            self::TEXTAREA => 'Textarea',
            self::SELECT => 'Select Dropdown',
            self::RADIO => 'Radio Buttons',
            self::CHECKBOX => 'Checkbox',
            self::TOGGLE => 'Toggle',
            self::CHECKBOX_LIST => 'Checkbox List',
            self::DATETIME => 'Date & Time',
            self::EDITOR => 'Rich Text Editor',
            self::MARKDOWN => 'Markdown Editor',
            self::COLOR => 'Color Picker',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->label()])
            ->toArray();
    }

    public function hasOptions(): bool
    {
        return in_array($this, [
            self::SELECT,
            self::RADIO,
            self::CHECKBOX_LIST,
        ]);
    }
}
