<?php

namespace App\Livewire\Pages;

use App\Models\Customer;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;

#[Layout('components.app-layout')]
class CustomerEditPage extends AppPage
{
    #[Locked]
    public int $customerId;

    public array $form = [];

    public function mount(Customer $customer): void
    {
        $this->customerId = $customer->id;
        $this->form = $customer->only([
            'first_name',
            'last_name',
            'phone',
            'business',
            'title',
            'address',
            'city',
            'state',
            'postal_code',
        ]);
    }

    public function save(): mixed
    {
        $customer = Customer::findOrFail($this->customerId);
        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', Rule::unique('customers', 'phone')->ignore($customer->id)],
            'business' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:50'],
        ];
        $formRules = [];
        foreach ($rules as $field => $fieldRules) {
            $formRules["form.{$field}"] = $fieldRules;
        }
        $validated = $this->validate($formRules)['form'];

        $customer->update($validated);
        session()->flash('success', 'Customer updated successfully.');

        return $this->redirectRoute('customers.show', $customer);
    }

    public function render()
    {
        return view('customers.edit', ['customer' => Customer::findOrFail($this->customerId)]);
    }
}
