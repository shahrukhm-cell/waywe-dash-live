<?php

namespace App\Livewire\Pages;

use App\Models\Job;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;

#[Layout('components.app-layout')]
class QuotePage extends AppPage
{
    #[Locked]
    public int $jobId;

    public function mount(Job $job): void
    {
        $this->jobId = $job->id;
    }

    public function render()
    {
        $job = Job::with(['customer', 'lineItems'])->findOrFail($this->jobId);

        return view('jobs.quote', compact('job'));
    }
}
