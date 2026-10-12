<?php

namespace App\Livewire\Pages;

use App\Models\Job;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;

#[Layout('components.app-layout')]
class JobEditPage extends JobFormPage
{
    #[Locked]
    public int $jobId;

    public function mount(Job $job): void
    {
        $this->jobId = $job->id;
        $this->form = $job->only([
            'quote_estimate_number',
            'po_number',
            'job_type',
            'tax',
            'other_tax',
            'confirmed_date',
            'booked_date',
            'best_time',
            'site_full_name',
            'site_telephone',
            'site_business',
            'site_title',
            'site_address',
            'site_city',
            'site_state',
            'site_postal_code',
            'billing_full_name',
            'billing_telephone',
            'billing_business',
            'billing_title',
            'billing_address',
            'billing_city',
            'billing_state',
            'billing_postal_code',
            'crew_route',
            'source',
            'source_percent',
            'source_amount',
            'agent',
            'agent_percent',
            'agent_amount',
            'notes',
        ]);
        $this->form['booked_date'] = $job->booked_date?->toDateString() ?? $job->scheduled_date?->toDateString() ?? '';
        $this->form['confirmed_date'] = $job->confirmed_date?->toDateString() ?? '';
        $this->form['items'] = $job->lineItems->map(fn ($item) => $item->only([
            'area_item', 'length', 'width', 'description', 'unit', 'quantity', 'time', 'amount',
        ]))->values()->all() ?: [$this->emptyItem()];
    }

    public function save(): mixed
    {
        $job = Job::findOrFail($this->jobId);
        $validated = $this->validateForm();
        $bookedDate = $validated['booked_date'];
        $scheduledTime = $validated['scheduled_time'] ?? $job->scheduled_time;
        $fields = [
            'quote_estimate_number',
            'po_number',
            'confirmed_date',
            'site_full_name',
            'site_telephone',
            'site_business',
            'site_title',
            'site_address',
            'site_city',
            'site_state',
            'site_postal_code',
            'billing_full_name',
            'billing_telephone',
            'billing_business',
            'billing_title',
            'billing_address',
            'billing_city',
            'billing_state',
            'billing_postal_code',
            'crew_route',
            'best_time',
            'source',
            'source_percent',
            'source_amount',
            'agent',
            'agent_percent',
            'agent_amount',
            'tax',
            'other_tax',
        ];
        $attributes = [];
        foreach ($fields as $field) {
            $attributes[$field] = $validated[$field] ?? $job->{$field};
        }
        $attributes = [
            ...$attributes,
            'job_type' => $validated['job_type'],
            'status' => $job->status,
            'booked_date' => $bookedDate,
            'scheduled_day' => date('D', strtotime($bookedDate)),
            'scheduled_date' => $bookedDate,
            'scheduled_time' => $scheduledTime,
            'notes' => $validated['notes'] ?? null,
        ];

        DB::transaction(function () use ($job, $attributes, $validated): void {
            $job->update($attributes);
            $job->lineItems()->delete();
            $this->storeItems($job, $validated['items'] ?? []);
            if (array_key_exists('technicians', $validated)) {
                $this->storeTechnicians($job, $validated['technicians']);
            }
            $this->refreshTotals($job);
        });
        session()->flash('success', 'Job updated successfully.');

        return $this->redirectRoute('jobs.show', ['job' => $job]);
    }

    public function render()
    {
        $job = Job::with(['customer', 'lineItems', 'reminders', 'technicians'])->findOrFail($this->jobId);

        return view('jobs.edit', compact('job'));
    }
}
