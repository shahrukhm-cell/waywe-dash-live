<?php

namespace Tests\Feature;

use App\Livewire\Pages\DashboardPage;
use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Recent jobs')
            ->assertSee('Today’s schedule')
            ->assertSee('Outstanding balance')
            ->assertSee('Payment collections');
    }

    public function test_collection_filters_are_applied_through_livewire(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(DashboardPage::class)
            ->call('showAllDates')
            ->assertSee('id="payment-collections"', false)
            ->assertSee('Recent jobs');
    }

    public function test_collection_filters_can_be_loaded_from_the_url(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::withQueryParams([
            'from' => today()->subDays(4)->toDateString(),
            'to' => today()->toDateString(),
            'payment_method' => 'cash',
            'collection_status' => 'outstanding',
            'all_dates' => '1',
        ])->test(DashboardPage::class)
            ->assertSet('from', today()->subDays(4)->toDateString())
            ->assertSet('to', today()->toDateString())
            ->assertSet('paymentMethod', 'cash')
            ->assertSet('collectionStatus', 'outstanding')
            ->assertSet('allDates', true);
    }

    public function test_collection_report_keeps_payments_for_deleted_jobs_and_links_to_archived_details(): void
    {
        $this->actingAs(User::factory()->create());
        $job = Job::create([
            'job_number' => 'J-ARCHIVED',
            'job_type' => 'job',
            'status' => 'completed',
            'site_full_name' => 'Archived Customer',
            'total' => 100,
            'amount_paid' => 25,
            'balance' => 75,
        ]);
        $job->payments()->create([
            'receipt_no' => 'R-2026-000201',
            'payment_method' => 'cash',
            'amount' => 25,
            'transaction_date' => today()->toDateString(),
        ]);
        $job->delete();

        $this->get(route('dashboard', ['all_dates' => 1]))
            ->assertOk()
            ->assertSee(route('jobs.deleted.show', $job), false)
            ->assertSee('R-2026-000201')
            ->assertSee('Archived Customer')
            ->assertSee('Archived')
            ->assertDontSee('Missing required parameter');
    }

    public function test_dashboard_collection_report_filters_payments_by_date_method_and_job_status(): void
    {
        $this->actingAs(User::factory()->create());

        $outstandingJob = Job::create([
            'status' => 'completed',
            'job_type' => 'job',
            'total' => 100,
            'balance' => 75,
        ]);
        $paidJob = Job::create([
            'status' => 'paid',
            'job_type' => 'job',
            'total' => 50,
            'amount_paid' => 50,
            'balance' => 0,
        ]);
        $olderJob = Job::create([
            'status' => 'paid',
            'job_type' => 'job',
            'total' => 100,
            'amount_paid' => 100,
            'balance' => 0,
        ]);
        $scheduledJob = Job::create([
            'status' => 'scheduled',
            'job_type' => 'job',
            'total' => 30,
            'amount_paid' => 10,
            'balance' => 20,
        ]);

        $outstandingJob->payments()->create([
            'receipt_no' => 'R-2026-000101',
            'payment_method' => 'cash',
            'amount' => 25,
            'transaction_date' => today()->toDateString(),
        ]);
        $paidJob->payments()->create([
            'receipt_no' => 'R-2026-000102',
            'payment_method' => 'card',
            'amount' => 50,
            'transaction_date' => today()->subDay()->toDateString(),
        ]);
        $olderJob->payments()->create([
            'receipt_no' => 'R-2026-000103',
            'payment_method' => 'cash',
            'amount' => 100,
            'transaction_date' => today()->subMonth()->toDateString(),
        ]);
        $scheduledJob->payments()->create([
            'receipt_no' => 'R-2026-000104',
            'payment_method' => 'cash',
            'amount' => 10,
            'transaction_date' => today()->toDateString(),
        ]);

        $response = Livewire::test(DashboardPage::class)
            ->set('from', today()->subDays(2)->toDateString())
            ->set('to', today()->toDateString())
            ->call('applyFilters')
            ->assertSee('id="payment-collections"', false)
            ->assertSee('data-total="85.00"', false)
            ->assertSee('Payments in this result')
            ->assertSee('Filtered collection total')
            ->assertSee('R-2026-000101')
            ->assertSee('R-2026-000102')
            ->assertSee('R-2026-000104')
            ->assertDontSee('R-2026-000103');

        Livewire::test(DashboardPage::class)
            ->call('showAllDates')
            ->assertSee('data-total="185.00"', false)
            ->assertSee('$185.00');

        Livewire::test(DashboardPage::class)
            ->set('from', today()->subDays(2)->toDateString())
            ->set('to', today()->toDateString())
            ->set('paymentMethod', 'cash')
            ->set('collectionStatus', 'outstanding')
            ->call('applyFilters')
            ->assertSee('data-total="35.00"', false)
            ->assertSee('R-2026-000101')
            ->assertSee('R-2026-000104')
            ->assertDontSee('R-2026-000102');

        Livewire::test(DashboardPage::class)
            ->set('from', today()->subDays(2)->toDateString())
            ->set('to', today()->toDateString())
            ->set('collectionStatus', 'paid')
            ->call('applyFilters')
            ->assertSee('data-total="50.00"', false)
            ->assertSee('R-2026-000102')
            ->assertDontSee('R-2026-000101');
    }
}
