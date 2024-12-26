<?php

namespace App\Filament\Resources\InquiryResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class InquiryPassengerRelationManager extends RelationManager
{
    protected static string $relationship = 'passengers'; // Relationship name in the Inquiry model

    protected static ?string $recordTitleAttribute = 'id'; // Title of the record (can be customized)

    // Corrected: Removed `static` from the `form()` method
    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('from_city_id')
                    ->label('From City')
                    ->required(),
                Forms\Components\Select::make('from_country_id')
                    ->label('From Country')
                    ->required(),
                Forms\Components\Select::make('des_city_id')
                    ->label('Destination City')
                    ->required(),
                Forms\Components\Select::make('des_country_id')
                    ->label('Destination Country')
                    ->required(),
                Forms\Components\DatePicker::make('dep_date')
                    ->label('Departure Date')
                    ->required(),
                Forms\Components\DatePicker::make('return_date')
                    ->label('Return Date'),
                Forms\Components\TextInput::make('adults')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('child')
                    ->numeric(),
                Forms\Components\TextInput::make('infants')
                    ->numeric(),
                Forms\Components\TextInput::make('flight_type')
                    ->label('Flight Type')
                    ->required(),
                Forms\Components\TextInput::make('airline')
                    ->label('Preferred Airline'),
            ]);
    }

    // Corrected: Removed `static` from the `table()` method
    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('from_city_id')->label('From City'),
                Tables\Columns\TextColumn::make('des_city_id')->label('Destination City'),
                Tables\Columns\TextColumn::make('dep_date')->date(),
                Tables\Columns\TextColumn::make('return_date')->date(),
                Tables\Columns\TextColumn::make('adults'),
                Tables\Columns\TextColumn::make('flight_type'),
            ])
            ->filters([
                // Add any filters here
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
