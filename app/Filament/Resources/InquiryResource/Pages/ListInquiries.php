<?php

namespace App\Filament\Resources\InquiryResource\Pages;

use App\Filament\Resources\InquiryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

use AymanAlhattami\FilamentContextMenu\Traits\PageHasContextMenu;
use AymanAlhattami\FilamentContextMenu\Actions\{RefreshAction, GoBackAction, GoForwardAction};


class ListInquiries extends ListRecords
{
    use PageHasContextMenu;

    //

    public static function getContextMenuActions(): array
    {
        return [
            RefreshAction::make(),
            GoBackAction::make(),
        ];
    }
    protected static string $resource = InquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
