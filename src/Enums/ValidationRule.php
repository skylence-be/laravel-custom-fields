<?php

namespace Skylence\LaravelCustomFields\Enums;

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
            'min' => 'Minimum Value/Length',
            'max' => 'Maximum Value/Length',
            'between' => 'Between (comma-separated)',
            'size' => 'Exact Size',
            'regex' => 'Regular Expression',
            'unique' => 'Unique in Table (table,column)',
            'exists' => 'Exists in Table (table,column)',
            'in' => 'In List (comma-separated)',
            'not_in' => 'Not In List (comma-separated)',
            'gt' => 'Greater Than Field',
            'gte' => 'Greater Than or Equal Field',
            'lt' => 'Less Than Field',
            'lte' => 'Less Than or Equal Field',
            'same' => 'Same As Field',
            'different' => 'Different From Field',
            'starts_with' => 'Starts With',
            'ends_with' => 'Ends With',
        ];
    }

    /**
     * Get all validation rules (simple + parametrized).
     */
    public static function allRules(): array
    {
        $simple = collect(self::cases())
            ->mapWithKeys(fn (self $rule) => [$rule->value => $rule->label()])
            ->toArray();

        $parametrized = array_map(
            fn ($label, $key) => $label.' (requires parameter)',
            self::parametrizedRules(),
            array_keys(self::parametrizedRules())
        );

        return array_merge(['simple' => $simple], ['parametrized' => $parametrized]);
    }

    /**
     * Check if a rule requires parameters.
     */
    public static function requiresParameter(string $rule): bool
    {
        return array_key_exists($rule, self::parametrizedRules());
    }

    /**
     * Convert a validation rule string to a human-readable label.
     */
    public static function toHumanReadable(string $rule): string
    {
        // Split rule and parameter (e.g., "min:5" -> ["min", "5"])
        $parts = explode(':', $rule, 2);
        $ruleName = $parts[0];
        $parameter = $parts[1] ?? null;

        // Try to get label from simple rules
        $simpleRules = self::options();
        if (isset($simpleRules[$ruleName])) {
            return $simpleRules[$ruleName];
        }

        // Try to get label from parametrized rules
        $parametrizedRules = self::parametrizedRules();
        if (isset($parametrizedRules[$ruleName]) && $parameter !== null) {
            $label = $parametrizedRules[$ruleName];

            // Create more specific labels based on the rule type
            return match ($ruleName) {
                'min' => "Minimum: {$parameter}",
                'max' => "Maximum: {$parameter}",
                'between' => "Between: {$parameter}",
                'size' => "Size: {$parameter}",
                'regex' => "Pattern: {$parameter}",
                'unique' => "Unique in: {$parameter}",
                'exists' => "Must exist in: {$parameter}",
                'in' => "Must be one of: {$parameter}",
                'not_in' => "Must not be one of: {$parameter}",
                'gt' => "Greater than: {$parameter}",
                'gte' => "Greater than or equal to: {$parameter}",
                'lt' => "Less than: {$parameter}",
                'lte' => "Less than or equal to: {$parameter}",
                'same' => "Same as: {$parameter}",
                'different' => "Different from: {$parameter}",
                'starts_with' => "Starts with: {$parameter}",
                'ends_with' => "Ends with: {$parameter}",
                default => "{$label}: {$parameter}",
            };
        }

        // Return the original rule if we can't find a label
        return $rule;
    }
}
