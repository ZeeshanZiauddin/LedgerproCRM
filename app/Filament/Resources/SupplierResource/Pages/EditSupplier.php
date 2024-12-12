<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use JoseEspinal\RecordNavigation\Traits\HasRecordNavigation;

class EditSupplier extends EditRecord
{
    use HasRecordNavigation;
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return array_merge(parent::getActions(), $this->getNavigationActions());
    }
}
