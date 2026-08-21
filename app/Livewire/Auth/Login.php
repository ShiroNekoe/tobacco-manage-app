<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Login extends Component
{
    public string $mode = 'email'; // 'email' or 'karyawan'
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function setMode(string $mode)
    {
        $this->mode = $mode;
        $this->resetErrorBag();
    }

    public function login()
    {
        if ($this->mode === 'karyawan') {
            $this->validate([
                'name' => 'required|string',
                'password' => 'required',
            ], [
                'name.required' => 'Nama Karyawan wajib diisi.',
                'password.required' => 'Kata sandi wajib diisi.',
            ]);

            // Exact or case-insensitive search by name
            $user = User::where('name', $this->name)->first();

            if ($user && Hash::check($this->password, $user->password)) {
                Auth::login($user, $this->remember);
                session()->regenerate();

                if ($user->isCustomer()) {
                    return redirect()->intended(route('customer.dashboard'));
                } elseif ($user->isAdmin() || $user->isSupervisor()) {
                    return redirect()->intended(route('admin.batches'));
                } else {
                    return redirect()->intended(route('karyawan.weighing'));
                }
            }

            $this->addError('name', 'Nama Karyawan atau Kata Sandi yang Anda masukkan salah.');
            return;
        }

        // Email / General Mode
        $this->validate([
            'email' => 'required',
            'password' => 'required',
        ], [
            'email.required' => 'Email atau Nama wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $user = User::where('email', $this->email)
            ->orWhere('name', $this->email)
            ->first();

        if ($user && Hash::check($this->password, $user->password)) {
            Auth::login($user, $this->remember);
            session()->regenerate();

            if ($user->isAdmin() || $user->isSupervisor() || $user->isItSupport()) {
                return redirect()->intended(route('admin.batches'));
            } elseif ($user->isCustomer()) {
                return redirect()->intended(route('customer.dashboard'));
            } else {
                return redirect()->intended(route('karyawan.weighing'));
            }
        }

        $this->addError('email', 'Email / Nama atau Kata Sandi yang Anda masukkan salah.');
    }

    public function loginAsRole(string $role)
    {
        $roleMap = [
            'karyawan' => ['email' => 'karyawan@tobacco.com', 'name' => 'Karyawan Shift 1', 'role' => 'karyawan'],
            'admin' => ['email' => 'admin@tobacco.com', 'name' => 'Admin TPMS', 'role' => 'admin'],
            'supervisor' => ['email' => 'supervisor@tobacco.com', 'name' => 'Supervisor QC', 'role' => 'supervisor'],
            'customer' => ['email' => 'customer@tobacco.com', 'name' => 'Customer Portal User', 'role' => 'customer'],
        ];

        if (! isset($roleMap[$role])) {
            return;
        }

        $target = $roleMap[$role];

        $user = User::firstOrCreate(
            ['email' => $target['email']],
            [
                'name' => $target['name'],
                'role' => $target['role'],
                'shift' => 'Shift 1',
                'group' => 'Group A',
                'password' => Hash::make('password'),
            ]
        );

        Auth::login($user);
        session()->regenerate();

        if ($role === 'customer') {
            return redirect()->route('customer.dashboard');
        } elseif (in_array($role, ['admin', 'supervisor'])) {
            return redirect()->route('admin.batches');
        } else {
            return redirect()->route('karyawan.weighing');
        }
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('layouts.guest', ['title' => 'Login System TPMS']);
    }
}
