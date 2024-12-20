<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReceiptResource\Pages;
use App\Models\Card;
use App\Models\Receipt;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontFamily;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReceiptResource extends Resource
{
    protected static ?string $model = Receipt::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make(
                    [
                        Forms\Components\TextInput::make('name')
                            ->default(fn() => ReceiptResource::generateName())
                            ->required()
                            ->label('Reciept No.')
                            ->disabled()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('created_at')
                            ->displayFormat('Y-m-d') // Save format as "Y-m-d"
                            ->label('Date')
                            ->default(fn($get) => $get('created_at') ? Carbon::parse($get('created_at'))->format('Y-m-d') : now()->format('Y-m-d')) // Get date from created_at
                            ->disabled(), // Make it readonly

                        Forms\Components\TextInput::make('year')
                            ->default(fn($get) => $get('created_at') ? Carbon::parse($get('created_at'))->year : Carbon::now()->year) // Get year from created_at
                            ->disabled(), // Make it readonly
                        Forms\Components\Select::make('user_id')
                            ->label('Issued By')
                            ->default(fn() => auth()->id())
                            ->preload()
                            ->relationship('user', titleAttribute: 'name')
                            ->searchable(),
                    ]
                )->columns(4)
                    ->columnSpanFull(),
                Forms\Components\Select::make('card_id')
                    ->label('Card')
                    ->relationship('card', 'card_name')
                    ->searchable()
                    ->preload()
                    ->default(fn() => request()->query('card_id')),
                Forms\Components\Select::make('customer_id')
                    ->searchable()
                    ->label('Customer')
                    ->preload()
                    ->relationship('customer', 'name')
                    ->afterStateHydrated(function ($get, $set) {
                        $cardId = $get('card_id');
                        $card = Card::find($cardId);

                        if ($card) {
                            $set('customer_id', $card->customer_id);
                        }
                    }),

                Forms\Components\TextInput::make('modified_by')
                    ->maxLength(255),
                Forms\Components\TextInput::make('bank_no')
                    ->maxLength(255),
                Forms\Components\TextInput::make('dc_cc')
                    ->maxLength(255),
                Forms\Components\TextInput::make('total')
                    ->default(0)
                    ->numeric(),
                Forms\Components\TextInput::make('changes')
                    ->default(0)
                    ->numeric(),
                Forms\Components\TextInput::make('recon_acc')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('bank_date')
                    ->native(false)
                    ->displayFormat(' j M y')
                    ->label('Date')
                    ->nullable(),
            ])
            ->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('#')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('user.name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('card.card_name')->sortable(),
                Tables\Columns\TextColumn::make('customer.name')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('date')->date('j M y')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('issued_by')->searchable(),
                Tables\Columns\TextColumn::make('total')->sortable(),
            ])
            ->filters([
                SelectFilter::make('user.name') // Create a filter for user
                    ->label('User') // Set the filter label
                    ->options(function () {
                        return \App\Models\User::all()->pluck('name', 'id'); // Fetch all users and use their name and id as options
                    }),
                SelectFilter::make('card_id')
                    ->label('Card')
                    ->options(\App\Models\Card::pluck('card_name', 'id'))
                    ->default(function (Builder $query) {
                        $cardId = request()->query('card_id') ?? null;
                        if ($cardId) {
                            return $cardId;
                        }
                        return null;
                    })
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('name')
                    ->label('Receipt Name'),
                TextEntry::make('created_at')
                    ->since()
                    ->label('Created At'),
                TextEntry::make('user.name')
                    ->badge()
                    ->color('primary')
                    ->label('Issued by'),
                TextEntry::make('card.card_name')
                    ->copyable()
                    ->fontFamily(FontFamily::Serif)
                    ->label('Card ID'),
                TextEntry::make('Bank_no')->copyable()
                    ->fontFamily(FontFamily::Serif),
                TextEntry::make('bank_date'),
                TextEntry::make('dc_cc'),
                TextEntry::make('recon_acc'),
                TextEntry::make('total_amount')
                    ->label('Total changes')
                    ->html()
                    ->badge()->color('success')
                    ->default(function ($record) {
                        return dollar($record->total);
                    }),
                TextEntry::make('any_changes')
                    ->html()
                    ->badge()->color('success')
                    ->default(function ($record) {
                        return dollar($record->changes);
                    }),
            ])
            ->columns(4);
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
            'index' => Pages\ListReceipts::route('/'),
            'create' => Pages\CreateReceipt::route('/create'),
            'edit' => Pages\EditReceipt::route('/{record}/edit'),

        ];
    }
    public static function generateName(): string
    {
        $latest = Receipt::latest('id')->first(); // Get the latest id
        $latestNumber = $latest ? (int) substr($latest->name, 2) : 0; // Extract the number part and increment it
        $newNumber = str_pad($latestNumber + 1, 7, '0', STR_PAD_LEFT); // Increment and pad the number with leading zeros

        return 'RS' . $newNumber; // Prefix with "QR"
    }
}
