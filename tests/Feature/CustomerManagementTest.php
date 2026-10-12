<?php

namespace Tests\Feature;

use App\Livewire\Pages\CustomerEditPage;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_be_edited_from_the_customer_list(): void
    {
        $this->actingAs(User::factory()->create());
        $customer = Customer::create([
            'first_name' => 'Morgan',
            'last_name' => 'Green',
            'phone' => '555-0101',
            'city' => 'Brooklyn',
        ]);

        $this->get(route('customers.index'))
            ->assertOk()
            ->assertSee(route('customers.edit', $customer), false);

        $this->get(route('customers.edit', $customer))
            ->assertOk()
            ->assertSee('Morgan')
            ->assertSee('Brooklyn');

        Livewire::test(CustomerEditPage::class, ['customer' => $customer])
            ->set('form.first_name', 'Morgan')
            ->set('form.last_name', 'Green Updated')
            ->set('form.phone', '555-0101')
            ->set('form.business', 'Green Services')
            ->set('form.city', 'Queens')
            ->call('save')
            ->assertRedirect(route('customers.show', $customer));

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'last_name' => 'Green Updated',
            'business' => 'Green Services',
            'city' => 'Queens',
        ]);
    }

    public function test_customer_phone_must_remain_unique_when_editing(): void
    {
        $this->actingAs(User::factory()->create());
        $customer = Customer::create(['first_name' => 'First', 'phone' => '555-0101']);
        $otherCustomer = Customer::create(['first_name' => 'Second', 'phone' => '555-0102']);

        Livewire::test(CustomerEditPage::class, ['customer' => $customer])
            ->set('form.first_name', 'First')
            ->set('form.phone', $otherCustomer->phone)
            ->call('save')
            ->assertHasErrors('form.phone');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'phone' => '555-0101',
        ]);
    }
}
