<div>
    @if($showModal)
        <div class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/85 backdrop-blur-md"
             x-data="{ showNewPass: false, showConfirmPass: false }"
             @keydown.escape.window.prevent="">
            
            <div class="bg-zinc-900 border-2 border-amber-500/40 rounded-3xl max-w-md w-full p-6 sm:p-8 space-y-5 shadow-2xl shadow-amber-950/50 relative animate-in fade-in zoom-in duration-200">
                
                <!-- Security Icon & Badge Header -->
                <div class="text-center space-y-2">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-950/80 border border-amber-600/40 flex items-center justify-center text-3xl shadow-lg shadow-amber-900/30">
                        🔐
                    </div>
                    <span class="inline-block px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500 text-zinc-950 font-mono shadow">
                        Wajib Ganti Password Pertama Kali
                    </span>
                    <h3 class="text-xl font-black tracking-wide text-zinc-100">
                        Selamat Datang di TPMS
                    </h3>
                    <p class="text-xs text-zinc-400 leading-relaxed">
                        Demi menjaga keamanan data sistem dan akun Anda (<span class="text-amber-400 font-bold font-mono">{{ auth()->user()->name ?? 'Pengguna' }}</span>), silakan tetapkan kata sandi baru untuk melanjutkan.
                    </p>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="updatePassword" class="space-y-4 text-xs">
                    <!-- New Password -->
                    <div>
                        <label class="block font-bold uppercase text-zinc-300 mb-1.5 flex items-center justify-between">
                            <span>Kata Sandi Baru <span class="text-amber-400">*</span></span>
                            <span class="text-[10px] text-zinc-500 font-normal lowercase">(minimal 6 karakter)</span>
                        </label>
                        <div class="relative">
                            <input :type="showNewPass ? 'text' : 'password'" 
                                   wire:model="newPassword" 
                                   class="w-full px-4 py-3 min-h-[48px] pr-11 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-mono outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500" 
                                   placeholder="Ketik password baru...">
                            <button type="button" 
                                    @click="showNewPass = !showNewPass" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-200 p-1">
                                <span x-show="!showNewPass" class="text-sm">👁️</span>
                                <span x-show="showNewPass" class="text-sm">🙈</span>
                            </button>
                        </div>
                        @error('newPassword') 
                            <span class="text-red-400 font-bold block mt-1.5 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $message }}
                            </span> 
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block font-bold uppercase text-zinc-300 mb-1.5">
                            Konfirmasi Kata Sandi Baru <span class="text-amber-400">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showConfirmPass ? 'text' : 'password'" 
                                   wire:model="newPasswordConfirmation" 
                                   class="w-full px-4 py-3 min-h-[48px] pr-11 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-mono outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500" 
                                   placeholder="Ulangi kata sandi baru...">
                            <button type="button" 
                                    @click="showConfirmPass = !showConfirmPass" 
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-200 p-1">
                                <span x-show="!showConfirmPass" class="text-sm">👁️</span>
                                <span x-show="showConfirmPass" class="text-sm">🙈</span>
                            </button>
                        </div>
                        @error('newPasswordConfirmation') 
                            <span class="text-red-400 font-bold block mt-1.5 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $message }}
                            </span> 
                        @enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full py-3.5 min-h-[48px] rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white font-black text-sm hover:from-amber-500 shadow-xl shadow-amber-950/60 flex items-center justify-center gap-2 transition-all">
                            <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <svg wire:loading class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Simpan Password & Lanjutkan</span>
                        </button>
                    </div>
                </form>

                <p class="text-[11px] text-center text-zinc-500 italic">
                    ⚠️ Penggantian ini hanya perlu dilakukan satu kali saat pertama login.
                </p>
            </div>
        </div>
    @endif
</div>
