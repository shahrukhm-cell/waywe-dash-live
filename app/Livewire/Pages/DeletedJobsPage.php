<?php

namespace App\Livewire\Pages;

use App\Models\Job;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('components.app-layout')]
class DeletedJobsPage extends AppPage
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        $this->search = (string) request()->query('search', '');
    }

    public function restore(int $jobId): mixed
    {
        Job::onlyTrashed()->findOrFail($jobId)->restore();
        session()->flash('success', 'Job restored successfully.');

        return $this->redirectRoute('jobs.deleted');
    }

    public function render()
    {
        $search = trim($this->search);
        $jobs = Job::onlyTrashed()
            ->with('customer')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($jobQuery) use ($search) {
                    $jobQuery
                        ->where('job_number', 'like', '%' . $search . '%')
                        ->orWhere('quote_estimate_number', 'like', '%' . $search . '%')
                        ->orWhere('site_full_name', 'like', '%' . $search . '%')
                        ->orWhere('site_telephone', 'like', '%' . $search . '%')
                        ->orWhere('site_business', 'like', '%' . $search . '%')
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery
                                ->where('first_name', 'like', '%' . $search . '%')
                                ->orWhere('last_name', 'like', '%' . $search . '%')
                                ->orWhere('phone', 'like', '%' . $search . '%')
                                ->orWhere('business', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest('deleted_at')
            ->paginate(15);

        return view('jobs.deleted', compact('jobs', 'search'));
    }
}
