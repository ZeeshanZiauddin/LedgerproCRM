<?php

namespace App\Filament\Resources\CardResource\Pages;

use App\Filament\Resources\CardResource;
use Awcodes\Recently\Concerns\HasRecentHistoryRecorder;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use JoseEspinal\RecordNavigation\Traits\HasRecordNavigation;
use Parallax\FilamentComments\Actions\CommentsAction;

class EditCard extends EditRecord
{
    use HasRecordNavigation;
    use HasRecentHistoryRecorder;
    protected static string $resource = CardResource::class;

    public $itinerary;

    protected function getHeaderActions(): array
    {
        $existingActions = [
            Actions\Action::make('reminders')
                ->icon('heroicon-s-bell')
                ->hiddenLabel()
                ->color('gray'),
            CommentsAction::make(),



        ];
        return array_merge($this->getNavigationActions(), $existingActions);


    }
}
