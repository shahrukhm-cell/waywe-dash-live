<?php

namespace App\Livewire\Pages;

use App\Models\Job;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;

#[Layout('components.app-layout')]
class JobCalendarPage extends AppPage
{
    public string $selectedMonth = '';

    public function mount(): void
    {
        $this->selectedMonth = (string) request()->query('month', today()->format('Y-m'));
    }

    public function changeMonth(string $month): void
    {
        if (! preg_match('/^\d{4}-\d{2}$/', $month) || ! checkdate((int) substr($month, 5, 2), 1, (int) substr($month, 0, 4))) {
            throw ValidationException::withMessages(['month' => 'Select a valid calendar month.']);
        }

        $this->selectedMonth = $month;
    }

    public function render()
    {
        $month = Carbon::createFromFormat('Y-m', $this->selectedMonth)->startOfMonth();
        $calendarStart = $month->copy()->startOfWeek(Carbon::SUNDAY);
        $calendarEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);
        $jobs = Job::with('customer')
            ->whereRaw('COALESCE(booked_date, scheduled_date) BETWEEN ? AND ?', [
                $calendarStart->toDateString(),
                $calendarEnd->toDateString(),
            ])
            ->orderByRaw('COALESCE(booked_date, scheduled_date)')
            ->orderBy('scheduled_time')
            ->get()
            ->groupBy(fn (Job $job) => ($job->booked_date ?? $job->scheduled_date)->toDateString());

        $weeks = collect();
        $cursor = $calendarStart->copy();
        while ($cursor->lte($calendarEnd)) {
            $week = collect();
            for ($day = 0; $day < 7; $day++) {
                $dayJobs = $jobs->get($cursor->toDateString(), collect());
                $week->push([
                    'date' => $cursor->copy(),
                    'jobs' => $dayJobs,
                    'count' => $dayJobs->count(),
                    'is_current_month' => $cursor->isSameMonth($month),
                    'is_today' => $cursor->isToday(),
                ]);
                $cursor->addDay();
            }
            $weeks->push($week);
        }

        $monthJobCount = $jobs->flatten(1)
            ->filter(fn (Job $job) => ($job->booked_date ?? $job->scheduled_date)->isSameMonth($month))
            ->count();

        return view('jobs.calendar', [
            'month' => $month,
            'previousMonth' => $month->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $month->copy()->addMonth()->format('Y-m'),
            'weeks' => $weeks,
            'monthJobCount' => $monthJobCount,
        ]);
    }
}
