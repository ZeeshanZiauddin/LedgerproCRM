<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardResource\Pages;
use App\Models\Card;
use App\Models\Receipt;
use Filament\Actions\MountableAction;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontFamily;
use Filament\Tables;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Table;
use Filament\Forms\Components\Group;
use Guava\FilamentClusters\Forms\Cluster;
use Icetalker\FilamentTableRepeatableEntry\Infolists\Components\TableRepeatableEntry;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Database\Eloquent\Builder;

class CardResource extends Resource
{
    protected static ?string $model = Card::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Group for card details
                Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('card_name')
                            ->default(fn() => CardResource::generateCardName()) // Call the generateInquiryName method
                            ->disabled() // Disable the field to prevent manual editing
                            ->required()
                            ->label('Card No.')
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('user_id')
                            ->label('Owner')
                            ->default(fn() => auth()->user()->id)
                            ->relationship('user', 'name')->disabled(),

                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')  // Relationship to the Customer model, assuming it has a `name` field
                            ->nullable()
                            ->preload()
                            ->searchable()  // Make the field searchable
                            ->placeholder('Select a Customer'),

                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'name')  // Relationship to the Supplier model, assuming it has a `name` field
                            ->nullable()
                            ->preload()
                            ->searchable()  // Make the field searchable
                            ->placeholder('Select a Supplier'),
                        Forms\Components\Select::make('inquiry_id')
                            ->relationship('inquiry', 'inquiry_name')  // Relationship to the Supplier model, assuming it has a `name` field
                            ->nullable()
                            ->preload()
                            ->searchable()  // Make the field searchable
                            ->placeholder(placeholder: 'Inquiry ID'),
                    ])->columns(1)->columnSpan(1),

                // Group for contact details
                Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('contact_name')->nullable(),
                        Forms\Components\TextInput::make('contact_email')
                            ->nullable(),
                        Forms\Components\TextInput::make('contact_mobile')->nullable(),
                        Forms\Components\TextInput::make('contact_home_number')->nullable(),
                        Forms\Components\Textarea::make('contact_address')->nullable(),
                    ])->columns(1)->columnSpan(1),
                Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('sales_price')
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $netCost = $get('net_cost');
                                $tax = $get('tax');
                                if ($state > 1 && $netCost > 1 && $tax > 1) {
                                    $set('margin', $state - ($netCost + $tax));
                                } else {
                                    $set('margin', 0);
                                }
                            }),

                        Forms\Components\TextInput::make('net_cost')
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $salesPrice = $get('sales_price');
                                $tax = $get('tax');
                                if ($salesPrice > 1 && $state > 1 && $tax > 1) {
                                    $set('margin', $salesPrice - ($state + $tax));
                                } else {
                                    $set('margin', 0);
                                }
                            }),

                        Forms\Components\TextInput::make('tax')
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $salesPrice = $get('sales_price');
                                $netCost = $get('net_cost');
                                if ($salesPrice > 1 && $netCost > 1 && $state > 1) {
                                    $set('margin', $salesPrice - ($netCost + $state));
                                } else {
                                    $set('margin', 0);
                                }
                            }),

                        Forms\Components\TextInput::make('margin')
                            ->numeric()
                            ->default(0)
                            ->readOnly(),

                        Forms\Components\TextInput::make('total_paid')
                            ->readOnly()
                            ->reactive()
                            ->default(0)
                            ->afterStateHydrated(function ($set, $record) {
                                if ($record) {
                                    $set('total_paid', $record->receipts()->sum('total'));
                                }
                            }),

                    ])
                    ->columns(1)
                    ->columnSpan(1),

                // Repeater for passengers
                TableRepeater::make('passengers')
                    ->relationship('passengers')
                    ->schema([

                        Forms\Components\Select::make('ticket_1')
                            ->label('Tkt no.')
                            ->placeholder(placeholder: '000')
                            ->relationship('airline', 'code')
                            ->nullable(),
                        Forms\Components\TextInput::make('ticket_2')
                            ->label(false)
                            ->placeholder('0000000000')
                            ->maxLength(10)  // Limit to 10 characters
                            ->minLength(10)  // Ensure a minimum of 10 characters
                            ->nullable(),

                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\DatePicker::make('issue_date')
                            ->native(false)
                            ->displayFormat('d M y')
                            ->default(now())
                            ->placeholder('dd mm yy'),
                        Forms\Components\DatePicker::make('option_date')
                            ->native(false)
                            ->placeholder('dd mm yy')
                            ->displayFormat('d M y')
                            ->nullable(),
                        Forms\Components\TextInput::make('sale')
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $netCost = $get('cost');
                                $tax = $get('tax');
                                if ($state > 1 && $netCost > 1 && $tax > 1) {
                                    $set('margin', $state - ($netCost + $tax));
                                } else {
                                    $set('margin', 0);
                                }
                            }),

                        Forms\Components\TextInput::make('cost')
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $salesPrice = $get('sale');
                                $tax = $get('tax');
                                if ($salesPrice > 1 && $state > 1 && $tax > 1) {
                                    $set('margin', $salesPrice - ($state + $tax));
                                } else {
                                    $set('margin', 0);
                                }
                            }),

                        Forms\Components\TextInput::make('tax')
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $salesPrice = $get('sale');
                                $netCost = $get('cost');
                                if ($salesPrice > 1 && $netCost > 1 && $state > 1) {
                                    $set('margin', $salesPrice - ($netCost + $state));
                                } else {
                                    $set('margin', 0);
                                }
                            }),

                        Forms\Components\TextInput::make('margin')
                            ->numeric()
                            ->default(0)
                            ->disabled(),

                        Forms\Components\TextInput::make('pnr')
                            ->nullable(),
                    ])
                    ->columnSpanFull()
                    ->columns(2)
                    ->colStyles([
                        'ticket_1' => 'width: 80px;',
                        'ticket_2' => 'width: 140px;',
                        'issue_date' => 'width: 110px;',
                        'option_date' => 'width: 110px;',
                        'sale' => 'width: 80px;',
                        'cost' => 'width: 80px;',
                        'tax' => 'width: 80px;',
                        'margin' => 'width: 80px;',
                    ])
                    ->required(),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('card_name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('user.name')->sortable()->searchable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->sortable()
                    ->searchable()
                    ->tooltip(function ($record) {
                        $customer = $record->customer;
                        return $customer ? "Email: {$customer->email}\nPhone: {$customer->phone}\nAddress: {$customer->address}" : 'No customer details available';
                    }),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->sortable()
                    ->searchable()
                    ->tooltip(function ($record) {
                        $supplier = $record->supplier;
                        return $supplier ? "Email: {$supplier->email}\nPhone: {$supplier->phone}\nAddress: {$supplier->address}" : 'No supplier details available';
                    }),

                Tables\Columns\TextColumn::make('sales_price')->sortable(),
                Tables\Columns\TextColumn::make('margin')->sortable(),
                Tables\Columns\BadgeColumn::make('Payment')
                    ->sortable()
                    ->html()
                    ->getStateUsing(function ($record) {
                        $res = $record->getReceiptsStatus();
                        return [
                            'lable' => $res['status']['lable'],
                        ];
                    })
                    ->color(
                        function ($record) {
                            $res = $record->getReceiptsStatus('status');
                            return $res['color'];
                        }
                    ),

                Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver(),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\ActionGroup::make([

                    Tables\Actions\Action::make('create_receipt')
                        ->label('Create Receipt')
                        ->icon('heroicon-s-document-plus')
                        ->url(function ($record) {
                            return route('filament.admin.resources.receipts.create', ['card_id' => $record->id]);
                        })
                        ->color('primary'),
                    Tables\Actions\Action::make('receipts')
                        ->label('Show all Receipts')
                        ->icon('heroicon-s-document-currency-pound')
                        ->url(function ($record) {
                            return route('filament.admin.resources.receipts.index', ['card_id' => $record->id]);
                        })
                        ->color('success'),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('user')
                    ->searchable()
                    ->preload(),
                Tables\Filters\QueryBuilder::make()
                    ->constraints([
                        DateConstraint::make('created_at')

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
                Infolists\Components\TextEntry::make('card_name')
                    ->label('Card Name'),
                Infolists\Components\TextEntry::make('created_at')
                    ->since()
                    ->label('Date'),
                Infolists\Components\TextEntry::make('user.name')
                    ->label('Issued by'),
                Infolists\Components\TextEntry::make('inquiry.name')
                    ->label('Inquiry No.'),
                Infolists\Components\TextEntry::make('contact_name'),
                Infolists\Components\TextEntry::make('contact_email')->copyable(),
                Infolists\Components\TextEntry::make('contact_mobile')->copyable(),
                Infolists\Components\TextEntry::make('contact_address')->copyable(),
                Infolists\Components\TextEntry::make('sales_price'),
                Infolists\Components\TextEntry::make('net_cost'),
                Infolists\Components\TextEntry::make('tax'),
                Infolists\Components\TextEntry::make('payment_status')
                    ->badge()
                    ->label('Payment Status')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $res = $record->getReceiptsStatus();
                        return [
                            'total' => dollar($res['total']),
                            'lable' => $res['status']['lable'],
                        ];
                    })
                    ->color(
                        function ($record): string {
                            $res = $record->getReceiptsStatus('status');
                            return $res['color'] ?? 'primary';
                        }
                    ),

                TableRepeatableEntry::make('passengers')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Passenger name'),
                        Infolists\Components\TextEntry::make('ticket')
                            ->label('Ticket No.')
                            ->fontFamily(FontFamily::Serif)
                            ->default(
                                function ($record) {
                                    return $record->ticket_1 . ' ' . $record->ticket_2;
                                }
                            )
                            ->copyable(),
                        Infolists\Components\TextEntry::make('issue_date')
                            ->date()
                            ->label('Issue date'),
                        Infolists\Components\TextEntry::make('option_date')
                            ->date()
                            ->label('Option Date'),
                        Infolists\Components\TextEntry::make('pnr')
                            ->label('PNR')
                            ->copyable()
                            ->fontFamily(FontFamily::Mono)
                    ])
                    ->columnSpanFull(),
                TableRepeatableEntry::make('receipts')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->fontFamily(FontFamily::Serif)
                            ->label('#'),
                        Infolists\Components\TextEntry::make('customer.name')
                            ->default('N/A')
                            ->label('Customer'),
                        Infolists\Components\TextEntry::make('bank_no')
                            ->default('Not Entered')
                            ->label('Bank')
                            ->fontFamily(FontFamily::Serif),
                        Infolists\Components\TextEntry::make(name: 'totals')
                            ->badge()
                            ->label('Amount')
                            ->html()
                            ->default(
                                function ($record) {
                                    return dollar($record->total);
                                }
                            )
                            ->color('success'),
                        Infolists\Components\TextEntry::make('user.name')->label('issued_by'),
                        Infolists\Components\TextEntry::make('created_at')->label('Created At')
                            ->date(),
                    ])
                    ->columnSpanFull(),
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
            'index' => Pages\ListCards::route('/'),
            // 'create' => Pages\CreateCard::route('/create'),
            // 'edit' => Pages\EditCard::route('/{record}/edit'),
        ];
    }
    public static function generateCardName(): string
    {
        $latestInquiry = Card::latest('id')->first(); // Get the latest inquiry
        $latestNumber = $latestInquiry ? (int) substr($latestInquiry->card_name, 2) : 0; // Extract the number part and increment it
        $newNumber = str_pad($latestNumber + 1, 7, '0', STR_PAD_LEFT); // Increment and pad the number with leading zeros

        return 'QT' . $newNumber; // Prefix with "QR"
    }
}
