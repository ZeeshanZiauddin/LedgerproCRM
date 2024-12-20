<?php

namespace App\Filament\Resources\CardResource\Pages;

use App\Filament\Resources\CardResource;
use Awcodes\Recently\Concerns\HasRecentHistoryRecorder;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Howdu\FilamentRecordSwitcher\Filament\Concerns\HasRecordSwitcher;
use JoseEspinal\RecordNavigation\Traits\HasRecordNavigation;
use Parallax\FilamentComments\Actions\CommentsAction;

class EditCard extends EditRecord
{
    use HasRecordNavigation;
    use HasRecordSwitcher;
    use HasRecentHistoryRecorder;
    protected static string $resource = CardResource::class;

    protected function getHeaderActions(): array
    {
        $existingActions = [

            Actions\Action::make('reminders')
                ->icon('heroicon-s-bell')
                ->hiddenLabel()
                ->color('gray'),
            CommentsAction::make(),
            Actions\Action::make('create_receipt')
                ->label('Create Receipt')
                ->icon('heroicon-s-document-plus')
                ->url(function ($record) {
                    return route('filament.admin.resources.receipts.create', ['card_id' => $record->id]);
                })
                ->color('primary'),

        ];
        return array_merge($this->getNavigationActions(), $existingActions);


    }

}
