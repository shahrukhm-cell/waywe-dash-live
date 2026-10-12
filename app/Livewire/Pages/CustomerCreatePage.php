<?php

namespace App\Livewire\Pages;

use App\Models\Customer;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;

#[Layout('components.app-layout')]
class CustomerCreatePage extends AppPage
{
    public array $form = [
        'first_name' => '',
        'last_name' => '',
        'phone' => '',
        'business' => '',
        'title' => '',
        'address' => '',
        'city' => '',
        'state' => '',
        'postal_code' => '',
    ];

    public function save(): mixed
    {
        $rules = [];
        foreach ($this->rules() as $field => $fieldRules) {
            $rules["form.{$field}"] = $fieldRules;
        }
        $validated = $this->validate($rules)['form'];
        $customer = Customer::create($validated);

        session()->flash('success', 'Customer saved successfully.');

        return $this->redirectRoute('jobs.create', ['phone' => $customer->phone]);
    }

    public function render()
    {
        return view('customers.create');
    }

    private function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', Rule::unique('customers', 'phone')],
            'business' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:50'],
        ];
    }
}
