<?php

namespace App\Livewire\Admin;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UserManagement extends Component
{
    public bool $showModal = false;
    public ?int $user_id = null;
    public string $name = '';
    public string $email = '';
    public string $role = 'karyawan';
    public string $shift = 'Shift 1';
    public string $group = 'Group A';
    public ?int $customer_id = null;
    public string $password = '';
    public bool $must_change_password = true;

    public function mount()
    {
        if (! Auth::user() || ! Auth::user()->isAdmin()) {
            abort(403, 'Hanya Admin yang dapat mengelola akun pengguna.');
        }
    }

    public function openModal(?int $id = null)
    {
        $this->resetFields();
        if ($id) {
            $u = User::findOrFail($id);
            $this->user_id = $u->id;
            $this->name = $u->name;
            $this->email = $u->email;
            $this->role = $u->role;
            $this->shift = $u->shift ?? 'Shift 1';
            $this->group = $u->group ?? 'Group A';
            $this->customer_id = $u->customer_id;
            $this->must_change_password = (bool) ($u->must_change_password ?? false);
        } else {
            $this->must_change_password = true;
        }
        $this->showModal = true;
    }

    public function saveUser()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $this->user_id,
            'role' => 'required|string|in:karyawan,admin,supervisor,customer',
            'shift' => 'required|string',
            'group' => 'required|string',
        ]);

        $cleanEmail = filled($this->email) ? trim($this->email) : null;

        $data = [
            'name' => $this->name,
            'email' => $cleanEmail,
            'role' => $this->role,
            'shift' => $this->shift,
            'group' => $this->group,
            'customer_id' => $this->role === 'customer' ? $this->customer_id : null,
            'must_change_password' => $this->must_change_password,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        } elseif (! $this->user_id) {
            $data['password'] = Hash::make('password');
        }

        User::updateOrCreate(['id' => $this->user_id], $data);

        $this->showModal = false;
        $this->resetFields();
        session()->flash('message', 'Pengguna berhasil disimpan.');
    }

    public function deleteUser(int $id)
    {
        if ($id === Auth::id()) {
            session()->flash('message', 'Tidak dapat menghapus akun sendiri.');
            return;
        }
        User::findOrFail($id)->delete();
        session()->flash('message', 'Pengguna berhasil dihapus.');
    }

    protected function resetFields()
    {
        $this->reset(['user_id', 'name', 'email', 'role', 'shift', 'group', 'customer_id', 'password', 'must_change_password']);
    }

    public function render()
    {
        $users = User::with('customer')->orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();

        return view('livewire.admin.user-management', compact('users', 'customers'));
    }
}
