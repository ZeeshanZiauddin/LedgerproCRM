<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InquiryResource\Pages;
use App\Filament\Resources\InquiryResource\RelationManagers\InquiryPassengerRelationManager;
use App\Models\Inquiry;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use Guava\FilamentClusters\Forms\Cluster;
use Icetalker\FilamentTableRepeatableEntry\Infolists\Components\TableRepeatableEntry;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Laravel\SerializableClosure\Serializers\Native;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;
    protected static ?string $navigationGroup = 'Resources';

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                    ->schema([
                        Group::make()
                            ->schema([
                                TextInput::make('inquiry_name')
                                    ->default(fn() => InquiryResource::generateInquiryName()) // Call the generateInquiryName method
                                    ->disabled() // Disable the field to prevent manual editing
                                    ->required()
                                    ->label('Inquiry No.'),
                                TextInput::make('user.name')
                                    ->label('User')
                                    ->default(fn($get) => auth()->user() ? auth()->user()->name : '')  // Show the logged-in user's name
                                    ->disabled()
                                    ->label('Owner'),

                                TextInput::make('created_at')
                                    ->label('Date')
                                    ->default(Carbon::now()->format('d M Y'))
                                    ->disabled(),
                                Select::make('status')
                                    ->label('Status')
                                    ->native(false)

                                    ->options([
                                        'pending' => 'Pending',
                                        'canceled' => 'Canceled',
                                        'in_progress' => 'In Progress',
                                    ])
                                    ->required()
                                    ->default('pending')
                            ])
                            ->extraAttributes(['class' => 'gap-0.5'])  // Reduce gap between inputs
                            ->columnSpan(1),
                        Group::make()
                            ->schema([
                                TextInput::make('contact_name')
                                    ->required(),
                                TextInput::make('contact_email'),
                                PhoneInput::make('contact_mobile')
                                    ->initialCountry('gb'),
                                TextInput::make('contact_address'),
                            ])
                            ->extraAttributes(['class' => 'gap-0.5'])
                            ->columnSpan(1),
                        Group::make()
                            ->schema([
                                DatePicker::make('option_date')
                                    ->native(false)
                                    ->displayFormat('d M Y'),
                                TextInput::make('pnr'),
                                TextInput::make('filter_point'),
                            ])
                            ->columnSpan(1),
                    ])
                    ->columns(3),

                TextArea::make('price_option')
                    ->columnSpanFull(),

                TableRepeater::make('items')
                    ->relationship('passengers')
                    ->label('Flights')
                    ->schema([
                        Select::make('departure_id')
                            ->searchable()
                            ->preload()
                            ->relationship('departure', 'city')
                            ->label('Departure')
                            ->placeholder('city'),
                        Select::make('destination_id')
                            ->searchable()
                            ->preload()
                            ->relationship('destination', 'city')
                            ->label('Destination')
                            ->placeholder('city'),
                        Cluster::make([
                            DatePicker::make('dep_date')
                                ->displayFormat('d M')
                                ->placeholder('Departure')

                                ->native(false),
                            DatePicker::make('return_date')
                                ->placeholder('Return')
                                ->displayFormat('d M')
                                ->native(false)
                        ])
                            ->label('Departure/return'),

                        Cluster::make([
                            TextInput::make('adults')
                                ->placeholder('Adults')
                                ->numeric(),
                            TextInput::make('child')
                                ->placeholder('Children')
                                ->numeric(),
                            TextInput::make('infants')
                                ->placeholder('Infants')
                                ->numeric(),
                        ])
                            ->label('Passengers'),
                        Select::make('flight_type')
                            ->label('Type')
                            ->native(false)
                            ->options([
                                'return' => 'Return',
                                'one_way' => 'One Way',
                                'direct_one_way' => 'Direct One Way',
                                'direct_return' => 'Direct Return',
                            ])
                            ->default('one_way')
                            ->placeholder('Type')
                            ->extraAttributes(['class' => 'max-w-[200px]']),
                        Select::make('airline_id')
                            ->searchable()
                            ->preload()
                            ->relationship('airline', 'name')
                            ->label('Airline')
                            ->placeholder('Select')
                    ])
                    ->reorderable()
                    ->collapsible()
                    ->colStyles([
                        'departure_id' => 'width: 150px;',
                        'destination_id' => 'width: 150px;',
                        'flight_type' => 'width: 150px',
                        'airline_id' => 'width: 200px',
                    ])
                    ->columnSpan('full'),

            ])
            ->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('inquiry_name'),
                \Filament\Tables\Columns\TextColumn::make('user.name')->label('User'),
                \Filament\Tables\Columns\TextColumn::make('created_at')->date(),
                \Filament\Tables\Columns\TextColumn::make('contact_name'),
                \Filament\Tables\Columns\TextColumn::make('contact_email'),
                \Filament\Tables\Columns\TextColumn::make('contact_mobile'),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'danger' => 'canceled',
                        'primary' => 'in_progress',
                    ]),
            ])
            ->filters([
                // Add any filters here
            ])
            ->actions([

                \Filament\Tables\Actions\ActionGroup::make([
                    \Filament\Tables\Actions\ViewAction::make()->slideOver(),
                    \Filament\Tables\Actions\EditAction::make(),
                    \Filament\Tables\Actions\Action::make('change_status')
                        ->icon('heroicon-s-arrow-path')
                        ->label('Change Status')
                        ->form([
                            Select::make('status')
                                ->label('Status')
                                ->native(false)
                                ->options([
                                    'pending' => 'Pending',
                                    'in_progress' => 'In Progress',
                                    'canceled' => 'Canceled',
                                ])
                                ->required(),
                        ])
                        ->modalWidth('sm')
                        ->action(function (Inquiry $record, array $data) {
                            $record->update(['status' => $data['status']]);
                            Notification::make()
                                ->title('Status updated successfully')
                                ->success()
                                ->send();
                        }),
                    \Filament\Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
                BulkAction::make('updateStatus')
                    ->icon('heroicon-s-arrow-path')

                    ->label('Change Status') // Label for bulk action
                    ->action(function ($records, array $data) {
                        // Update the status of selected records
                        foreach ($records as $record) {
                            $record->update(['status' => $data['status']]);
                        }
                    })
                    ->form([
                        Select::make('status')
                            ->label('Status')
                            ->native(false)
                            ->options([
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'canceled' => 'Canceled',
                            ])
                            ->required(),
                    ])->modalWidth('sm'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('inquiry_name')
                    ->fontFamily(FontFamily::Serif)
                    ->label('Inquiry Id'),
                TextEntry::make('created_at')
                    ->since()
                    ->label('Created'),
                TextEntry::make('user.name')
                    ->badge()
                    ->color('primary')
                    ->label('Issued by'),
                TextEntry::make('status')
                    ->badge()
                    ->color('primary')
                    ->label('Inquiry status'),
                TextEntry::make('contact_name'),
                TextEntry::make('contact_email'),
                TextEntry::make('contact_mobile'),
                TextEntry::make('contact_home_number'),
                TextEntry::make('contact_address')->columnSpan(2),
                TextEntry::make('price_option')->columnSpan(2),
                TextEntry::make('option_date'),
                TextEntry::make('pnr'),
                TextEntry::make('filter_point'),
                TableRepeatableEntry::make('passengers')
                    ->label('Flights')
                    ->schema([
                        TextEntry::make('departure.city')
                            ->label('Dep City'),
                        TextEntry::make('departure.country')
                            ->label('Dep Country'),
                        TextEntry::make('destination.city')
                            ->label('Des City'),
                        TextEntry::make('destination.country')
                            ->label('Des Country'),
                        TextEntry::make('dep_date')
                            ->date()
                            ->label('Issue date'),
                        TextEntry::make('return_date')
                            ->date()
                            ->label('Option Date'),
                        TextEntry::make('adults')
                            ->label('Adults'),
                        TextEntry::make('child')
                            ->label('children'),
                        TextEntry::make('infants')
                            ->label('infants'),
                        TextEntry::make('flight_type')
                            ->badge()
                            ->label('Type'),
                        TextEntry::make('airline.code')
                            ->label('Airline'),
                        TextEntry::make('airline.name')
                            ->label('Airline'),

                    ])
                    ->columnSpanFull(),
            ])
            ->columns(4);
    }



    public static function getRelations(): array
    {
        return [
            InquiryPassengerRelationManager::class,

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInquiries::route('/'),
            //'create' => Pages\CreateInquiry::route('/create'),
        ];
    }
    public static function generateInquiryName(): string
    {
        $latestInquiry = Inquiry::latest('id')->first(); // Get the latest inquiry
        $latestNumber = $latestInquiry ? (int) substr($latestInquiry->inquiry_name, 2) : 0; // Extract the number part and increment it
        $newNumber = str_pad($latestNumber + 1, 7, '0', STR_PAD_LEFT); // Increment and pad the number with leading zeros

        return 'QR' . $newNumber; // Prefix with "QR"
    }



}
