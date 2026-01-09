<?php

declare(strict_types=1);

namespace Xve\LaravelCustomFields\Filament\Resources\FieldResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Xve\LaravelCustomFields\Filament\Resources\FieldResource;
use Xve\LaravelCustomFields\Services\FieldsColumnManager;

class EditField extends EditRecord
{
    protected static string $resource = FieldResource::class;

    protected function getSavedNotification(): Notification
    {
        return Notification::make()
            ->success()
            ->title('Custom field updated')
            ->body('The custom field has been updated.');
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()->formId('form'),
            $this->getCancelFormAction(),
            DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected function afterSave(): void
    {
        FieldsColumnManager::updateColumn($this->record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
