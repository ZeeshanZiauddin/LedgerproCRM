<?php

use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\TicketSaleDetailPDFController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

use App\Http\Controllers\PDFController;

Route::get('/generate-pdf-preview', [PDFController::class, 'preview'])->name('generate.pdf.preview');

Route::get('/ticketdetails/preview', [TicketSaleDetailPDFController::class, 'preview'])->name('ticketdetails.preview');

Route::post('/api/update-itinerary', [ItineraryController::class, 'updateItinerary']);
