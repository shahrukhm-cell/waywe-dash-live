<?php

namespace App\Livewire\Pages;

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('components.app-layout')]
class CustomerIndexPage extends AppPage
{
    use WithPagination;

    public string $search = '';

    public string $phone = '';

    public function mount(): void
    {
        $this->search = (string) request()->query('search', '');
        $this->phone = (string) request()->query('phone', '');
    }

    public function render()
    {
        $customers = Customer::query()
            ->when($this->phone !== '', fn ($query) => $query->where('phone', 'like', '%' . $this->phone . '%'))
            ->when($this->search !== '', function ($query) {
                $search = $this->search;

                $query->where(function ($customers) use ($search) {
                    $customers->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('business', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15);

        return view('customers.index', ['customers' => $customers, 'search' => $this->search]);
    }
}
