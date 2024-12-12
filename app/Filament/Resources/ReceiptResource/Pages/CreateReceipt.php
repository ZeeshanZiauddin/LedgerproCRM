<?php
namespace App\Filament\Resources\ReceiptResource\Pages;

use App\Filament\Resources\ReceiptResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Redirect;

class CreateReceipt extends CreateRecord
{
    protected static string $resource = ReceiptResource::class;

    protected function getCard(): ?string
    {
        return request()->query('card_id');
    }

    // Add a back button action
    protected function getActions(): array
    {
        $cardId = $this->getCard();

        return [
            Actions\Action::make('backToReceipts')
                ->label('All receipts for the card')
                ->icon('heroicon-o-arrow-left')
                ->url(function () use ($cardId) {
                    return route('filament.admin.resources.receipts.index', [
                        'card_id' => $cardId
                    ]);
                })
                ->color('secondary')
                ->hidden(!$cardId),  // Hide if card_id is not available

            Actions\Action::make('backToCards')
                ->label('Back to Cards')
                ->icon('heroicon-o-arrow-left')
                ->url(route('filament.admin.resources.cards.index'))
                ->color('primary')
                ->hidden(!$cardId),  // Hide if card_id is available
        ];
    }
}
