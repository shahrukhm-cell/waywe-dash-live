<?php

namespace App\Livewire\Pages;

use Illuminate\Support\Facades\Auth;

class LogoutButton extends AppPage
{
    public string $buttonClass = '';

    public string $formClass = '';

    public function mount(string $buttonClass = '', string $formClass = ''): void
    {
        $this->buttonClass = $buttonClass;
        $this->formClass = $formClass;
    }

    public function logout(): mixed
    {
        request()->setLaravelSession(app('session')->driver());
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return $this->redirectRoute('login');
    }

    public function render()
    {
        return view('livewire.pages.logout-button');
    }
}
