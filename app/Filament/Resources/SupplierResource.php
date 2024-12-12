<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Other Resources';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->unique(Supplier::class, 'code')
                    ->disabled() // Read-only, auto-generated
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('email')->email()->nullable(),
                Forms\Components\TextInput::make('phone_no')->tel()->nullable(),
                Forms\Components\TextInput::make('address')->nullable()->maxLength(255),
                Forms\Components\TextInput::make('fax_no')->nullable()->maxLength(255),
                Forms\Components\TextInput::make('credit_limit')
                    ->numeric()
                    ->default(0),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->default('active')
                    ->required(),
                Forms\Components\Textarea::make('comment')->nullable()->columnSpanFull(),

            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('email')->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('phone')->sortable()->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('credit_limit')->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => fn($state): bool => $state === 'active',
                        'danger' => fn($state): bool => $state === 'inactive',
                    ])
                    ->formatStateUsing(fn($state): string => ucfirst($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('status')
                        ->label('Change Status')
                        ->icon('heroicon-o-arrow-path-rounded-square')
                        ->action(function ($record) {
                            // Toggle the status between active and inactive
                            $newStatus = $record->status === 'active' ? 'inactive' : 'active';
                            $record->update(['status' => $newStatus]);
                        })
                        ->modalHeading('Confirm Status Change')
                        ->modalSubheading('Are you sure you want to change the status of this Supplier?')
                        ->modalButton('Yes, Change Status')
                        ->successNotificationMessage('Status updated successfully.')
                        ->color('warning')
                        ->requiresConfirmation(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            // 'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
