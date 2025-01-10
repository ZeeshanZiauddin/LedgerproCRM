<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\CardPassenger;
use App\Models\Payment;
use App\Models\PaymentPassenger;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Filament\Actions\Modal\Actions\Action;
use Filament\Forms;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Tables\Table;
use Guava\FilamentClusters\Forms\Cluster;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Torgodly\Html2Media\Tables\Actions\Html2MediaAction;


class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Voucher no')
                ->inlineLabel()
                ->default(function () {
                    // Optional: Display a placeholder or dynamic default value
                    $lastId = Payment::max('id') ?? 0;
                    $newId = $lastId + 1;
                    return 'SP' . str_pad($newId, 6, '0', STR_PAD_LEFT);
                })
                ->disabled(),
            Forms\Components\Select::make('supplier_id')
                ->inlineLabel()
                ->label('Supplier')
                ->relationship('supplier', 'name')
                ->required()
                ->afterStateUpdated(function ($set) {
                    $set('passengers', []);
                    $set('payable', 0);
                })
                ->reactive(),

            Forms\Components\TextInput::make('cheque_no')
                ->inlineLabel()

                ->label('Cheque No'),
            Forms\Components\Select::make('bank_id')
                ->label('Bank')
                ->inlineLabel()
                ->relationship('bank', 'name')
                ->native(false),
            Forms\Components\TextInput::make('total')
                ->numeric()
                ->prefix(setting('site_currency') ?? '£')
                ->inlineLabel()
                ->debounce(300)
                ->default(0)
                ->suffix(function ($get) {
                    return dollar($get('payable') ?? 0.00);
                })
                ->suffixIconColor(function ($get, $state) {
                    $payable = $get('payable') ?? 0;
                    $color = 'success';
                    if ($payable > $state) {
                        $color = 'danger';
                    } elseif ($payable < $state) {
                        $color = 'warning';
                    }
                    return $color;
                })
                ->suffixIcon(function ($get, $state) {
                    $payable = $get('payable') ?? 0;
                    if (!$payable) {
                        return null;
                    }
                    $icon = 'heroicon-m-check-circle';
                    if ($payable > $state) {
                        $icon = 'heroicon-m-arrow-trending-down';
                    } elseif ($payable < $state) {
                        $icon = 'heroicon-m-arrow-trending-up';
                    }
                    return $icon;
                })
                ->label('Total Amount'),

            Forms\Components\Textarea::make('details')
                ->label('Details')->columnSpanFull(),

            Forms\Components\Actions::make([
                Forms\Components\Actions\Action::make('Remove All')
                    ->label('Remove All Tickets')
                    ->visible(fn($operation) => $operation === 'create')
                    ->action(function ($set) {
                        $set('passengers', []);
                        $set('payable', 0);
                    })
                    ->badge()
                    ->badgeColor('warning')
                    ->badgeIcon('heroicon-o-trash'),
                Forms\Components\Actions\Action::make('Alocate Tickets')
                    ->modalHeading('Select Tickets')
                    ->modalSubheading(
                        function ($get) {
                            $supplierId = $get('supplier_id');
                            $supplier = \App\Models\Supplier::find($supplierId);
                            return 'Select Tickets Issued on Cards by ' . $supplier->name;
                        }
                    )
                    ->modalWidth('3xl')
                    ->form(
                        function ($get) {

                            if (!$get('supplier_id')) {
                                return [];
                            }
                            $supplierId = $get('supplier_id');
                            $selectedPassengers = $get('passengers') ?? [];
                            // dd($selectedPassengers);
                            return [
                                TableRepeater::make('passengers')
                                    ->default(function () use ($supplierId, $selectedPassengers) {
                                $selectedPassengerIds = Arr::pluck($selectedPassengers, 'id');
                                // dd($selectedPassengerIds);
                                $passengers = CardPassenger::whereHas('card', function ($q) use ($supplierId) {
                                    $q->where('supplier_id', $supplierId);
                                })
                                    ->whereDoesntHave('payments', function ($query) {
                                    $query->join('payment_passengers as pp', 'payments.id', '=', 'pp.payment_id')
                                        ->whereRaw('(card_passengers.cost + card_passengers.tax) = pp.paid')
                                        ->whereColumn('card_passengers.id', '=', 'pp.card_passenger_id');
                                })
                                    ->whereNotNull('issue_date')
                                    ->whereNotIn('card_passengers.id', $selectedPassengerIds) // Fully qualify `id`
                                    ->with(['card.airline'])
                                    ->leftJoin('payment_passengers as pp', 'card_passengers.id', '=', 'pp.card_passenger_id')
                                    ->select('card_passengers.*', 'pp.paid')
                                    ->get()
                                    ->toArray();

                                $data = [];
                                foreach ($passengers as $passenger) {
                                    $tkt = $passenger['card']['airline']['code'] . $passenger['ticket_2'];
                                    $data[] = [
                                        'selected' => false,
                                        'id' => $passenger['id'],
                                        'card' => $passenger['card']['card_name'],
                                        'name' => $passenger['name'],
                                        'ticket' => $tkt,
                                        'pnr' => $passenger['pnr'],
                                        'total' => $passenger['sale'] + $passenger['tax'],
                                        'balance' => ($passenger['sale'] + $passenger['tax']) - ($passenger['paid'] ?? 0),
                                        'issue_date' => $passenger['issue_date'],
                                    ];
                                }
                                return $data;
                            })

                                    ->headers([
                                        Header::make('')->markAsRequired(),
                                        Header::make('No')->width('140px')->markAsRequired(),
                                        Header::make('passenger_name')->width('200px'),
                                        Header::make('Tkt')->width('150px'),
                                        Header::make('PNR')->width('100px'),
                                        Header::make('Total')->width('70px'),
                                        Header::make('Balance')->width('70px'),
                                        Header::make('Issue')->width('80px'),
                                    ])
                                    ->schema([
                                        Forms\Components\Checkbox::make('selected')
                                            ->default(false),
                                        Forms\Components\Hidden::make('id'),
                                        Forms\Components\TextInput::make('card'),
                                        Forms\Components\TextInput::make('name')->readOnly(),
                                        Forms\Components\TextInput::make('ticket')->readOnly(),
                                        Forms\Components\TextInput::make('pnr')->readOnly(),
                                        Forms\Components\TextInput::make('total')->readOnly(),
                                        Forms\Components\TextInput::make('balance')->readOnly(),
                                        Forms\Components\DatePicker::make('issue_date')
                                            ->readOnly()
                                            ->native(false)
                                            ->displayFormat('dM'),
                                    ])
                                    ->deletable(condition: false)
                                    ->orderable(false)
                                    ->emptyLabel('That is all there is... ')
                                    ->addable(false)
                                    ->columnSpanFull(),
                            ];
                        }
                    )
                    ->action(function ($data, $get, $set) {
                        $selected = array_filter($data['passengers'], function ($pass) {
                            return $pass['selected'] ?? $pass;
                        });
                        $oldPassengers = $get('passengers') ?? [];
                        $passengers = array_unique(array_merge($oldPassengers, $selected), SORT_REGULAR);

                        $set('passengers', $passengers);
                    })
                    ->visible(fn($operation) => $operation === 'create')
                    ->tooltip(fn($get) => $get('supplier_id') ? 'Alocate tickets' : 'Select the supplier first')
                    ->disabled(fn($get) => !$get('supplier_id'))
                    ->badge(),
            ])->alignment(Alignment::End)->columnSpanFull(),
            Forms\Components\Hidden::make('payable')->default(0),
            Forms\Components\Hidden::make('all_passengers')->default([]),
            TableRepeater::make('passengers')
                ->default([])
                ->relationship('cardPassengers')
                ->headers([
                    Header::make('No')->width('145px')->markAsRequired(),
                    Header::make('passenger_name')->width('150px'),
                    Header::make('Tkt')->width('150px'),
                    Header::make('PNR')->width('100px'),
                    Header::make('Total')->width('80px'),
                    Header::make('Paid')->width('80px'),
                    Header::make('Issue Date')->width('90px'),
                ])
                ->schema([
                    Forms\Components\TextInput::make('card'),
                    Forms\Components\TextInput::make('name')->readOnly(),
                    Forms\Components\Hidden::make('id'),
                    Forms\Components\TextInput::make('ticket')->readOnly(),
                    Forms\Components\TextInput::make('pnr')->readOnly(),
                    Forms\Components\TextInput::make('total')->readOnly(),
                    Forms\Components\TextInput::make('balance'),
                    Forms\Components\DatePicker::make('issue_date')
                        ->native(false)
                        ->displayFormat('dM'),
                ])
                ->saveRelationshipsUsing(function ($state, $record) {
                    dd($state, $record);
                })
                ->orderable(false)
                ->emptyLabel('No Selected Passengers')
                ->addable(false)
                ->columnSpanFull(),
        ]);
    }
    // public static function infolist(Infolist $infolist): Infolist
    // {
    //     return $infolist
    //         ->schema([
    //             TextEntry::make('id')
    //                 ->label('Card Name'),
    //             TextEntry::make('supplier.name')
    //                 ->label('Supplier'),
    //             TextEntry::make('created_at')
    //                 ->since()
    //                 ->label('date'),
    //             TextEntry::make('supplier.name')
    //                 ->label('Card Name'),

    //             TextEntry::make('bank.name')
    //                 ->label('Bank'),
    //             TextEntry::make('details')
    //                 ->label('Details'),
    //             TextEntry::make('passenger_ids')
    //                 ->visible(false)
    //                 ->label('Details'),
    //             TableRepeatableEntry::make('receipts')
    //                 ->schema([
    //                     TextEntry::make('name'),
    //                 ])
    //                 ->columnSpanFull(),
    //         ])
    //         ->columns(5);
    // }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supplier.name')->label('Supplier'),
                Tables\Columns\TextColumn::make('cheque_no')->label('Cheque No'),
                Tables\Columns\TextColumn::make('bank.name')->label('Bank'),
                Tables\Columns\TextColumn::make('total')->label('Total'),
            ])
            ->filters([])
            ->actions([

                Tables\Actions\ViewAction::make()
                    ->modal()
                    ->modalWidth('3xl'),
                Tables\Actions\EditAction::make()->modalWidth('3xl'),
                Tables\Actions\Action::make('viewVoucher')
                    ->label('Voucher')
                    ->icon('heroicon-o-document-text')
                    ->slideOver()
                    ->modalHeading(fn($record) => static::generateSerial($record->id))
                    ->modalSubheading(function ($record) {
                        return ' Voucher for ' . $record->supplier->name;
                    })
                    ->modalWidth('3xl')
                    ->modalContent(function ($record) {
                        dd($record->cardPassengers);
                        $cardPassengerIds = PaymentPassenger::where('payment_id', $record->id)->pluck('card_passenger_id');
                        $passengers = CardPassenger::whereIn('id', $cardPassengerIds)->get();
                        $data = [
                            'company' => [
                                'name' => setting('site_name'),
                                'email' => setting('site_email'),
                                'phone' => setting('site_phone'),
                                'address' => setting('site_address'),
                            ],
                            'passengers' => $passengers,
                            'supplier' => $record->supplier->name,
                            'bank' => $record->bank->name,
                            'details' => $record->details,
                            'user' => auth()->user()->name,
                            'currency' => setting('site_currency'),
                            'record_date' => $record->created_at->format('d/M/y'),
                            'record_year' => $record->created_at->format('Y'),
                            'current_date' => now()->format('d/M/y'),

                        ];

                        // Generate the PDF content
                        $pdf = Pdf::loadView('pdf.payment', ['payment' => $record]);

                        // Return the PDF as a data URL to embed in the modal
                        $pdfData = base64_encode($pdf->output());
                        $pdfSrc = 'data:application/pdf;base64,' . $pdfData;

                        return view('components.pdf-viewer', ['pdfSrc' => $pdfSrc]);
                    })
                    ->modalSubmitActionLabel('Email')
                    ->modalSubmitAction(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            //          'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }


    public static function generateSerial(int $id)
    {
        $num = str_pad($id + 1, 7, '0', STR_PAD_LEFT);
        return 'SP' . $num;
    }
}
