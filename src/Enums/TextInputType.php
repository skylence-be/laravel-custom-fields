<?php

namespace Skylence\LaravelCustomFields\Enums;

enum TextInputType: string
{
    case TEXT = 'text';
    case EMAIL = 'email';
    case NUMERIC = 'numeric';
    case INTEGER = 'integer';
    case PASSWORD = 'password';
    case TEL = 'tel';
    case URL = 'url';

    public function label(): string
    {
        return match ($this) {
            self::TEXT => 'Text',
            self::EMAIL => 'Email',
            self::NUMERIC => 'Numeric',
            self::INTEGER => 'Integer',
            self::PASSWORD => 'Password',
            self::TEL => 'Telephone',
            self::URL => 'URL',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->label()])
            ->toArray();
    }
}
