<?php

namespace App\Filament\Resources\ReceiptResource\Pages;

use App\Filament\Resources\ReceiptResource;
use Awcodes\Recently\Concerns\HasRecentHistoryRecorder;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Howdu\FilamentRecordSwitcher\Filament\Concerns\HasRecordSwitcher;
use JoseEspinal\RecordNavigation\Traits\HasRecordNavigation;

class EditReceipt extends EditRecord
{
    use HasRecordNavigation;
    use HasRecordSwitcher;
    use HasRecentHistoryRecorder;

    protected static string $resource = ReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return array_merge(parent::getActions(), $this->getNavigationActions());
    }
}
