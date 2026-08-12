<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black tracking-wide text-zinc-100 flex items-center">
                Manajemen Pengguna & Shift
                <span class="ml-3 px-3 py-0.5 rounded-full text-[10px] font-black uppercase bg-purple-950 text-purple-300 border border-purple-800">
                    Akses Admin
                </span>
            </h2>
            <p class="text-xs text-zinc-400 mt-1">Pengelolaan akun Karyawan (Shift & Group), Admin, Supervisor, dan Customer Portal</p>
        </div>

        <button wire:click="openModal()" class="px-5 py-3 min-h-[48px] inline-flex items-center justify-center font-black text-xs rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white hover:from-amber-500 shadow-xl shadow-amber-950/50">
            + Tambah Pengguna Baru
        </button>
    </div>

    <!-- Users Data Table -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-zinc-300">
                <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase border-b border-zinc-800">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Peran (Role)</th>
                        <th class="px-4 py-3">Shift & Group</th>
                        <th class="px-4 py-3">Customer Link</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/80">
                    @forelse($users as $u)
                        <tr class="hover:bg-zinc-800/40 transition-colors">
                            <td class="px-4 py-3.5 font-bold text-zinc-100">
                                {{ $u->name }}
                                @if($u->must_change_password)
                                    <span class="ml-2 px-2 py-0.5 rounded-md text-[9px] font-bold bg-amber-950 text-amber-300 border border-amber-800">
                                        🔐 Wajib Ganti Password
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-zinc-400 font-mono">{{ $u->email ?: '-' }}</td>
                            <td class="px-4 py-3.5">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-amber-950 text-amber-300 border border-amber-800">
                                    {{ strtoupper($u->role) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-semibold text-zinc-300">
                                {{ $u->shift ?? 'Shift 1' }} - {{ $u->group ?? 'Group A' }}
                            </td>
                            <td class="px-4 py-3.5 text-zinc-400 font-medium">
                                {{ $u->customer->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-center space-x-2">
                                <button wire:click="openModal({{ $u->id }})" class="px-3 py-2 min-h-[44px] text-xs font-bold rounded-xl bg-zinc-800 text-zinc-200 hover:bg-zinc-700">
                                    ✏️ Edit
                                </button>
                                <button wire:click="deleteUser({{ $u->id }})" onclick="return confirm('Hapus akun pengguna ini?')" class="px-3 py-2 min-h-[44px] text-xs font-bold rounded-xl bg-red-950 text-red-400 border border-red-800 hover:bg-red-900">
                                    🗑️ Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-zinc-500">Belum ada pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- USER MODAL -->
    <div x-data="{ show: @entangle('showModal') }" x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="bg-zinc-900 border border-zinc-700 rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl">
            <h3 class="text-lg font-black text-amber-400 border-b border-zinc-800 pb-3">Form Pengguna & Shift</h3>
            <form wire:submit.prevent="saveUser" class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Nama Lengkap <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="name" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none focus:border-amber-500">
                    @error('name') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Email <span class="text-zinc-500 font-normal text-[10px] lowercase">(opsional)</span></label>
                    <input type="email" wire:model="email" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-mono outline-none focus:border-amber-500">
                    @error('email') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Peran (Role) <span class="text-red-400">*</span></label>
                    <select wire:model.live="role" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none">
                        <option value="karyawan">Karyawan (Worker)</option>
                        <option value="admin">Admin</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="customer">Customer</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold uppercase text-zinc-300 mb-1">Shift Kerja</label>
                        <select wire:model="shift" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none">
                            <option value="Shift 1">Shift 1</option>
                            <option value="Shift 2">Shift 2</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-zinc-300 mb-1">Group Kerja</label>
                        <select wire:model="group" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none">
                            <option value="Group A">Group A</option>
                            <option value="Group B">Group B</option>
                            <option value="Group C">Group C</option>
                        </select>
                    </div>
                </div>
                @if($role === 'customer')
                    <div>
                        <label class="block font-bold uppercase text-zinc-300 mb-1">Link Perusahaan Pelanggan</label>
                        <select wire:model="customer_id" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none">
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Kata Sandi (Kosongkan jika tidak diubah)</label>
                    <input type="password" wire:model="password" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none" placeholder="Default: password">
                </div>
                <div class="pt-1">
                    <label class="inline-flex items-center gap-2 text-zinc-300 cursor-pointer select-none">
                        <input type="checkbox" wire:model="must_change_password" class="rounded bg-zinc-950 border-zinc-800 text-amber-500 focus:ring-amber-500">
                        <span class="font-semibold">Wajib ganti password saat login pertama kali</span>
                    </label>
                </div>
                <div class="flex justify-end space-x-3 pt-3 border-t border-zinc-800">
                    <button type="button" wire:click="$set('showModal', false)" class="px-5 py-3 min-h-[48px] rounded-xl bg-zinc-800 text-zinc-300 font-bold">Batal</button>
                    <button type="submit" class="px-6 py-3 min-h-[48px] rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white font-black">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>
