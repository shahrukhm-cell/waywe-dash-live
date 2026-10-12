<?php

namespace App\Livewire\Pages;

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;

#[Layout('components.app-layout')]
class CustomerShowPage extends AppPage
{
    #[Locked]
    public int $customerId;

    public function mount(Customer $customer): void
    {
        $this->customerId = $customer->id;
    }

    public function render()
    {
        $customer = Customer::with(['jobs' => fn ($query) => $query
            ->with(['lineItems', 'technicians', 'payments', 'reminders'])
            ->latest()])
            ->findOrFail($this->customerId);

        return view('customers.show', compact('customer'));
    }
}
