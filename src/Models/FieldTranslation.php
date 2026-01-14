<?php

namespace Skylence\LaravelCustomFields\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
