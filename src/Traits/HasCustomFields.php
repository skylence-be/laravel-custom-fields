<?php

namespace Xve\LaravelCustomFields\Traits;

use Exception;
use Xve\LaravelCustomFields\Models\Field;

trait HasCustomFields
{
    protected static mixed $customFillable;

    protected static mixed $customCasts;

    /**
     * Boot the trait.
     */
    protected static function bootHasCustomFields(): void
    {
        static::retrieved(fn ($model) => $model->loadCustomFields());

        static::creating(fn ($model) => $model->loadCustomFields());

        static::updating(fn ($model) => $model->loadCustomFields());
    }

    /**
     * Fill the model with an array of attributes.
     */
    public function fill(array $attributes): static
    {
        $this->loadCustomFields();

        return parent::fill($attributes);
    }

    /**
     * Load and merge custom fields into the model.
     */
    protected function loadCustomFields(): void
    {
        try {
            $customFields = $this->getCustomFields();

            $this->mergeFillable(self::$customFillable ??= $customFields->pluck('code')->toArray());

            $this->mergeCasts(self::$customCasts ??= $customFields->select('code', 'type', 'is_multiselect')->get());
        } catch (Exception $e) {
            // Silently fail if custom_fields table doesn't exist yet
        }
    }

    /**
     * Get all custom field codes for this model.
     */
    protected function getCustomFields()
    {
        return Field::where('customizable_type', get_class($this));
    }

    /**
     * Add custom fields to fillable.
     */
    public function mergeFillable(array $attributes): void
    {
        $this->fillable = array_unique(array_merge($this->fillable, $attributes));
    }

    /**
     * Add custom fields to casts.
     */
    public function mergeCasts($attributes): void
    {
        if (is_array($attributes)) {
            parent::mergeCasts($attributes);

            return;
        }

        foreach ($attributes as $attribute) {
            match ($attribute->type) {
                'select' => $this->casts[$attribute->code] = $attribute->is_multiselect ? 'array' : 'string',
                'checkbox' => $this->casts[$attribute->code] = 'boolean',
                'toggle' => $this->casts[$attribute->code] = 'boolean',
                'checkbox_list' => $this->casts[$attribute->code] = 'array',
                default => $this->casts[$attribute->code] = 'string',
            };
        }
    }

    /**
     * Get a custom field value by code.
     */
    public function getCustomField(string $code)
    {
        return $this->{$code} ?? null;
    }

    /**
     * Set a custom field value by code.
     */
    public function setCustomField(string $code, $value): void
    {
        $this->{$code} = $value;
    }

    /**
     * Get all custom field values as array.
     */
    public function getCustomFieldValues(): array
    {
        $fields = $this->getCustomFields()->pluck('code');
        $values = [];

        foreach ($fields as $code) {
            $values[$code] = $this->{$code};
        }

        return $values;
    }

    /**
     * Get custom field values for API response.
     * Only includes fields with show_in_api = true.
     */
    public function getCustomFieldsForApi(): array
    {
        $fields = $this->getCustomFields()
            ->where('show_in_api', true)
            ->get();

        $values = [];

        foreach ($fields as $field) {
            $values[$field->code] = $this->{$field->code};
        }

        return $values;
    }

    /**
     * Get validation rules for custom fields.
     * Only includes fields with is_required = true.
     *
     * @return array<string, string>
     */
    public static function getCustomFieldValidationRules(): array
    {
        $fields = Field::where('customizable_type', static::class)
            ->where('is_required', true)
            ->get();

        $rules = [];

        foreach ($fields as $field) {
            $rules[$field->code] = 'required';
        }

        return $rules;
    }

    /**
     * Get validation rules for custom fields in API requests.
     * Only includes fields with api_required = true.
     *
     * @return array<string, string>
     */
    public static function getCustomFieldApiValidationRules(): array
    {
        $fields = Field::where('customizable_type', static::class)
            ->where('show_in_api', true)
            ->where('api_required', true)
            ->get();

        $rules = [];

        foreach ($fields as $field) {
            $rules[$field->code] = 'required';
        }

        return $rules;
    }

    /**
     * Get all custom field definitions for this model.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Field>
     */
    public static function getCustomFieldDefinitions()
    {
        return Field::where('customizable_type', static::class)->get();
    }

    /**
     * Get custom field definitions visible in API.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Field>
     */
    public static function getApiCustomFieldDefinitions()
    {
        return Field::where('customizable_type', static::class)
            ->where('show_in_api', true)
            ->get();
    }
}
