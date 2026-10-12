<?php

namespace App\Livewire\Pages;

use App\Models\Job;
use App\Models\JobPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;

#[Layout('components.app-layout')]
class JobShowPage extends AppPage
{
    #[Locked]
    public int $jobId;

    public array $schedule = [];

    public array $payment = [];

    public string $note = '';

    public function mount(Job $job): void
    {
        $this->jobId = $job->id;
        $this->schedule = [
            'crew_route' => $job->crew_route ?? '',
            'scheduled_date' => $job->scheduled_date?->format('Y-m-d') ?? '',
            'scheduled_time' => $job->scheduled_time ?? '',
            'best_time' => $job->best_time ?? '',
            'technicians' => $job->technicians->map(fn ($technician) => $technician->only([
                'technician_name', 'time', 'amount',
            ]))->values()->all() ?: [['technician_name' => '', 'time' => '', 'amount' => '']],
        ];
        $this->payment = [
            'payment_method' => '',
            'amount' => number_format((float) $job->balance, 2, '.', ''),
            'transaction_date' => today()->toDateString(),
            'check_number' => '',
            'check_date' => '',
            'account_reference' => '',
            'card_last_four' => '',
            'card_expiry_month' => '',
            'card_expiry_year' => '',
            'explanation' => '',
            'notes' => '',
        ];
    }

    public function addTechnician(): void
    {
        $this->schedule['technicians'][] = ['technician_name' => '', 'time' => '', 'amount' => ''];
    }

    public function removeTechnician(int $index): void
    {
        unset($this->schedule['technicians'][$index]);
        $this->schedule['technicians'] = array_values($this->schedule['technicians']);
    }

    public function confirmQuote(): mixed
    {
        $job = $this->job();

        if (! in_array($job->status, ['quote', 'draft'], true)) {
            session()->flash('success', 'This job has already been confirmed.');

            return $this->redirectRoute('jobs.show', ['job' => $job]);
        }

        $job->update([
            'job_number' => $job->job_number ?: $this->generateJobNumber(),
            'status' => 'approved',
            'confirmed_date' => today()->toDateString(),
        ]);
        session()->flash('success', 'Quote approved. The job number has been assigned.');

        return $this->redirectRoute('jobs.show', ['job' => $job]);
    }

    public function schedule(): mixed
    {
        $job = $this->job();
        abort_unless(in_array($job->status, ['approved', 'scheduled', 'dispatched'], true), 403, 'Approve the quote before scheduling the job.');

        $validated = $this->validate([
            'schedule.crew_route' => ['nullable', 'string', 'max:255'],
            'schedule.scheduled_date' => ['required', 'date'],
            'schedule.scheduled_time' => ['nullable', 'date_format:H:i'],
            'schedule.best_time' => ['nullable', 'date_format:H:i'],
            'schedule.technicians' => ['nullable', 'array'],
            'schedule.technicians.*.technician_name' => ['nullable', 'string', 'max:255'],
            'schedule.technicians.*.time' => ['nullable', 'date_format:H:i'],
            'schedule.technicians.*.amount' => ['nullable', 'numeric', 'min:0'],
        ])['schedule'];

        DB::transaction(function () use ($job, $validated): void {
            $job->update([
                'status' => 'scheduled',
                'crew_route' => $validated['crew_route'] ?? null,
                'booked_date' => $validated['scheduled_date'],
                'scheduled_day' => date('D', strtotime($validated['scheduled_date'])),
                'scheduled_date' => $validated['scheduled_date'],
                'scheduled_time' => $validated['scheduled_time'] ?? null,
                'best_time' => $validated['best_time'] ?? null,
            ]);
            $job->technicians()->delete();
            foreach ($validated['technicians'] ?? [] as $technician) {
                if (! is_array($technician) || trim((string) ($technician['technician_name'] ?? '')) === '') {
                    continue;
                }
                $job->technicians()->create([
                    'technician_name' => trim($technician['technician_name']),
                    'time' => $technician['time'] ?? null,
                    'amount' => $technician['amount'] ?? 0,
                ]);
            }
        });

        session()->flash('success', 'Schedule and technician assignments saved.');

        return $this->redirectRoute('jobs.show', ['job' => $job]);
    }

    public function markDispatched(): mixed
    {
        $job = $this->job();
        abort_unless(in_array($job->status, ['scheduled', 'dispatched'], true), 403, 'Schedule the approved job before dispatching it.');
        $job->update(['status' => 'dispatched']);
        session()->flash('success', 'Job marked as dispatched.');

        return $this->redirectRoute('jobs.show', ['job' => $job]);
    }

    public function complete(): mixed
    {
        $job = $this->job();
        abort_unless(in_array($job->status, ['scheduled', 'dispatched', 'completed'], true), 403, 'The job must be scheduled or dispatched before it can be completed.');
        $job->update(['status' => 'completed']);
        session()->flash('success', 'Job marked complete. You can now record payments and issue the invoice.');

        return $this->redirectRoute('jobs.show', ['job' => $job]);
    }

    public function recordPayment(): mixed
    {
        $job = $this->job();
        $availableBalance = max((float) $job->total - (float) $job->payments()->sum('amount'), 0);
        $validated = $this->validate([
            'payment.payment_method' => ['required', Rule::in(['check', 'charge', 'card', 'cash', 'other'])],
            'payment.amount' => ['required', 'numeric', 'gt:0', 'lte:'.number_format($availableBalance, 2, '.', '')],
            'payment.transaction_date' => ['required', 'date'],
            'payment.check_number' => ['nullable', 'string', 'max:100'],
            'payment.check_date' => ['nullable', 'required_if:payment.payment_method,check', 'date'],
            'payment.account_reference' => [
                'nullable',
                'string',
                'max:40',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value !== null && ctype_digit((string) $value) && strlen((string) $value) !== 4) {
                        $fail('For account details, enter only the last four digits, not a full account number.');
                    }
                },
            ],
            'payment.card_last_four' => ['required_if:payment.payment_method,card,charge', 'nullable', 'digits:4'],
            'payment.card_expiry_month' => ['nullable', 'required_with:payment.card_expiry_year', 'integer', 'between:1,12'],
            'payment.card_expiry_year' => ['nullable', 'required_with:payment.card_expiry_month', 'integer', 'between:'.now()->year.','.(now()->year + 20)],
            'payment.explanation' => ['nullable', 'string', 'max:2000'],
            'payment.notes' => ['nullable', 'string', 'max:2000'],
        ])['payment'];

        $payment = DB::transaction(function () use ($job, $validated): JobPayment {
            $lockedJob = Job::whereKey($job->id)->lockForUpdate()->firstOrFail();
            $currentBalance = max((float) $lockedJob->total - (float) $lockedJob->payments()->sum('amount'), 0);

            if ((float) $validated['amount'] > $currentBalance) {
                throw ValidationException::withMessages([
                    'amount' => 'This payment is greater than the remaining invoice balance.',
                ]);
            }

            $payment = $lockedJob->payments()->create([
                ...$validated,
                'receipt_no' => 'TMP-'.bin2hex(random_bytes(16)),
            ]);
            $payment->update([
                'receipt_no' => 'R-'.now()->format('Y').'-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT),
            ]);

            $paid = (float) $lockedJob->payments()->sum('amount');
            $total = (float) $lockedJob->total;
            $lockedJob->update([
                'invoice_number' => $lockedJob->invoice_number ?: 'INV-'.now()->format('Y').'-'.str_pad((string) $lockedJob->id, 6, '0', STR_PAD_LEFT),
                'invoice_total' => $total,
                'amount_paid' => $paid,
                'balance' => max($total - $paid, 0),
                'payment_method' => $payment->payment_method,
                'payment_reference' => $payment->account_reference ?: $payment->card_last_four ?: $payment->check_number,
                'receipt_no' => $payment->receipt_no,
                'transaction_date' => $payment->transaction_date,
                'status' => $paid >= $total ? 'paid' : 'completed',
            ]);

            return $payment;
        });

        session()->flash('success', 'Payment saved. Receipt '.$payment->receipt_no.' is ready; the invoice includes the full payment history.');

        return $this->redirectRoute('jobs.show', ['job' => $job]);
    }

    public function addNote(): mixed
    {
        $validated = $this->validate(['note' => ['required', 'string', 'max:5000']]);
        $this->job()->reminders()->create($validated);
        session()->flash('success', 'Reminder added to this job.');

        return $this->redirectRoute('jobs.show', ['job' => $this->job()]);
    }

    public function delete(): mixed
    {
        $this->job()->delete();
        session()->flash('success', 'Job moved to deleted jobs.');

        return $this->redirectRoute('jobs.index');
    }

    public function render()
    {
        $job = $this->job()->load(['customer', 'lineItems', 'reminders', 'technicians', 'payments']);

        return view('jobs.show', compact('job'));
    }

    private function job(): Job
    {
        return Job::findOrFail($this->jobId);
    }

    private function generateJobNumber(): string
    {
        $highestJobNumber = Job::withTrashed()
            ->whereNotNull('job_number')
            ->pluck('job_number')
            ->map(fn ($jobNumber) => (int) filter_var($jobNumber, FILTER_SANITIZE_NUMBER_INT))
            ->max();

        return (string) (($highestJobNumber ?? 62775) + 1);
    }
}
