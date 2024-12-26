<?php

namespace App\Filament\Resources\CardResource\RelationManagers;

use App\Filament\Resources\ReceiptResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Guava\FilamentModalRelationManagers\Concerns\CanBeEmbeddedInModals;


class ReceiptsRelationManager extends RelationManager
{
    use CanBeEmbeddedInModals;
    protected static string $relationship = 'receipts';

    public function form(Form $form): Form
    {
        return $form
            ->schema(ReceiptResource::getFormSchema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns(ReceiptResource::getTableSchema())
            ->filters([
                //
            ])
            ->headerActions([

                Tables\Actions\CreateAction::make('New')
                    ->icon('heroicon-s-plus')
                    ->modalWidth(MaxWidth::ThreeExtraLarge)

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }
}
