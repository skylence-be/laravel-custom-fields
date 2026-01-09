<?php

declare(strict_types=1);

namespace Xve\LaravelCustomFields\Filament\Resources\FieldResource\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Xve\LaravelCustomFields\Enums\FieldType;
use Xve\LaravelCustomFields\Filament\Resources\FieldResource;
use Xve\LaravelCustomFields\Models\Field;
use Xve\LaravelCustomFields\Services\FieldsColumnManager;

class FieldsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label('Label')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('customizable_type')
                    ->label('Resource')
                    ->formatStateUsing(fn (string $state): string => str($state)->afterLast('\\')->toString())
                    ->sortable(),
                IconColumn::make('is_system')
                    ->label('System')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                IconColumn::make('is_required')
                    ->label('Required')
                    ->boolean(),
                IconColumn::make('show_in_api')
                    ->label('API')
                    ->boolean(),
                IconColumn::make('use_in_table')
                    ->label('In Table')
                    ->boolean(),
                TextColumn::make('sort')
                    ->label('Order')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(FieldType::class),
                SelectFilter::make('customizable_type')
                    ->label('Resource')
                    ->options(fn () => FieldResource::getCustomizableResources()),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->hidden(fn ($record) => $record->trashed()),
                    RestoreAction::make()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Field restored')
                                ->body('The custom field has been restored.')
                        ),
                    DeleteAction::make()
                        ->hidden(fn (Field $record): bool => $record->is_system)
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Field deleted')
                                ->body('The custom field has been deleted.')
                        ),
                    ForceDeleteAction::make()
                        ->hidden(fn (Field $record): bool => $record->is_system)
                        ->before(function (Field $record) {
                            FieldsColumnManager::deleteColumn($record);
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Field permanently deleted')
                                ->body('The custom field and its column have been permanently removed.')
                        ),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    RestoreBulkAction::make(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                FieldsColumnManager::deleteColumn($record);
                            }
                        }),
                ]),
            ])
            ->defaultSort('sort');
    }
}
