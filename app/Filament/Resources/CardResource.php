<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardResource\Pages;
use App\Filament\Resources\CardResource\RelationManagers\ReceiptsRelationManager;
use App\Models\Card;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
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
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use Icetalker\FilamentTableRepeatableEntry\Infolists\Components\TableRepeatableEntry;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\Tabs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class CardResource extends Resource
{
    protected static ?string $model = Card::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Resources';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'card_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(components:
                [
                    Group::make()
                        ->schema([
                            Forms\Components\TextInput::make('card_name')
                                ->disabled() // Disable the field to prevent manual editing
                                ->inlineLabel()
                                ->label('Card No.'),

                            Forms\Components\DatePicker::make('created_at')
                                ->label('Date')
                                ->displayFormat('d-M-Y')
                                ->native(false)
                                ->inlineLabel()
                                ->default(fn() => Carbon::now())->disabled(),

                            Forms\Components\Select::make('user_id')
                                ->inlineLabel()
                                ->default(fn() => auth()->user()->id)
                                ->relationship('user', 'name')->disabled(),
                            Forms\Components\Select::make('airline_id')
                                ->placeholder('000')
                                ->inlineLabel()
                                ->relationship('airline', modifyQueryUsing: fn(Builder $query) => $query->orderBy('first_name')->orderBy('last_name'), )
                                ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->code} {$record->iata}")
                                ->searchable(['code', 'iata'])
                                ->native(false)
                                ->getOptionLabelUsing(function ($value) {
                                    $airline = \App\Models\Airline::find($value);
                                    return $airline ? $airline->iata . ' - ' . $airline->code : 'Unknown';
                                })
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    if ($state) {
                                        // Fetch the current repeater data
                                        $passengers = $get('passenger') ?? [];
                                        // Update the 'ticket_1' field for each passenger
                                        $updatedPassengers = array_map(function ($passenger) use ($state) {
                                            $passenger['ticket_1'] = $state;
                                            return $passenger;
                                        }, $passengers);

                                        // Set the updated repeater data
                                        $set('passenger', $updatedPassengers);
                                    }
                                }),
                        ])
                        ->columns(4)
                        ->columnSpanFull(),

                    Group::make()
                        ->schema([

                            Forms\Components\Select::make('customer_id')
                                ->relationship('customer', 'name')  // Relationship to the Customer model, assuming it has a `name` field
                                ->nullable()
                                ->native(false)
                                ->inlineLabel()
                                ->live(debounce: 300)
                                ->afterStateUpdated(function ($state, $set) {
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
                                ->placeholder('Select a Customer'),

                            Forms\Components\Select::make('supplier_id')
                                ->relationship('supplier', 'name')  // Relationship to the Supplier model, assuming it has a `name` field
                                ->nullable()
                                ->inlineLabel()
                                ->native(false)
                                ->placeholder('Select a Supplier'),

                            Forms\Components\Select::make('inquiry_id')
                                ->relationship('inquiry', 'inquiry_name')  // Relationship to the Supplier model, assuming it has a `name` field
                                ->nullable()
                                ->preload()
                                ->inlineLabel()
                                ->searchable()  // Make the field searchable
                                ->placeholder(placeholder: 'Inquiry ID'),
                        ])->columns(1)->columnSpan(1),

                    // Group for contact details
                    Group::make()
                        ->schema([

                            Forms\Components\TextInput::make('contact_name')
                                ->nullable()
                                ->inlineLabel(),

                            Forms\Components\TextInput::make('contact_email')
                                ->nullable()
                                ->inlineLabel(),
                            Forms\Components\TextInput::make('contact_address')
                                ->inlineLabel()
                                ->nullable(),
                        ])->columns(1)->columnSpan(2),
                    Group::make()
                        ->schema([

                            Forms\Components\TextInput::make('contact_mobile')
                                ->inlineLabel()->label('Mobile No')->nullable(),
                            Forms\Components\TextInput::make('contact_home_number')
                                ->inlineLabel()->label('Home No')->nullable(),
                            Textarea::make('itinerary')
                                ->hiddenLabel()
                                ->placeholder("Paste the itinerary here...")
                                ->rows(1)
                                ->afterStateUpdated(
                                    function ($state, $set, $get) {
                                        $lines = explode("\n", $state);
                                        $passengers = [];
                                        $flights = [];
                                        $airline = $get('airline_id');

                                        foreach ($lines as $line) {
                                            $line = trim($line);

                                            // Parse passengers
                                            if (preg_match_all('/\d+\.\d+([A-Z\/ ]+ [A-Z]{2,4})/', $line, $matches)) {
                                                foreach ($matches[1] as $name) {
                                                    $passengers[] = [
                                                        'name' => trim($name),
                                                        'ticket_1' => $airline,
                                                        'ticket_2' => null,
                                                        'issue_date' => null,
                                                        'sale' => 0,
                                                        'cost' => 0,
                                                        'tax' => 0,
                                                        'margin' => 0,
                                                        'pnr' => null,
                                                    ];
                                                }
                                            }

                                            // Parse flights
                                            if (preg_match('/^\d+\s*\.\s+([A-Z]{2})\s+(\d+)\s+([A-Z])\s+(\d{2}[A-Z]{3})\s+([A-Z]{3})([A-Z]{3})\s+HK\d+\s+(\d{4})\s+(#?)(\d{4})\s+O\*/', $line, $flightMatches)) {
                                                $dateRaw = $flightMatches[4] . date('Y'); // Date in format 04FEB
                                                $timeDepRaw = $flightMatches[7]; // Departure time in format 1910
                                                $timeArrRaw = $flightMatches[9]; // Arrival time in format 0415

                                                // Convert date to YYYY-MM-DD
                                                $date = Carbon::createFromFormat('dMY', $dateRaw);
                                                $formattedDate = $date->format('Y-m-d');
                                                // Convert time to 24-hour format (HH:mm)
                                                $formattedDepTime = substr($timeDepRaw, 0, 2) . ':' . substr($timeDepRaw, 2, 2);
                                                $formattedArrTime = substr($timeArrRaw, 0, 2) . ':' . substr($timeArrRaw, 2, 2);

                                                $flights[] = [
                                                    'airline' => $flightMatches[1],
                                                    'flight' => $flightMatches[2],
                                                    'class' => $flightMatches[3],
                                                    'date' => $formattedDate,
                                                    'from' => $flightMatches[5],
                                                    'to' => $flightMatches[6],
                                                    'dep' => $formattedDepTime,
                                                    'arr' => $formattedArrTime,
                                                ];
                                            }
                                        }

                                        $parsedData = [
                                            'passengers' => $passengers,
                                            'flights' => $flights,
                                        ];

                                        $set('passengers', $parsedData['passengers']);
                                        $set('flights', $parsedData['flights']);
                                        $set('sales_price', 0);
                                        $set('net_cost', 0);
                                        $set('tax', 0);
                                        $set('margin', 0);
                                    }
                                )
                                ->live(debounce: 500),
                        ]),
                    Tabs::make('Tabs')
                        ->tabs([
                            Tabs\Tab::make('Passengers')
                                ->icon('heroicon-o-user-group')
                                ->schema([
                                    TableRepeater::make('passengers')
                                        ->relationship('passengers')
                                        ->default([])
                                        ->hiddenLabel()
                                        ->headers([
                                            Header::make('Passenger Name')->width('250px')->markAsRequired(),
                                            Header::make('Ticket')->width('120px'),
                                            Header::make('')->width('180px'),
                                            Header::make('Issue date')->width('150px'),
                                            Header::make('Sale')->width('140px'),
                                            Header::make('Net')->width('140px'),
                                            Header::make('tax')->width('140px'),
                                            Header::make('Profit')->width('140px'),
                                            Header::make('Pnr')->width('150px'),
                                        ])
                                        ->schema([
                                            Forms\Components\TextInput::make('name')
                                                ->nullable(),

                                            Forms\Components\Select::make('ticket_1')
                                                ->placeholder(placeholder: '000')
                                                ->relationship('airline', 'code')

                                                ->nullable()
                                                ->default(function ($get) {
                                                    return $get('airline_id');
                                                }),

                                            Forms\Components\TextInput::make('ticket_2')
                                                ->label(false)
                                                ->placeholder('0000000000')
                                                ->maxLength(10)
                                                ->minLength(10)
                                                ->nullable(),

                                            Forms\Components\DatePicker::make('issue_date')
                                                ->native(false)
                                                ->displayFormat('d M y')
                                                ->default(null)
                                                ->placeholder('dd mm yy')
                                                ->nullable(),
                                            Forms\Components\TextInput::make('sale')
                                                ->nullable()
                                                ->default(0)
                                                ->extraAttributes(['data-field' => 'sale']),
                                            Forms\Components\TextInput::make('cost')
                                                ->nullable()
                                                ->default(0)
                                                ->extraAttributes(['data-field' => 'cost']),
                                            Forms\Components\TextInput::make('tax')
                                                ->nullable()
                                                ->default(0)
                                                ->extraAttributes(['data-field' => 'tax']),
                                            Forms\Components\TextInput::make('margin')
                                                ->nullable()
                                                ->extraAttributes(['data-field' => 'margin'])
                                                ->default(0),

                                            Forms\Components\TextInput::make('pnr')
                                                ->nullable(),
                                        ])
                                        ->columnSpanFull(),
                                ]),
                            Tabs\Tab::make('Flight Details')
                                ->icon('heroicon-o-rocket-launch')
                                ->schema([
                                    TableRepeater::make('flights')
                                        ->relationship(name: 'flights')
                                        ->hiddenLabel()
                                        ->headers([
                                            Header::make('airline')->width('250px')->markAsRequired(),
                                            Header::make('flight')->width('120px'),
                                            Header::make('class')->width('180px'),
                                            Header::make('date')->width('150px'),
                                            Header::make('from')->width('150px'),
                                            Header::make('to')->width('140px'),
                                            Header::make('Dep time')->width('140px'),
                                            Header::make('Arr time')->width('140px'),
                                        ])
                                        ->label('Flights')
                                        ->schema([
                                            Forms\Components\TextInput::make('airline')
                                                ->required(),
                                            Forms\Components\TextInput::make('flight')
                                                ->required(),
                                            Forms\Components\TextInput::make('class')
                                                ->required(),
                                            Forms\Components\DatePicker::make('date')
                                                ->displayFormat('dM')
                                                ->native(false)
                                                ->required(),
                                            Forms\Components\TextInput::make('from')
                                                ->required(),
                                            Forms\Components\TextInput::make('to')
                                                ->required(),
                                            Forms\Components\TextInput::make('dep')
                                                ->required(),
                                            Forms\Components\TextInput::make('arr')
                                                ->nullable(),
                                        ])
                                        ->default([])
                                        ->columnSpanFull()
                                        ->createItemButtonLabel('Add Flight'),
                                ]),
                        ])
                        ->columnSpanFull(),

                    Grid::make([
                        'default' => 5,
                    ])
                        ->schema([

                            TextInput::make('sales_price')
                                ->label('Sales')
                                ->extraAttributes(['data-field' => 'total_sale'])
                                ->default(0)
                                ->inlineLabel(),

                            TextInput::make('net_cost')
                                ->label('Cost')
                                ->extraAttributes(['data-field' => 'total_cost'])
                                ->default(0)
                                ->inlineLabel(),
                            TextInput::make('tax')
                                ->extraAttributes(['data-field' => 'total_tax'])
                                ->default(0)
                                ->inlineLabel(),
                            TextInput::make('margin')
                                ->extraAttributes(['data-field' => 'total_margin'])
                                ->default(0)
                                ->inlineLabel(),

                            Forms\Components\TextInput::make('total_paid')
                                ->label('Paid')
                                ->readOnly()
                                ->inlineLabel()
                                ->default(0)
                                ->afterStateHydrated(function ($set, $record) {
                                    if ($record) {
                                        $set('total_paid', $record->receipts()->sum('total'));
                                    }
                                }),
                        ])
                        ->columns(5),
                ])->columns(4);
    }
    public function mount(): void
    {
        parent::mount();
        $this->dispatchBrowserEvent('form-loaded');
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
                RelationManagerAction::make('lesson-relation-manager')
                    ->icon('heroicon-o-ticket')
                    ->label('Receipts')
                    ->hideRelationManagerHeading(true)
                    ->slideOver()
                    ->relationManager(ReceiptsRelationManager::make()),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\ActionGroup::make([
                    \Parallax\FilamentComments\Tables\Actions\CommentsAction::make(),
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
                        Infolists\Components\TextEntry::make('title')
                            ->suffixAction(\Guava\FilamentModalRelationManagers\Actions\Infolist\RelationManagerAction::make()
                                ->label('View Receipts')
                                ->relationManager(ReceiptsRelationManager::make()))

                    ])
                    ->columnSpanFull(),
            ])
            ->columns(4);
    }
    public static function getRelations(): array
    {
        return [
            ReceiptsRelationManager::class,
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
    public static function updateSums(callable $get, callable $set, $record)
    {
        $passengers = $get('../') ?? [];

        // Ensure each value is treated as a float before summing
        $saleSum = collect($passengers)->sum(fn($passenger) => (float) $passenger['sale']);
        $costSum = collect($passengers)->sum(fn($passenger) => (float) $passenger['cost']);
        $taxSum = collect($passengers)->sum(fn($passenger) => (float) $passenger['tax']);

        // Now calculate the margin sum safely
        $marginSum = $saleSum - ($costSum + $taxSum);


        $set('../../sales_price', $saleSum);
        $set('../../net_cost', $costSum);
        $set('../../tax', $taxSum);
        $set('../../margin', $marginSum);
    }

}
