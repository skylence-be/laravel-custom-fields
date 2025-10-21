<?php

namespace Xve\LaravelCustomFields\Enums;

enum ValidationRule: string
{
    case REQUIRED = 'required';
    case NULLABLE = 'nullable';
    case EMAIL = 'email';
    case URL = 'url';
    case NUMERIC = 'numeric';
    case INTEGER = 'integer';
    case STRING = 'string';
    case BOOLEAN = 'boolean';
    case DATE = 'date';
    case ALPHA = 'alpha';
    case ALPHA_DASH = 'alpha_dash';
    case ALPHA_NUM = 'alpha_num';
    case JSON = 'json';
    case IP = 'ip';
    case IPV4 = 'ipv4';
    case IPV6 = 'ipv6';
    case CONFIRMED = 'confirmed';

    public function label(): string
    {
        return match ($this) {
            self::REQUIRED => 'Required',
            self::NULLABLE => 'Nullable',
            self::EMAIL => 'Email',
            self::URL => 'URL',
            self::NUMERIC => 'Numeric',
            self::INTEGER => 'Integer',
            self::STRING => 'String',
            self::BOOLEAN => 'Boolean',
            self::DATE => 'Date',
            self::ALPHA => 'Alphabetic Only',
            self::ALPHA_DASH => 'Alpha Dash (letters, numbers, dashes, underscores)',
            self::ALPHA_NUM => 'Alphanumeric',
            self::JSON => 'Valid JSON',
            self::IP => 'IP Address',
            self::IPV4 => 'IPv4 Address',
            self::IPV6 => 'IPv6 Address',
            self::CONFIRMED => 'Confirmed (field must have matching field_confirmation)',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $rule) => [$rule->value => $rule->label()])
            ->toArray();
    }

    /**
     * Get validation rules that require parameters.
     */
    public static function parametrizedRules(): array
    {
        return [
            'min' => 'Minimum Value/Length (e.g., min:1)',
            'max' => 'Maximum Value/Length (e.g., max:255)',
            'between' => 'Between (e.g., between:1,10)',
            'size' => 'Exact Size (e.g., size:10)',
            'regex' => 'Regular Expression (e.g., regex:/^[a-zA-Z]+$/)',
            'unique' => 'Unique in Table (e.g., unique:users,email)',
            'exists' => 'Exists in Table (e.g., exists:users,id)',
            'in' => 'In List (e.g., in:small,medium,large)',
            'not_in' => 'Not In List (e.g., not_in:admin,root)',
            'gt' => 'Greater Than Field (e.g., gt:start_date)',
            'gte' => 'Greater Than or Equal Field (e.g., gte:min_value)',
            'lt' => 'Less Than Field (e.g., lt:end_date)',
            'lte' => 'Less Than or Equal Field (e.g., lte:max_value)',
            'same' => 'Same As Field (e.g., same:password)',
            'different' => 'Different From Field (e.g., different:username)',
            'starts_with' => 'Starts With (e.g., starts_with:https://)',
            'ends_with' => 'Ends With (e.g., ends_with:.com)',
        ];
    }
}
