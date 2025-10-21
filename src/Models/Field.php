<?php

namespace Xve\LaravelCustomFields\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Xve\LaravelCustomFields\Enums\FieldType;
use Xve\LaravelCustomFields\Enums\TextInputType;

class Field extends Model implements Sortable
{
    use SoftDeletes, SortableTrait;

    protected $table = 'custom_fields';

    protected function casts(): array
    {
        return [
            'type' => FieldType::class,
            'input_type' => TextInputType::class,
            'is_multiselect' => 'boolean',
            'options' => 'array',
            'form_settings' => 'array',
            'table_settings' => 'array',
            'infolist_settings' => 'array',
            'use_in_table' => 'boolean',
        ];
    }

    protected $fillable = [
        'code',
        'name',
        'type',
        'input_type',
        'is_multiselect',
        'datalist',
        'options',
        'default_option',
        'form_settings',
        'use_in_table',
        'table_settings',
        'infolist_settings',
        'sort',
        'customizable_type',
    ];

    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    /**
     * Get the table name for the customizable model.
     */
    public function getCustomizableTable(): string
    {
        $model = app($this->customizable_type);

        return $model->getTable();
    }

    /**
     * Get all available field types.
     */
    public static function getFieldTypes(): array
    {
        return FieldType::options();
    }

    /**
     * Get available input types for text fields.
     */
    public static function getTextInputTypes(): array
    {
        return TextInputType::options();
    }

    /**
     * Get all translations for this field.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(FieldTranslation::class);
    }

    /**
     * Get translation for a specific locale.
     */
    public function translate(?string $locale = null): ?FieldTranslation
    {
        $locale = $locale ?? app()->getLocale();

        return $this->translations()
            ->where('locale', $locale)
            ->first();
    }

    /**
     * Get translated name for the current or specified locale.
     */
    public function getTranslatedName(?string $locale = null): string
    {
        $translation = $this->translate($locale);

        return $translation?->name ?? $this->name;
    }

    /**
     * Get translated options for the current or specified locale.
     */
    public function getTranslatedOptions(?string $locale = null): ?array
    {
        $translation = $this->translate($locale);

        return $translation?->options ?? $this->options;
    }

    /**
     * Set or update translation for a specific locale.
     */
    public function setTranslation(string $locale, string $name, ?array $options = null): FieldTranslation
    {
        return $this->translations()->updateOrCreate(
            ['locale' => $locale],
            [
                'name' => $name,
                'options' => $options,
            ]
        );
    }

    /**
     * Get available locales from configuration.
     */
    public static function getAvailableLocales(): array
    {
        return config('app.locales', ['en']);
    }
}
