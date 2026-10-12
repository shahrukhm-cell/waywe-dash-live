<?php

namespace App\Livewire\Pages;

use App\Models\Customer;
use App\Models\Job;

abstract class JobFormPage extends AppPage
{
    public array $form = [];

    protected function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'quote_estimate_number' => ['nullable', 'string', 'max:255'],
            'po_number' => ['nullable', 'string', 'max:255'],
            'confirmed_date' => ['nullable', 'date'],
            'booked_date' => ['required', 'date'],
            'job_type' => ['required', 'in:estimate,job,redo,pending,adjustment,delivery'],
            'site_full_name' => ['nullable', 'string', 'max:255'],
            'site_telephone' => ['required', 'string', 'max:255'],
            'site_business' => ['nullable', 'string', 'max:255'],
            'site_title' => ['nullable', 'string', 'max:255'],
            'site_address' => ['nullable', 'string', 'max:255'],
            'site_city' => ['nullable', 'string', 'max:255'],
            'site_state' => ['nullable', 'string', 'max:255'],
            'site_postal_code' => ['nullable', 'string', 'max:255'],
            'billing_full_name' => ['nullable', 'string', 'max:255'],
            'billing_telephone' => ['nullable', 'string', 'max:255'],
            'billing_business' => ['nullable', 'string', 'max:255'],
            'billing_title' => ['nullable', 'string', 'max:255'],
            'billing_address' => ['nullable', 'string', 'max:255'],
            'billing_city' => ['nullable', 'string', 'max:255'],
            'billing_state' => ['nullable', 'string', 'max:255'],
            'billing_postal_code' => ['nullable', 'string', 'max:50'],
            'crew_route' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'source_percent' => ['nullable', 'numeric', 'min:0'],
            'source_amount' => ['nullable', 'numeric', 'min:0'],
            'agent' => ['nullable', 'string', 'max:255'],
            'agent_percent' => ['nullable', 'numeric', 'min:0'],
            'agent_amount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'other_tax' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.area_item' => ['nullable', 'string', 'max:255'],
            'items.*.length' => ['nullable', 'numeric', 'min:0'],
            'items.*.width' => ['nullable', 'numeric', 'min:0'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.time' => ['nullable', 'date_format:H:i'],
            'items.*.amount' => ['nullable', 'numeric', 'min:0'],
            'scheduled_date' => ['nullable', 'date'],
            'scheduled_time' => ['nullable', 'date_format:H:i'],
            'best_time' => ['nullable', 'date_format:H:i'],
            'technicians' => ['nullable', 'array'],
            'technicians.*.technician_name' => ['nullable', 'string', 'max:255'],
            'technicians.*.time' => ['nullable', 'date_format:H:i'],
            'technicians.*.amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function validateForm(): array
    {
        $rules = [];
        foreach ($this->rules() as $field => $fieldRules) {
            $rules["form.{$field}"] = $fieldRules;
        }

        return $this->validate($rules)['form'];
    }

    public function addItem(): void
    {
        $this->form['items'][] = $this->emptyItem();
    }

    public function removeItem(int $index): void
    {
        unset($this->form['items'][$index]);
        $this->form['items'] = array_values($this->form['items']);
    }

    protected function emptyItem(): array
    {
        return [
            'area_item' => '',
            'length' => '',
            'width' => '',
            'description' => '',
            'unit' => '1',
            'quantity' => '1',
            'time' => '',
            'amount' => '',
        ];
    }

    protected function resolveCustomer(array $form): ?Customer
    {
        if (! empty($form['customer_id'])) {
            return Customer::find($form['customer_id']);
        }

        $phone = trim((string) ($form['site_telephone'] ?? $form['customer_phone'] ?? ''));
        if ($phone === '') {
            return null;
        }

        $parts = preg_split('/\s+/', trim((string) ($form['site_full_name'] ?? '')), 2) ?: [];

        return Customer::firstOrCreate(
            ['phone' => $phone],
            [
                'first_name' => $parts[0] ?? '',
                'last_name' => $parts[1] ?? '',
                'business' => $form['site_business'] ?? null,
                'title' => $form['site_title'] ?? null,
                'address' => $form['site_address'] ?? null,
                'city' => $form['site_city'] ?? null,
                'state' => $form['site_state'] ?? null,
                'postal_code' => $form['site_postal_code'] ?? null,
            ],
        );
    }

    protected function storeItems(Job $job, array $items): void
    {
        foreach ($items as $item) {
            if (! is_array($item) || (empty($item['description']) && empty($item['area_item']))) {
                continue;
            }

            $job->lineItems()->create([
                'area_item' => $item['area_item'] ?? null,
                'length' => (float) ($item['length'] ?? 0),
                'width' => (float) ($item['width'] ?? 0),
                'description' => $item['description'] ?? null,
                'unit' => $item['unit'] ?? '1',
                'quantity' => isset($item['length'], $item['width']) && $item['length'] !== '' && $item['width'] !== ''
                    ? round((float) $item['length'] * (float) $item['width'], 2)
                    : (float) ($item['quantity'] ?? 1),
                'time' => $item['time'] ?? null,
                'amount' => (float) ($item['amount'] ?? 0),
            ]);
        }
    }

    protected function storeTechnicians(Job $job, array $technicians): void
    {
        $job->technicians()->delete();

        foreach ($technicians as $technician) {
            if (! is_array($technician) || trim((string) ($technician['technician_name'] ?? '')) === '') {
                continue;
            }

            $job->technicians()->create([
                'technician_name' => trim($technician['technician_name']),
                'time' => $technician['time'] ?? null,
                'amount' => $technician['amount'] ?? 0,
            ]);
        }
    }

    protected function refreshTotals(Job $job): void
    {
        $subtotal = (float) $job->lineItems()->sum('amount');
        $total = $subtotal + (float) $job->tax + (float) $job->other_tax;
        $paid = (float) $job->payments()->sum('amount');

        $job->update([
            'subtotal' => $subtotal,
            'total' => $total,
            'invoice_total' => $total,
            'amount_paid' => $paid,
            'balance' => max($total - $paid, 0),
            'status' => $paid >= $total && $paid > 0 ? 'paid' : ($job->status === 'paid' ? 'completed' : $job->status),
        ]);
    }

    protected function firstItemTime(array $items): ?string
    {
        foreach ($items as $item) {
            if (is_array($item) && ! empty($item['time'])) {
                return $item['time'];
            }
        }

        return null;
    }

    protected function quoteNumber(): string
    {
        return 'Q-'.now()->format('YmdHis');
    }
}
