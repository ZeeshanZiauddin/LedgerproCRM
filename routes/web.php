<?php

use App\Http\Controllers\TicketSaleDetailPDFController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

use App\Http\Controllers\PDFController;

Route::get('ticket-sale-details/preview', [TicketSaleDetailPDFController::class, 'preview'])->name('generate.ticketdetails.pdf.preview');

Route::get('ticket-sale-details/preview', [TicketSaleDetailPDFController::class, 'preview'])->name('generate.ticketdetails.pdf.preview');
