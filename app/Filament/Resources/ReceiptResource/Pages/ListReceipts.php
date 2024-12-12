<?php

namespace App\Filament\Resources\ReceiptResource\Pages;

use App\Filament\Resources\ReceiptResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReceipts extends ListRecords
{
    protected static string $resource = ReceiptResource::class;

    protected function getCard(): ?string
    {
        return request()->query('card_id');
    }


    protected function getHeaderActions(): array
    {
        $cardId = $this->getCard();
        return [
            Actions\Action::make('Back to Cards')
                ->label('Back to cards')
                ->icon('heroicon-o-arrow-left')
                ->color('primary')
                ->url(route('filament.admin.resources.cards.index')) // Example: Redirect to a custom route
                ->hidden(!$cardId)
                ->color('secondary'),
            Actions\CreateAction::make(),

        ];
    }
}
