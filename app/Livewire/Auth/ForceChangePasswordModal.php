<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ForceChangePasswordModal extends Component
{
    public bool $showModal = false;
    public string $newPassword = '';
    public string $newPasswordConfirmation = '';

    public function mount()
    {
        $this->checkStatus();
    }

    public function checkStatus(): void
    {
        if (Auth::check() && Auth::user()->must_change_password) {
            $this->showModal = true;
        } else {
            $this->showModal = false;
        }
    }

    public function updatePassword(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $this->validate([
            'newPassword' => 'required|string|min:6|same:newPasswordConfirmation',
        ], [
            'newPassword.required' => 'Kata sandi baru wajib diisi.',
            'newPassword.min' => 'Kata sandi baru minimal 6 karakter.',
            'newPassword.same' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user->update([
            'password' => Hash::make($this->newPassword),
            'must_change_password' => false,
            'password_changed_at' => now(),
        ]);

        $this->showModal = false;
        $this->reset(['newPassword', 'newPasswordConfirmation']);

        $this->dispatch('swal:alert', [
            'icon' => 'success',
            'title' => 'Kata Sandi Berhasil Diperbarui!',
            'text' => 'Akun Anda sekarang telah diamankan dengan kata sandi baru. Selamat bekerja!',
        ]);
    }

    public function render()
    {
        return view('livewire.auth.force-change-password-modal');
    }
}
