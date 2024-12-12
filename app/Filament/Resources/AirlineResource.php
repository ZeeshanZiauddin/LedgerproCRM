<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AirlineResource\Pages;
use App\Models\Airline;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;

class AirlineResource extends Resource
{
    protected static ?string $model = Airline::class;

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationGroup = 'Other Resources';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->label('Airline Code')
                    ->numeric() // Ensure the input is numeric
                    ->minLength(3) // Minimum of 3 characters
                    ->maxLength(3) // Maximum of 3 characters
                    ->rule('regex:/^\d{3}$/') // Enforce exactly three digits
                    ->helperText('The code must be exactly 3 digits.'),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Airline Name'),

                Forms\Components\Textarea::make('comment')
                    ->nullable()
                    ->label('Comment'),

                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->default('active') // Default value for status
                    ->required()
                    ->label('Status'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Airline Code'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Airline Name'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'active' => 'success',
                        'inactive' => 'danger',
                    ])
                    ->label('Status'),

                Tables\Columns\TextColumn::make('comment')
                    ->limit(50)
                    ->label('Comment'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\BulkAction::make('updateStatus')
                    ->label('Change Status') // Label for bulk action
                    ->action(function ($records, array $data) {
                        // Update the status of selected records
                        foreach ($records as $record) {
                            $record->update(['status' => $data['status']]);
                        }
                    })
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->required(),
                    ]),
            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAirlines::route('/'),

        ];
    }
}
