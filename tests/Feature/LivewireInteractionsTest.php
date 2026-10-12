<?php

namespace Tests\Feature;

use App\Livewire\Pages\CustomerCreatePage;
use App\Livewire\Pages\CustomerEditPage;
use App\Livewire\Pages\JobCreatePage;
use App\Livewire\Pages\LoginPage;
use App\Livewire\Pages\LogoutButton;
use App\Models\Customer;
use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireInteractionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_out_through_the_livewire_navigation(): void
    {
        $this->actingAs(User::factory()->create());
        request()->setLaravelSession(app('session')->driver());

        Livewire::test(LogoutButton::class)
            ->call('logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_user_can_log_in_through_the_livewire_form(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('secret-password'),
        ]);

        request()->setLaravelSession(app('session')->driver());

        Livewire::test(LoginPage::class)
            ->set('email', $user->email)
            ->set('password', 'secret-password')
            ->call('login')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_customer_can_be_updated_through_the_livewire_form(): void
    {
        $this->actingAs(User::factory()->create());
        $customer = Customer::create([
            'first_name' => 'Morgan',
            'last_name' => 'Green',
            'phone' => '555-0101',
        ]);

        Livewire::test(CustomerEditPage::class, ['customer' => $customer])
            ->set('form', [
                'first_name' => 'Morgan',
                'last_name' => 'Green Updated',
                'phone' => '555-0101',
                'city' => 'Queens',
            ])
            ->call('save')
            ->assertRedirect(route('customers.show', $customer));

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'last_name' => 'Green Updated',
            'city' => 'Queens',
        ]);
    }

    public function test_customer_can_be_created_through_the_livewire_form(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CustomerCreatePage::class)
            ->set('form.first_name', 'Casey')
            ->set('form.last_name', 'Brown')
            ->set('form.phone', '555-0190')
            ->set('form.city', 'Queens')
            ->call('save')
            ->assertRedirect(route('jobs.create', ['phone' => '555-0190']));

        $this->assertDatabaseHas('customers', [
            'first_name' => 'Casey',
            'last_name' => 'Brown',
            'phone' => '555-0190',
            'city' => 'Queens',
        ]);
    }

    public function test_repeatable_job_items_are_managed_by_the_livewire_component(): void
    {
        Livewire::test(JobCreatePage::class)
            ->call('addItem')
            ->assertSet('form.items.1.unit', '1')
            ->call('removeItem', 0)
            ->assertSet('form.items.0.unit', '1');
    }

    public function test_job_can_be_created_through_the_livewire_form(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(JobCreatePage::class)
            ->set('form', [
                'site_full_name' => 'Taylor Customer',
                'site_telephone' => '555-0100',
                'booked_date' => today()->toDateString(),
                'job_type' => 'estimate',
                'items' => [[
                    'area_item' => 'Area rug',
                    'quantity' => 1,
                    'amount' => 150,
                ]],
            ])
            ->call('save')
            ->assertRedirect();

        $this->assertSame('quote', Job::where('site_full_name', 'Taylor Customer')->firstOrFail()->status);
    }
}
