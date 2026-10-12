<?php

namespace App\Livewire\Pages;

use App\Models\Job;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;

#[Layout('components.app-layout')]
class InvoicePage extends AppPage
{
    #[Locked]
    public int $jobId;

    public function mount(Job $job): void
    {
        $this->jobId = $job->id;
    }

    public function render()
    {
        $job = Job::with(['customer', 'lineItems', 'payments'])->findOrFail($this->jobId);
        abort_if($job->payments->isEmpty(), 404, 'An invoice is available after the first payment is recorded.');

        return view('jobs.invoice', compact('job'));
    }
}
