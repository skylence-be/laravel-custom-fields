<?php

namespace Skylence\LaravelCustomFields\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Skylence\LaravelCustomFields\Enums\FieldType;
use Skylence\LaravelCustomFields\Enums\TextInputType;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property FieldType $type
 * @property TextInputType|null $input_type
 * @property bool $is_multiselect
 * @property string|null $datalist
 * @property array|null $options
 * @property string|null $default_option
 * @property array|null $form_settings
 * @property bool $use_in_table
 * @property array|null $table_settings
 * @property array|null $infolist_settings
 * @property int $sort
 * @property string $customizable_type
 * @property string|null $form_section
 * @property bool $is_system
 * @property bool $is_required
 * @property bool $show_in_api
 * @property bool $api_required
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
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
            'is_system' => 'boolean',
            'is_required' => 'boolean',
            'show_in_api' => 'boolean',
            'api_required' => 'boolean',
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
        'form_section',
        'is_system',
        'is_required',
        'show_in_api',
        'api_required',
        'created_by',
        'updated_by',
    ];

    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($field) {
            if (Auth::check()) {
                $field->created_by = Auth::id();
                $field->updated_by = Auth::id();
            }
        });

        static::updating(function ($field) {
            if (Auth::check()) {
                $field->updated_by = Auth::id();
            }
        });
    }

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
     *
     * @return FieldTranslation|null
     */
    public function translate(?string $locale = null): ?FieldTranslation
    {
        $locale = $locale ?? app()->getLocale();

        /** @var FieldTranslation|null */
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

        return $translation->name ?? $this->name;
    }

    /**
     * Get translated options for the current or specified locale.
     */
    public function getTranslatedOptions(?string $locale = null): ?array
    {
        $translation = $this->translate($locale);

        return $translation->options ?? $this->options;
    }

    /**
     * Get option values as a simple array (for select/radio display).
     * Maintains order but strips the keys.
     */
    public function getOptionValues(): array
    {
        if (empty($this->options)) {
            return [];
        }

        return array_values($this->options);
    }

    /**
     * Get translated option values as a simple array.
     */
    public function getTranslatedOptionValues(?string $locale = null): array
    {
        $options = $this->getTranslatedOptions($locale);

        if (empty($options)) {
            return [];
        }

        return array_values($options);
    }

    /**
     * Set or update translation for a specific locale.
     *
     * @return FieldTranslation
     */
    public function setTranslation(string $locale, string $name, ?array $options = null): FieldTranslation
    {
        /** @var FieldTranslation */
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

    /**
     * Get the user who created this field.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    /**
     * Get the user who last updated this field.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'updated_by');
    }

    /**
     * Get all activity logs for this field.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(FieldActivityLog::class);
    }
}
