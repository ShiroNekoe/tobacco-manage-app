<div class="min-h-screen flex items-center justify-center bg-zinc-950 p-4">
    <div class="w-full max-w-md space-y-6">
        <!-- Logo & Header -->
        <div class="text-center space-y-2">
            <img src="/images/icontobacco.jpeg" alt="Tobacco Logo" class="inline-block w-16 h-16 object-contain rounded-2xl shadow-xl shadow-amber-900/40 border border-amber-500/30 mb-2">
            <h1 class="text-2xl font-black tracking-wide text-amber-400">Tobacco Production Management System</h1>
            <p class="text-xs text-zinc-400 font-medium">Sistem Production & Quality Control Timbangan Tembakau</p>
        </div>

        <!-- Quick Demo Login Buttons for 4 Roles -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 shadow-xl space-y-3">
            <div class="flex items-center justify-between border-b border-zinc-800 pb-2">
                <span class="text-xs font-black uppercase text-amber-400 tracking-wider">⚡ Akses Cepat Login Demo (4 Roles):</span>
            </div>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <button type="button" wire:click="loginAsRole('karyawan')" class="px-3 py-3 min-h-[48px] rounded-xl bg-amber-950/80 text-amber-300 border border-amber-800 font-bold hover:bg-amber-900 transition-all text-left">
                    👨‍🌾 <strong>Karyawan</strong>
                    <span class="block text-[10px] font-normal text-zinc-400">PWA Timbangan Karung</span>
                </button>
                <button type="button" wire:click="loginAsRole('admin')" class="px-3 py-3 min-h-[48px] rounded-xl bg-purple-950/80 text-purple-300 border border-purple-800 font-bold hover:bg-purple-900 transition-all text-left">
                    🛡️ <strong>Admin MES</strong>
                    <span class="block text-[10px] font-normal text-zinc-400">Launch Batch & Tracking</span>
                </button>
                <button type="button" wire:click="loginAsRole('supervisor')" class="px-3 py-3 min-h-[48px] rounded-xl bg-emerald-950/80 text-emerald-300 border border-emerald-800 font-bold hover:bg-emerald-900 transition-all text-left">
                    👔 <strong>Supervisor QC</strong>
                    <span class="block text-[10px] font-normal text-zinc-400">ACC Approval Certificate</span>
                </button>
                <button type="button" wire:click="loginAsRole('customer')" class="px-3 py-3 min-h-[48px] rounded-xl bg-blue-950/80 text-blue-300 border border-blue-800 font-bold hover:bg-blue-900 transition-all text-left">
                    🏢 <strong>Customer Portal</strong>
                    <span class="block text-[10px] font-normal text-zinc-400">Line Chart & Download PDF</span>
                </button>
            </div>
        </div>

        <!-- Login Form -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 shadow-2xl space-y-4">
            <form wire:submit.prevent="login" class="space-y-4 text-xs">
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Alamat Email</label>
                    <input type="email" wire:model="email" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-mono outline-none focus:border-amber-500" placeholder="nama@tobacco.com">
                    @error('email') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Kata Sandi (Password)</label>
                    <input type="password" wire:model="password" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none focus:border-amber-500">
                    @error('password') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center text-zinc-400 cursor-pointer">
                        <input type="checkbox" wire:model="remember" class="rounded bg-zinc-950 border-zinc-800 text-amber-500">
                        <span class="ml-2 font-medium">Ingat Saya</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3.5 min-h-[48px] rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white font-black text-sm hover:from-amber-500 shadow-xl shadow-amber-950/50">
                    Masuk ke Sistem MES
                </button>
            </form>
        </div>
    </div>
</div>
