<?php

namespace App\Filament\Resources\InquiryResource\Pages;

use App\Filament\Resources\InquiryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\MaxWidth;

class CreateInquiry extends CreateRecord
{
    protected static string $resource = InquiryResource::class;


    protected function getModalWidth(): string
    {
        return '7xl';  // This will set the modal to full width
    }
    protected function getFormAttributes(): array
    {
        return [
            'class' => 'max-w-8xl', // Apply the custom class here
        ];
    }
}
