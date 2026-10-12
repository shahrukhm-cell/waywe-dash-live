<?php

use App\Http\Controllers\JobDocumentController;
use App\Livewire\Pages\CustomerCreatePage;
use App\Livewire\Pages\CustomerEditPage;
use App\Livewire\Pages\CustomerIndexPage;
use App\Livewire\Pages\CustomerShowPage;
use App\Livewire\Pages\DashboardPage;
use App\Livewire\Pages\DeletedJobShowPage;
use App\Livewire\Pages\DeletedJobsPage;
use App\Livewire\Pages\DispatchPage;
use App\Livewire\Pages\InvoicePage;
use App\Livewire\Pages\JobCalendarPage;
use App\Livewire\Pages\JobCreatePage;
use App\Livewire\Pages\JobEditPage;
use App\Livewire\Pages\JobIndexPage;
use App\Livewire\Pages\JobShowPage;
use App\Livewire\Pages\QuotePage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardPage::class)->name('dashboard');

    Route::get('/customers', CustomerIndexPage::class)->name('customers.index');
    Route::get('/customers/create', CustomerCreatePage::class)->name('customers.create');
    Route::get('/customers/{customer}', CustomerShowPage::class)->name('customers.show');
    Route::get('/customers/{customer}/edit', CustomerEditPage::class)->name('customers.edit');

    Route::get('/jobs', JobIndexPage::class)->name('jobs.index');
    Route::get('/jobs/calendar', JobCalendarPage::class)->name('jobs.calendar');
    Route::get('/jobs/create', JobCreatePage::class)->name('jobs.create');
    Route::get('/jobs/{job}', JobShowPage::class)->name('jobs.show');
    Route::get('/jobs/{job}/edit', JobEditPage::class)->name('jobs.edit');
    Route::get('/jobs/{job}/dispatch', DispatchPage::class)->name('jobs.dispatch');
    Route::get('/jobs/{job}/dispatch/print', [JobDocumentController::class, 'printDispatch'])->name('jobs.dispatch.print');
    Route::get('/jobs/{job}/quote', QuotePage::class)->name('jobs.quote');
    Route::get('/jobs/{job}/quote/download', [JobDocumentController::class, 'downloadQuote'])->name('jobs.quote.download');
    Route::get('/jobs/{job}/invoice', InvoicePage::class)->name('jobs.invoice');
    Route::get('/jobs/{job}/invoice/download', [JobDocumentController::class, 'downloadInvoice'])->name('jobs.invoice.download');
    Route::get('/deleted-jobs', DeletedJobsPage::class)->name('jobs.deleted');
    Route::get('/deleted-jobs/{job}', DeletedJobShowPage::class)->name('jobs.deleted.show')->withTrashed();
});

require __DIR__.'/auth.php';
