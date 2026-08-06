<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black tracking-wide text-zinc-100">
                {{ $mrlId ? 'Edit Material Receipt List (MRL)' : 'Input MRL Baru (Penerimaan Bahan Baku)' }}
            </h2>
            <p class="text-xs text-zinc-400 mt-1">Registrasi penerimaan dan penimbangan awal tembakau mentah dari supplier</p>
        </div>
        <a href="{{ route('mrl.list') }}" class="px-4 py-2 min-h-[44px] inline-flex items-center text-xs font-semibold rounded-xl bg-zinc-800 text-zinc-300 hover:bg-zinc-700">
            &larr; Kembali ke Daftar MRL
        </a>
    </div>

    <form wire:submit.prevent="save" class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 sm:p-8 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Supplier -->
            <div>
                <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Pemasok / Supplier <span class="text-red-400">*</span></label>
                <select wire:model.live="supplier_id" class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm focus:border-amber-500 outline-none">
                    <option value="">-- Pilih Supplier --</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}">{{ $sup->supplier_code }} - {{ $sup->name }} ({{ $sup->origin_region }})</option>
                    @endforeach
                </select>
                @error('supplier_id') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Delivery Note (DN Number) -->
            <div>
                <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Nomor Surat Jalan (Delivery Note / DN) <span class="text-zinc-500 font-normal">(Opsional)</span></label>
                <input type="text" wire:model="dn_number" class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm focus:border-amber-500 outline-none" placeholder="Contoh: DN-2026-0801 (Otomatis jika dikosongkan)">
                @error('dn_number') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Asal Daerah / Origin Region -->
            <div>
                <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Asal Daerah Tembakau (Origin) <span class="text-red-400">*</span></label>
                <input type="text" wire:model="origin_region" class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm focus:border-amber-500 outline-none" placeholder="Contoh: Jember, Jawa Timur">
                @error('origin_region') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Grade Quality -->
            <div>
                <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Klasifikasi Grade / Mutu <span class="text-red-400">*</span></label>
                <input type="text" wire:model="tobacco_grade" class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm focus:border-amber-500 outline-none" placeholder="Contoh: FCV Grade A / Kasturi Top">
                @error('tobacco_grade') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Batch Number -->
            <div>
                <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Nomor Batch (Batch ID) <span class="text-red-400">*</span></label>
                <input type="text" wire:model="batch_number" class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm focus:border-amber-500 outline-none" placeholder="Contoh: BATCH-JTM-2026A">
                @error('batch_number') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Total Pack / Bale -->
            <div>
                <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Jumlah Pack / Bale (Bungkus) <span class="text-red-400">*</span></label>
                <input type="number" inputmode="numeric" wire:model="total_pack" class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm focus:border-amber-500 outline-none" placeholder="50">
                @error('total_pack') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="border-t border-zinc-800 pt-6">
            <h3 class="text-sm font-bold uppercase tracking-wider text-amber-400 mb-4">Penimbangan Bobot (Digital Weighing)</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Gross Weight -->
                <div>
                    <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Gross Weight / Bobot Kotor (kg) <span class="text-red-400">*</span></label>
                    <input type="number" step="0.01" inputmode="decimal" wire:model.live.debounce.300ms="gross_weight" class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-lg font-bold focus:border-amber-500 outline-none text-emerald-400" placeholder="0.00">
                    @error('gross_weight') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Tare Weight -->
                <div>
                    <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Tare Weight / Bobot Kemasan (kg) <span class="text-red-400">*</span></label>
                    <input type="number" step="0.01" inputmode="decimal" wire:model.live.debounce.300ms="tare_weight" class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-lg font-bold focus:border-amber-500 outline-none text-amber-400" placeholder="0.00">
                    @error('tare_weight') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Net Weight (Auto-calculated) -->
                <div>
                    <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Net Weight / Bobot Bersih (kg) <span class="text-amber-500">(Auto)</span></label>
                    <input type="number" step="0.01" inputmode="decimal" wire:model="net_weight" readonly class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-zinc-950/80 border border-amber-800/60 text-amber-400 text-xl font-black cursor-not-allowed outline-none" placeholder="0.00">
                    <p class="text-[11px] text-zinc-500 mt-1">Dihitung otomatis: Gross Weight - Tare Weight</p>
                    @error('net_weight') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="border-t border-zinc-800 pt-6 flex justify-end">
            <button type="submit" class="px-6 py-3 min-h-[44px] rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white font-bold text-sm hover:from-amber-500 hover:to-amber-600 shadow-lg shadow-amber-900/30">
                Simpan & Terbitkan MRL
            </button>
        </div>
    </form>
</div>
