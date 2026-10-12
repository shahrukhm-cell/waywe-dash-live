<?php

namespace App\Livewire\Pages;

use App\Models\Job;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;

#[Layout('components.app-layout')]
class DeletedJobShowPage extends AppPage
{
    #[Locked]
    public int $jobId;

    public function mount(Job $job): void
    {
        $this->jobId = $job->id;
    }

    public function restore(): mixed
    {
        $this->job()->restore();
        session()->flash('success', 'Job restored successfully.');

        return $this->redirectRoute('jobs.deleted');
    }

    public function render()
    {
        $job = $this->job()->load(['customer', 'lineItems', 'reminders', 'technicians', 'payments']);

        return view('jobs.deleted-show', compact('job'));
    }

    private function job(): Job
    {
        $job = Job::withTrashed()->findOrFail($this->jobId);
        abort_unless($job->trashed(), 404);

        return $job;
    }
}
