<?php

namespace App\Filament\Resources\CardResource\Pages;

use App\Filament\Resources\CardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCard extends EditRecord
{
    protected static string $resource = CardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_receipt')
                ->label('Create Receipt')
                ->icon('heroicon-s-document-plus')
                ->url(function ($record) {
                    return route('filament.admin.resources.receipts.create', ['card_id' => $record->id]);
                })
                ->color('primary'),
            Actions\DeleteAction::make(),

        ];
    }
}
