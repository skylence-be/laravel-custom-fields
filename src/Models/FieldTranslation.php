<?php

namespace Skylence\LaravelCustomFields\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $field_id
 * @property string $locale
 * @property string $name
 * @property array|null $options
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class FieldTranslation extends Model
{
    protected $table = 'custom_field_translations';

    protected $fillable = [
        'field_id',
        'locale',
        'name',
        'options',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
        ];
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }
}
