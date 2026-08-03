<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black tracking-wide text-zinc-100 flex items-center">
                Pengelolaan Master Data
                <span class="ml-3 px-3 py-0.5 rounded-full text-[10px] font-black uppercase bg-purple-950 text-purple-300 border border-purple-800">
                    Akses Admin & Supervisor
                </span>
            </h2>
            <p class="text-xs text-zinc-400 mt-1">Tambah, edit, dan kelola Master Data Pelanggan, Jenis Produk, Asal Tembakau, dan Jenis Kemasan</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex flex-wrap gap-2 border-b border-zinc-800 pb-3">
        <button wire:click="$set('activeTab', 'customers')" class="px-5 py-3 min-h-[48px] text-xs font-black rounded-xl transition-all {{ $activeTab === 'customers' ? 'bg-amber-600 text-white shadow-lg' : 'bg-zinc-900 text-zinc-400 border border-zinc-800 hover:text-zinc-200' }}">
            🏢 Pelanggan (Customer)
        </button>
        <button wire:click="$set('activeTab', 'products')" class="px-5 py-3 min-h-[48px] text-xs font-black rounded-xl transition-all {{ $activeTab === 'products' ? 'bg-amber-600 text-white shadow-lg' : 'bg-zinc-900 text-zinc-400 border border-zinc-800 hover:text-zinc-200' }}">
            🏷️ Jenis Produk (Product Type)
        </button>
        <button wire:click="$set('activeTab', 'origins')" class="px-5 py-3 min-h-[48px] text-xs font-black rounded-xl transition-all {{ $activeTab === 'origins' ? 'bg-amber-600 text-white shadow-lg' : 'bg-zinc-900 text-zinc-400 border border-zinc-800 hover:text-zinc-200' }}">
            🗺️ Asal Utama Tembakau (Origin)
        </button>
    </div>

    <!-- TAB 1: CUSTOMERS -->
    @if($activeTab === 'customers')
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 space-y-4 shadow-xl">
            <div class="flex items-center justify-between border-b border-zinc-800 pb-4">
                <div>
                    <h3 class="text-base font-black text-amber-400 uppercase tracking-wide">Daftar Pelanggan (Customer)</h3>
                    <p class="text-xs text-zinc-400">Master data pembeli / customer tembakau</p>
                </div>
                <button wire:click="openCustomerModal()" class="px-4 py-2.5 min-h-[48px] text-xs font-black rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white hover:from-amber-500 shadow">
                    + Tambah Pelanggan Baru
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-zinc-300">
                    <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase border-b border-zinc-800">
                        <tr>
                            <th class="px-4 py-3">Kode Customer</th>
                            <th class="px-4 py-3">Nama Pelanggan</th>
                            <th class="px-4 py-3">Kontak Person</th>
                            <th class="px-4 py-3">Telepon</th>
                            <th class="px-4 py-3">Alamat</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/80">
                        @forelse($customers as $c)
                            <tr class="hover:bg-zinc-800/40 transition-colors">
                                <td class="px-4 py-3 font-mono font-bold text-amber-400">{{ $c->code }}</td>
                                <td class="px-4 py-3 font-bold text-zinc-100">{{ $c->name }}</td>
                                <td class="px-4 py-3 text-zinc-300">{{ $c->contact_person ?? '-' }}</td>
                                <td class="px-4 py-3 text-zinc-400 font-mono">{{ $c->phone ?? '-' }}</td>
                                <td class="px-4 py-3 text-zinc-400 truncate max-w-xs">{{ $c->address ?? '-' }}</td>
                                <td class="px-4 py-3 text-center space-x-2">
                                    <button wire:click="openCustomerModal({{ $c->id }})" class="px-3 py-2 min-h-[44px] text-xs font-bold rounded-xl bg-zinc-800 text-zinc-200 hover:bg-zinc-700">
                                        ✏️ Edit
                                    </button>
                                    <button wire:click="deleteCustomer({{ $c->id }})" onclick="return confirm('Apakah Anda yakin ingin menghapus data pelanggan ini?')" class="px-3 py-2 min-h-[44px] text-xs font-bold rounded-xl bg-red-950 text-red-400 border border-red-800 hover:bg-red-900">
                                        🗑️ Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-zinc-500">Belum ada data pelanggan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- TAB 2: PRODUCT TYPES -->
    @if($activeTab === 'products')
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 space-y-4 shadow-xl">
            <div class="flex items-center justify-between border-b border-zinc-800 pb-4">
                <div>
                    <h3 class="text-base font-black text-amber-400 uppercase tracking-wide">Daftar Jenis Produk (Product Type)</h3>
                    <p class="text-xs text-zinc-400">Master data varietas / grade jenis tembakau</p>
                </div>
                <button wire:click="openProductModal()" class="px-4 py-2.5 min-h-[48px] text-xs font-black rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white hover:from-amber-500 shadow">
                    + Tambah Jenis Produk Baru
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-zinc-300">
                    <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase border-b border-zinc-800">
                        <tr>
                            <th class="px-4 py-3">Kode Produk</th>
                            <th class="px-4 py-3">Nama Jenis Produk</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/80">
                        @forelse($productTypes as $pt)
                            <tr class="hover:bg-zinc-800/40 transition-colors">
                                <td class="px-4 py-3 font-mono font-bold text-amber-400">{{ $pt->code }}</td>
                                <td class="px-4 py-3 font-bold text-zinc-100">{{ $pt->name }}</td>
                                <td class="px-4 py-3 text-center space-x-2">
                                    <button wire:click="openProductModal({{ $pt->id }})" class="px-3 py-2 min-h-[44px] text-xs font-bold rounded-xl bg-zinc-800 text-zinc-200 hover:bg-zinc-700">
                                        ✏️ Edit
                                    </button>
                                    <button wire:click="deleteProduct({{ $pt->id }})" onclick="return confirm('Hapus jenis produk ini?')" class="px-3 py-2 min-h-[44px] text-xs font-bold rounded-xl bg-red-950 text-red-400 border border-red-800 hover:bg-red-900">
                                        🗑️ Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-zinc-500">Belum ada data jenis produk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- TAB 3: ORIGINS -->
    @if($activeTab === 'origins')
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 space-y-4 shadow-xl">
            <div class="flex items-center justify-between border-b border-zinc-800 pb-4">
                <div>
                    <h3 class="text-base font-black text-amber-400 uppercase tracking-wide">Daftar Asal Utama Tembakau (Primary Origin)</h3>
                    <p class="text-xs text-zinc-400">Master data wilayah asal tembakau (Paiton, Lombok, Jember, dll)</p>
                </div>
                <button wire:click="openOriginModal()" class="px-4 py-2.5 min-h-[48px] text-xs font-black rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white hover:from-amber-500 shadow">
                    + Tambah Asal Daerah Baru
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-zinc-300">
                    <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase border-b border-zinc-800">
                        <tr>
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">Nama Daerah / Region Origin</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/80">
                        @forelse($origins as $idx => $org)
                            <tr class="hover:bg-zinc-800/40 transition-colors">
                                <td class="px-4 py-3 font-mono font-bold text-amber-400">{{ $idx + 1 }}</td>
                                <td class="px-4 py-3 font-bold text-zinc-100">{{ $org->region_name }}</td>
                                <td class="px-4 py-3 text-center space-x-2">
                                    <button wire:click="openOriginModal({{ $org->id }})" class="px-3 py-2 min-h-[44px] text-xs font-bold rounded-xl bg-zinc-800 text-zinc-200 hover:bg-zinc-700">
                                        ✏️ Edit
                                    </button>
                                    <button wire:click="deleteOrigin({{ $org->id }})" onclick="return confirm('Hapus asal daerah ini?')" class="px-3 py-2 min-h-[44px] text-xs font-bold rounded-xl bg-red-950 text-red-400 border border-red-800 hover:bg-red-900">
                                        🗑️ Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-zinc-500">Belum ada data asal daerah tembakau.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- CUSTOMER MODAL -->
    <div x-data="{ show: @entangle('showCustomerModal') }" x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="bg-zinc-900 border border-zinc-700 rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl">
            <h3 class="text-lg font-black text-amber-400 border-b border-zinc-800 pb-3">Form Data Pelanggan (Customer)</h3>
            <form wire:submit.prevent="saveCustomer" class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Kode Customer <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="customer_code" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-mono outline-none focus:border-amber-500" placeholder="Contoh: CUST-FNG">
                    @error('customer_code') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Nama Pelanggan <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="customer_name" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none focus:border-amber-500" placeholder="PT. Falih Nur Gemilang">
                    @error('customer_name') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Kontak Person</label>
                    <input type="text" wire:model="contact_person" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none" placeholder="Budi Santoso">
                </div>
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Nomor Telepon</label>
                    <input type="text" wire:model="phone" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-mono outline-none" placeholder="081234567890">
                </div>
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Alamat</label>
                    <textarea wire:model="address" rows="2" class="w-full px-4 py-3 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none" placeholder="Jl. Raya Industri No. 88, Surabaya"></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-3 border-t border-zinc-800">
                    <button type="button" wire:click="$set('showCustomerModal', false)" class="px-5 py-3 min-h-[48px] rounded-xl bg-zinc-800 text-zinc-300 font-bold">Batal</button>
                    <button type="submit" class="px-6 py-3 min-h-[48px] rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white font-black">Simpan Pelanggan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- PRODUCT TYPE MODAL -->
    <div x-data="{ show: @entangle('showProductModal') }" x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="bg-zinc-900 border border-zinc-700 rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl">
            <h3 class="text-lg font-black text-amber-400 border-b border-zinc-800 pb-3">Form Data Jenis Produk</h3>
            <form wire:submit.prevent="saveProduct" class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Kode Jenis Produk <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="product_code" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-mono outline-none focus:border-amber-500" placeholder="PAITON-P10T5">
                    @error('product_code') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Nama Jenis Produk <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="product_name" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none focus:border-amber-500" placeholder="PAITON P10T5">
                    @error('product_name') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="flex justify-end space-x-3 pt-3 border-t border-zinc-800">
                    <button type="button" wire:click="$set('showProductModal', false)" class="px-5 py-3 min-h-[48px] rounded-xl bg-zinc-800 text-zinc-300 font-bold">Batal</button>
                    <button type="submit" class="px-6 py-3 min-h-[48px] rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white font-black">Simpan Jenis Produk</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ORIGIN MODAL -->
    <div x-data="{ show: @entangle('showOriginModal') }" x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="bg-zinc-900 border border-zinc-700 rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl">
            <h3 class="text-lg font-black text-amber-400 border-b border-zinc-800 pb-3">Form Data Asal Utama Tembakau</h3>
            <form wire:submit.prevent="saveOrigin" class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Nama Asal Daerah (Origin Region) <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="region_name" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 uppercase outline-none focus:border-amber-500" placeholder="PAITON / LOMBOK / JEMBER">
                    @error('region_name') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="flex justify-end space-x-3 pt-3 border-t border-zinc-800">
                    <button type="button" wire:click="$set('showOriginModal', false)" class="px-5 py-3 min-h-[48px] rounded-xl bg-zinc-800 text-zinc-300 font-bold">Batal</button>
                    <button type="submit" class="px-6 py-3 min-h-[48px] rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white font-black">Simpan Asal Daerah</button>
                </div>
            </form>
        </div>
    </div>
</div>
