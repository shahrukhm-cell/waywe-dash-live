<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Barryvdh\DomPDF\Facade\Pdf;

class JobDocumentController extends Controller
{
    public function printDispatch(Job $job)
    {
        $job->load(['customer', 'lineItems', 'technicians']);

        return Pdf::loadView('jobs.dispatch_pdf', compact('job'))
            ->setPaper('a4', 'portrait')
            ->download('dispatch-' . ($job->job_number ?: $job->id) . '.pdf');
    }

    public function downloadQuote(Job $job)
    {
        $job->load(['customer', 'lineItems']);

        return Pdf::loadView('jobs.quote_pdf', compact('job'))
            ->setPaper('a4', 'portrait')
            ->download('quote-' . ($job->quote_estimate_number ?: $job->id) . '.pdf');
    }

    public function downloadInvoice(Job $job)
    {
        $job->load(['customer', 'lineItems', 'payments']);
        abort_if($job->payments->isEmpty(), 404, 'An invoice is available after the first payment is recorded.');

        return Pdf::loadView('jobs.invoice_pdf', compact('job'))
            ->setPaper('a4', 'portrait')
            ->download('invoice-' . $job->invoice_number . '.pdf');
    }
}
