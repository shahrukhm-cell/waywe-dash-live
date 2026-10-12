<?php

namespace App\Livewire\Pages;

use App\Models\Customer;
use App\Models\Job;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

#[Layout('components.app-layout')]
class JobCreatePage extends JobFormPage
{
    public string $phone = '';

    public function mount(): void
    {
        $this->phone = (string) request()->query('phone', '');
        $customer = $this->phone !== '' ? Customer::where('phone', $this->phone)->first() : null;
        $this->form = [
            'customer_id' => $customer?->id,
            'site_full_name' => $customer?->full_name ?? '',
            'site_telephone' => $customer?->phone ?? $this->phone,
            'site_business' => $customer?->business ?? '',
            'site_title' => $customer?->title ?? '',
            'site_address' => $customer?->address ?? '',
            'site_city' => $customer?->city ?? '',
            'site_state' => $customer?->state ?? '',
            'site_postal_code' => $customer?->postal_code ?? '',
            'billing_full_name' => $customer?->full_name ?? '',
            'billing_telephone' => $customer?->phone ?? '',
            'billing_business' => $customer?->business ?? '',
            'billing_title' => $customer?->title ?? '',
            'billing_address' => $customer?->address ?? '',
            'billing_city' => $customer?->city ?? '',
            'billing_state' => $customer?->state ?? '',
            'billing_postal_code' => $customer?->postal_code ?? '',
            'quote_estimate_number' => '',
            'po_number' => '',
            'job_type' => 'estimate',
            'confirmed_date' => today()->toDateString(),
            'booked_date' => '',
            'items' => [$this->emptyItem()],
            'crew_route' => '',
            'best_time' => '',
            'source' => '',
            'agent' => '',
            'notes' => '',
            'tax' => 0,
            'other_tax' => 0,
        ];
    }

    public function save(): mixed
    {
        $validated = $this->validateForm();
        $bookedDate = $validated['booked_date'];
        $scheduledTime = $this->firstItemTime($validated['items'] ?? []) ?? ($validated['scheduled_time'] ?? null);

        $job = DB::transaction(function () use ($validated, $bookedDate, $scheduledTime): Job {
            $customer = $this->resolveCustomer($validated);
            $job = Job::create([
                'customer_id' => $customer?->id,
                'quote_estimate_number' => $validated['quote_estimate_number'] ?? $this->quoteNumber(),
                'po_number' => $validated['po_number'] ?? null,
                'job_type' => $validated['job_type'],
                'status' => 'quote',
                'confirmed_date' => today()->toDateString(),
                'booked_date' => $bookedDate,
                'site_full_name' => $validated['site_full_name'] ?? $customer?->full_name,
                'site_telephone' => $validated['site_telephone'] ?? $customer?->phone,
                'site_business' => $validated['site_business'] ?? $customer?->business,
                'site_title' => $validated['site_title'] ?? $customer?->title,
                'site_address' => $validated['site_address'] ?? $customer?->address,
                'site_city' => $validated['site_city'] ?? $customer?->city,
                'site_state' => $validated['site_state'] ?? $customer?->state,
                'site_postal_code' => $validated['site_postal_code'] ?? $customer?->postal_code,
                'billing_full_name' => $validated['billing_full_name'] ?? $validated['site_full_name'] ?? $customer?->full_name,
                'billing_telephone' => $validated['billing_telephone'] ?? $validated['site_telephone'] ?? $customer?->phone,
                'billing_business' => $validated['billing_business'] ?? $validated['site_business'] ?? $customer?->business,
                'billing_title' => $validated['billing_title'] ?? $validated['site_title'] ?? $customer?->title,
                'billing_address' => $validated['billing_address'] ?? $validated['site_address'] ?? $customer?->address,
                'billing_city' => $validated['billing_city'] ?? $validated['site_city'] ?? $customer?->city,
                'billing_state' => $validated['billing_state'] ?? $validated['site_state'] ?? $customer?->state,
                'billing_postal_code' => $validated['billing_postal_code'] ?? $validated['site_postal_code'] ?? $customer?->postal_code,
                'crew_route' => $validated['crew_route'] ?? null,
                'scheduled_day' => date('D', strtotime($bookedDate)),
                'scheduled_date' => $bookedDate,
                'scheduled_time' => $scheduledTime,
                'best_time' => $validated['best_time'] ?? null,
                'source' => $validated['source'] ?? null,
                'source_percent' => $validated['source_percent'] ?? 0,
                'source_amount' => $validated['source_amount'] ?? 0,
                'agent' => $validated['agent'] ?? null,
                'agent_percent' => $validated['agent_percent'] ?? 0,
                'agent_amount' => $validated['agent_amount'] ?? 0,
                'tax' => $validated['tax'] ?? 0,
                'other_tax' => $validated['other_tax'] ?? 0,
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->storeItems($job, $validated['items'] ?? []);
            if (isset($validated['technicians'])) {
                $this->storeTechnicians($job, $validated['technicians']);
            }
            $this->refreshTotals($job);

            return $job;
        });
        session()->flash('success', 'Quote saved. Download it from the job details when ready.');

        return $this->redirectRoute('jobs.show', ['job' => $job]);
    }

    public function render()
    {
        $phone = trim($this->phone);
        $customer = $phone !== '' ? Customer::where('phone', $phone)->first() : null;

        return view('jobs.create', compact('customer', 'phone'));
    }
}
