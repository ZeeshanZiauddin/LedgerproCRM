<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardResource\Pages;
use App\Models\Card;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontFamily;
use Filament\Tables;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Table;
use Filament\Forms\Components\Group;
use Guava\FilamentClusters\Forms\Cluster;
use Icetalker\FilamentTableRepeatableEntry\Infolists\Components\TableRepeatableEntry;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\Tabs;

class CardResource extends Resource
{
    protected static ?string $model = Card::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $recordTitleAttribute = 'card_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Group for card details

                Grid::make([
                    'default' => 3,
                ])
                    ->schema([
                        Placeholder::make('Card no'),
                        Forms\Components\TextInput::make('card_name')
                            ->default(fn() => CardResource::generateCardName()) // Call the generateInquiryName method
                            ->disabled() // Disable the field to prevent manual editing
                            ->required()
                            ->columnSpan(2)
                            ->hiddenLabel()
                            ->label('Card No.')
                            ->unique(ignoreRecord: true),
                    ])->columnSpan(1),


                Grid::make([
                    'default' => 3,
                ])
                    ->schema([
                        Placeholder::make('Date'),

                        Forms\Components\DatePicker::make('created_at')
                            ->displayFormat('d-M-Y')
                            ->native(false)
                            ->columnSpan(2)
                            ->default(fn() => Carbon::now())->disabled()
                            ->hiddenLabel()
                    ])->columnSpan(1),
                Grid::make([
                    'default' => 3,
                ])
                    ->schema([
                        Placeholder::make('Year'),
                        Forms\Components\DatePicker::make('created_at')
                            ->native(false)
                            ->hiddenLabel()
                            ->default(fn() => Carbon::now()->format('Y'))->disabled()
                            ->displayFormat('Y')
                            ->columnSpan(2)
                    ])->columnSpan(1),
                Grid::make([
                    'default' => 3,
                ])
                    ->schema([
                        Placeholder::make('Owner'),
                        Forms\Components\Select::make('user_id')
                            ->hiddenLabel()
                            ->default(fn() => auth()->user()->id)
                            ->columnSpan(2)
                            ->relationship('user', 'name')->disabled(),
                    ])->columnSpan(1),

                Group::make()
                    ->schema([

                        Grid::make([
                            'default' => 3,
                        ])
                            ->schema([
                                Placeholder::make('Customer'),

                                Forms\Components\Select::make('customer_id')
                                    ->relationship('customer', 'name')  // Relationship to the Customer model, assuming it has a `name` field
                                    ->nullable()
                                    ->preload()
                                    ->hiddenLabel()
                                    ->afterStateUpdated(function ($state, $set, $record) {
                                        if ($state) {
                                            $customer = \App\Models\Customer::find($state);
                                            if ($customer) {
                                                $set('contact_name', $customer->name);
                                                $set('contact_email', $customer->email);
                                                $set('contact_mobile', $customer->phone);
                                                $set('contact_address', $customer->address);
                                            }
                                        }
                                    })
                                    ->columnSpan(2)
                                    ->searchable()  // Make the field searchable
                                    ->placeholder('Select a Customer'),

                            ])->columnSpan(1),


                        Grid::make([
                            'default' => 3,
                        ])
                            ->schema([
                                Placeholder::make('Supplier'),

                                Forms\Components\Select::make('supplier_id')
                                    ->relationship('supplier', 'name')  // Relationship to the Supplier model, assuming it has a `name` field
                                    ->nullable()
                                    ->preload()
                                    ->hiddenLabel()
                                    ->columnSpan(2)
                                    ->searchable()  // Make the field searchable
                                    ->placeholder('Select a Supplier'),
                            ])->columnSpan(1),

                        Grid::make([
                            'default' => 3,
                        ])
                            ->schema([
                                Placeholder::make('Customer'),

                                Forms\Components\Select::make('inquiry_id')
                                    ->relationship('inquiry', 'inquiry_name')  // Relationship to the Supplier model, assuming it has a `name` field
                                    ->nullable()
                                    ->preload()
                                    ->hiddenLabel()
                                    ->columnSpan(2)
                                    ->searchable()  // Make the field searchable
                                    ->placeholder(placeholder: 'Inquiry ID'),
                            ])->columnSpan(1),
                    ])->columns(1)->columnSpan(1),

                // Group for contact details
                Group::make()
                    ->schema([

                        Grid::make([
                            'default' => 4,
                        ])
                            ->schema([
                                Placeholder::make('Contact Name'),
                                Forms\Components\TextInput::make('contact_name')
                                    ->nullable()
                                    ->hiddenLabel()
                                    ->columnSpan(3),
                            ])->columnSpan(1),

                        Grid::make([
                            'default' => 4,
                        ])
                            ->schema([
                                Placeholder::make('Contact Email'),
                                Forms\Components\TextInput::make('contact_email')
                                    ->nullable()
                                    ->hiddenLabel()
                                    ->columnSpan(3),
                            ])->columnSpan(1),

                        Grid::make([
                            'default' => 4,
                        ])
                            ->schema([
                                Placeholder::make('Address'),
                                Forms\Components\TextInput::make('contact_address')
                                    ->hiddenLabel()
                                    ->columnSpan(3)
                                    ->nullable(),
                            ])->columnSpan(1),
                    ])->columns(1)->columnSpan(2),
                Group::make()
                    ->schema([

                        Grid::make([
                            'default' => 3,
                        ])
                            ->schema([
                                Placeholder::make('Mobile No'),
                                Forms\Components\TextInput::make('contact_mobile')
                                    ->hiddenLabel()
                                    ->columnSpan(2)
                                    ->nullable(),
                            ])->columnSpan(1),

                        Grid::make([
                            'default' => 3,
                        ])
                            ->schema([
                                Placeholder::make('Home No'),
                                Forms\Components\TextInput::make('contact_home_number')
                                    ->hiddenLabel()
                                    ->columnSpan(2)
                                    ->nullable(),
                            ])->columnSpan(1),
                    ]),

                Grid::make([
                    'default' => 5,
                ])
                    ->schema([
                        Grid::make([
                            'default' => 3,
                        ])
                            ->schema([
                                Placeholder::make('Sale'),
                                TextInput::make('sales_price')
                                    ->numeric()
                                    ->default(0)
                                    ->reactive()
                                    ->hiddenLabel()
                                    ->columnSpan(2)
                                    ->required()
                                    ->extraAttributes(['x-model.number' => 'sales_price']),
                            ])->columnSpan(1),

                        Grid::make([
                            'default' => 3,
                        ])
                            ->schema([
                                Placeholder::make('Net Cost'),
                                TextInput::make('net_cost')
                                    ->numeric()
                                    ->default(0)
                                    ->reactive()
                                    ->required()
                                    ->hiddenLabel()
                                    ->columnSpan(2)
                                    ->extraAttributes(['x-model.number' => 'net_cost']), // Alpine.js binding
                            ])->columnSpan(1),
                        Grid::make([
                            'default' => 3,
                        ])
                            ->schema([
                                Placeholder::make('Tax'),
                                TextInput::make('tax')
                                    ->numeric()
                                    ->default(0)
                                    ->reactive()
                                    ->required()
                                    ->hiddenLabel()
                                    ->columnSpan(2)
                                    ->extraAttributes(['x-model.number' => 'tax']), // Alpine.js binding
                            ])->columnSpan(1),
                        Grid::make([
                            'default' => 3,
                        ])
                            ->schema([
                                Placeholder::make('Margin'),
                                TextInput::make('margin')
                                    ->default(0)
                                    ->readOnly()
                                    ->hiddenLabel()
                                    ->columnSpan(2)
                                    ->extraAttributes([
                                        'x-text' => 'sales_price - (net_cost + tax)',
                                        'x-init' => '() => margin = sales_price - (net_cost + tax)',
                                        'class' => 'py-0.5 ps-2',
                                    ]),
                            ])->columnSpan(1),
                        Grid::make([
                            'default' => 3,
                        ])
                            ->schema([
                                Placeholder::make('Paid'),
                                Forms\Components\TextInput::make('total_paid')
                                    ->readOnly()
                                    ->reactive()
                                    ->hiddenLabel()
                                    ->columnSpan(2)
                                    ->default(0)
                                    ->afterStateHydrated(function ($set, $record) {
                                        if ($record) {
                                            $set('total_paid', $record->receipts()->sum('total'));
                                        }
                                    }),
                            ])->columnSpan(1),
                    ])
                    ->columns(5)
                    ->extraAttributes(['x-data' => '{ sales_price: 0, net_cost: 0, tax: 0 }']), // Alpine.js data at the grid level

                TableRepeater::make('users')
                    ->hiddenLabel()
                    ->relationship('passengers')
                    ->headers([
                        Header::make('Passenger Name')->width('250px')->markAsRequired(),
                        Header::make('Ticket')->width('120px'),
                        Header::make('')->width('180px'),
                        Header::make('Issue date')->width('150px'),
                        Header::make('Option date')->width('150px'),
                        Header::make('Sale')->width('140px'),
                        Header::make('Net')->width('140px'),
                        Header::make('Cost')->width('140px'),
                        Header::make('Profit')->width('140px'),
                        Header::make('Pnr')->width('150px'),
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required(),

                        Forms\Components\Select::make('ticket_1')
                            ->label('Tkt no.')
                            ->placeholder('000')
                            ->native(false)
                            ->relationship('airline', 'code')
                            ->nullable(),

                        Forms\Components\TextInput::make('ticket_2')
                            ->label(false)
                            ->placeholder('0000000000')
                            ->maxLength(10)
                            ->minLength(10)
                            ->nullable(),

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
                            ->extraAttributes([
                                'x-model.number' => 'sale', // Alpine.js binding
                            ]),

                        Forms\Components\TextInput::make('cost')
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->extraAttributes([
                                'x-model.number' => 'cost', // Alpine.js binding
                            ]),

                        Forms\Components\TextInput::make('tax')
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->extraAttributes([
                                'x-model.number' => 'tax', // Alpine.js binding
                            ]),

                        Forms\Components\TextInput::make('margin')
                            ->numeric()
                            ->default(0)
                            ->readOnly()
                            ->extraAttributes([
                                'x-text' => 'sale - (cost + tax)', // Alpine.js margin calculation
                                'x-init' => 'margin = sale - (cost + tax)', // Initialize margin value based on the fields
                                'class' => 'py-0.5 ps-2'
                            ]),

                        Forms\Components\TextInput::make('pnr')
                            ->nullable(),
                    ])
                    ->extraAttributes([
                        'x-data' => '({ sale: 0, cost: 0, tax: 0, margin: 0 })', // Alpine.js data model per row
                    ])
                    ->columnSpanFull(),

            ])->columns(4);
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
                    ->slideOver()
                    ->modalActions([
                        Tables\Actions\ButtonAction::make('receipts')
                            ->label('Make Receipt')
                            ->color('primary')
                            ->icon('heroicon-s-document-plus')
                            ->url(function ($record) {
                                return route('filament.admin.resources.receipts.create', ['card_id' => $record->id]);
                            })
                    ]),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\ActionGroup::make([
                    \Parallax\FilamentComments\Tables\Actions\CommentsAction::make(),
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
            'edit' => Pages\EditCard::route('/{record}/edit'),
        ];
    }
    public static function generateCardName(): string
    {
        $latestInquiry = Card::latest('id')->first(); // Get the latest inquiry
        $latestNumber = $latestInquiry ? (int) substr($latestInquiry->card_name, 2) : 0; // Extract the number part and increment it
        $newNumber = str_pad($latestNumber + 1, 7, '0', STR_PAD_LEFT); // Increment and pad the number with leading zeros

        return 'QT' . $newNumber; // Prefix with "QR"
    }

    protected static function calculateMargin($set, $get)
    {
        // Ensure values are cast to integers or floats
        $salesPrice = (float) ($get('sales_price') ?? 0);
        $netCost = (float) ($get('net_cost') ?? 0);
        $tax = (float) ($get('tax') ?? 0);

        $margin = $salesPrice - ($netCost + $tax);
        $set('margin', max($margin, 0));
    }
}
