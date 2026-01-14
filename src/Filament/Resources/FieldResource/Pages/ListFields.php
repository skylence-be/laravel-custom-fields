<?php

declare(strict_types=1);

namespace Skylence\LaravelCustomFields\Filament\Resources\FieldResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Skylence\LaravelCustomFields\Filament\Resources\FieldResource;

class ListFields extends ListRecords
{
    protected static string $resource = FieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
