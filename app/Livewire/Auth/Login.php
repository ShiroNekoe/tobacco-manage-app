<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected array $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        $this->addError('email', 'Email atau kata sandi yang Anda masukkan salah.');
    }

    public function loginAsRole(string $role)
    {
        $roleMap = [
            'karyawan' => ['email' => 'karyawan@tobacco.com', 'name' => 'Karyawan Shift 1', 'role' => 'karyawan'],
            'admin' => ['email' => 'admin@tobacco.com', 'name' => 'Admin MES', 'role' => 'admin'],
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
        return view('livewire.auth.login')->layout('layouts.guest', ['title' => 'Login System MES']);
    }
}
