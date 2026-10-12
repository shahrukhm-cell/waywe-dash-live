<?php

namespace App\Livewire\Pages;

use App\Models\Job;
use Livewire\Attributes\Layout;

#[Layout('components.app-layout')]
class JobIndexPage extends AppPage
{
    public function render()
    {
        return view('jobs.index', ['jobs' => Job::with('customer')->latest()->get()]);
    }
}
