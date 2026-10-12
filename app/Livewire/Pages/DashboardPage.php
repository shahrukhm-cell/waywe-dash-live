<?php

namespace App\Livewire\Pages;

use App\Models\Job;
use App\Models\JobPayment;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('components.app-layout')]
class DashboardPage extends AppPage
{
    use WithPagination;

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    #[Url(as: 'payment_method')]
    public string $paymentMethod = '';

    #[Url(as: 'collection_status')]
    public string $collectionStatus = '';

    #[Url(as: 'all_dates')]
    public bool $allDates = false;

    public function mount(): void
    {
        $this->from = (string) request()->query('from', today()->startOfMonth()->toDateString());
        $this->to = (string) request()->query('to', today()->toDateString());
        $this->paymentMethod = (string) request()->query('payment_method', '');
        $this->collectionStatus = (string) request()->query('collection_status', '');
        $this->allDates = request()->boolean('all_dates');
    }

    public function applyFilters(): void
    {
        $this->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'paymentMethod' => ['nullable', Rule::in(['check', 'charge', 'card', 'cash', 'other'])],
            'collectionStatus' => ['nullable', Rule::in(['outstanding', 'paid'])],
        ]);

        $this->allDates = false;
        $this->resetPage();
    }

    public function showAllDates(): void
    {
        $this->allDates = true;
        $this->resetPage();
    }

    public function render()
    {
        $from = $this->allDates ? null : ($this->from ?: null);
        $to = $this->allDates ? null : ($this->to ?: null);
        $filters = [
            'payment_method' => $this->paymentMethod,
            'collection_status' => $this->collectionStatus,
        ];
        $jobs = Job::with('customer')->latest()->take(6)->get();
        $todayJobs = Job::with('customer')
            ->whereDate('scheduled_date', today())
            ->orderBy('scheduled_time')
            ->get();

        $paymentsQuery = JobPayment::query()
            ->with([
                'job' => fn ($query) => $query->withTrashed(),
                'job.customer',
            ])
            ->when($from, fn ($query) => $query->whereDate('transaction_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('transaction_date', '<=', $to))
            ->when($this->paymentMethod, fn ($query, $method) => $query->where('payment_method', $method))
            ->when($this->collectionStatus, function ($query, $status) {
                $query->whereHas('job', function ($jobsQuery) use ($status) {
                    $status === 'paid'
                        ? $jobsQuery->where('status', 'paid')
                        : $jobsQuery->where('status', '!=', 'paid');
                });
            });

        $collectionTotal = (float) (clone $paymentsQuery)->sum('amount');
        $collectionCount = (clone $paymentsQuery)->count();
        $payments = $paymentsQuery
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();
        $stats = [
            'quotes' => Job::where('status', 'quote')->count(),
            'scheduled' => Job::where('status', 'scheduled')->count(),
            'completed' => Job::where('status', 'completed')->count(),
            'paid' => Job::where('status', 'paid')->count(),
            'outstanding' => (float) Job::where('balance', '>', 0)->sum('balance'),
            'collected' => $collectionTotal,
        ];

        return view('dashboard', compact(
            'jobs',
            'todayJobs',
            'stats',
            'payments',
            'collectionTotal',
            'collectionCount',
            'from',
            'to',
            'filters',
        ));
    }
}
