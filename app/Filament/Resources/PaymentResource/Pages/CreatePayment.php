<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\PaymentPassenger;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     $data['passengers_ids'] = $data['passengers_ids'] ?? [];
    //     dd($data['passengers']);
    //     return $data;
    // }




}

