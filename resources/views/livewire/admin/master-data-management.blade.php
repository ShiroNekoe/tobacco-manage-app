<div x-data="{
    activeTab: @entangle('activeTab')
}"
x-init="$watch('activeTab', value => $dispatch('master-data-tab-changed', value))"
x-on:switch-master-data-tab.window="activeTab = $event.detail; $wire.setTab($event.detail)"
class="space-y-6">

    <!-- TOP HEADER -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-4 sm:p-6 shadow-2xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <div class="w-11 h-11 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center shrink-0 shadow-lg shadow-amber-500/10">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-black text-zinc-100 tracking-wide uppercase">Pengelolaan Master Data</h1>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-purple-950 text-purple-300 border border-purple-800">
                            Akses Admin & Supervisor
                        </span>
                    </div>
                    <p class="text-xs text-zinc-400 mt-0.5">Kelola data terpusat untuk Pelanggan, Jenis Produk, Asal Tembakau, dan Jenis Muatan Surat Jalan</p>
                </div>
            </div>

            <!-- ACTIVE MODULE BADGE (Synced with Sidebar Submenu) -->
            <div class="flex items-center gap-2 bg-zinc-950 px-4 py-2 rounded-2xl border border-zinc-800 self-start sm:self-auto shadow-inner">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                <span class="text-xs font-mono font-bold text-amber-400" x-text="
                    activeTab === 'customers' ? '🏢 Master Pelanggan (Customer)' :
                    activeTab === 'products' ? '🏷️ Jenis Produk (Product Type)' :
                    activeTab === 'origins' ? '🗺️ Asal Tembakau (Primary Origin)' :
                    activeTab === 'materials' ? '📦 Jenis Muatan (Material Type)' :
                    activeTab === 'pack_types' ? '📦 Jenis Kemasan (Pack Type)' : 'Master Data'
                ">Master Pelanggan</span>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT AREA -->
    <div class="w-full space-y-4">
        <!-- Search & Actions Top Bar -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-4 sm:p-5 shadow-xl flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="relative flex-1">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari dalam daftar {{ $activeTab === 'customers' ? 'pelanggan' : ($activeTab === 'products' ? 'jenis produk' : ($activeTab === 'origins' ? 'asal tembakau' : ($activeTab === 'materials' ? 'jenis muatan' : 'jenis kemasan'))) }}..." class="w-full pl-10 pr-4 py-2.5 rounded-2xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                <span class="absolute left-3.5 top-3 text-zinc-500 text-xs">🔍</span>
            </div>

            @if($activeTab === 'customers')
                <button wire:click="openCustomerModal()" class="px-4 py-2.5 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-700 text-white hover:from-amber-500 text-xs font-black transition-all shadow-md shadow-amber-900/30 flex items-center justify-center gap-1.5 whitespace-nowrap">
                    <span>➕ Tambah Pelanggan</span>
                </button>
            @elseif($activeTab === 'products')
                <button wire:click="openProductModal()" class="px-4 py-2.5 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-700 text-white hover:from-amber-500 text-xs font-black transition-all shadow-md shadow-amber-900/30 flex items-center justify-center gap-1.5 whitespace-nowrap">
                    <span>➕ Tambah Jenis Produk</span>
                </button>
            @elseif($activeTab === 'origins')
                <button wire:click="openOriginModal()" class="px-4 py-2.5 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-700 text-white hover:from-amber-500 text-xs font-black transition-all shadow-md shadow-amber-900/30 flex items-center justify-center gap-1.5 whitespace-nowrap">
                    <span>➕ Tambah Asal Daerah</span>
                </button>
            @elseif($activeTab === 'materials')
                <button wire:click="openMaterialModal()" class="px-4 py-2.5 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-700 text-white hover:from-amber-500 text-xs font-black transition-all shadow-md shadow-amber-900/30 flex items-center justify-center gap-1.5 whitespace-nowrap">
                    <span>➕ Tambah Jenis Muatan</span>
                </button>
            @elseif($activeTab === 'pack_types')
                <button wire:click="openPackTypeModal()" class="px-4 py-2.5 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-700 text-white hover:from-amber-500 text-xs font-black transition-all shadow-md shadow-amber-900/30 flex items-center justify-center gap-1.5 whitespace-nowrap">
                    <span>➕ Tambah Jenis Kemasan</span>
                </button>
            @endif
        </div>

            <!-- TAB 1: CUSTOMERS -->
            @if($activeTab === 'customers')
                <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 space-y-4 shadow-xl">
                    <div class="border-b border-zinc-800 pb-3">
                        <h3 class="text-sm font-black text-amber-400 uppercase tracking-wider">Daftar Pelanggan (Customer)</h3>
                        <p class="text-xs text-zinc-400 mt-0.5">Master data pembeli / mitra customer tembakau</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-zinc-300">
                            <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase border-b border-zinc-800">
                                <tr>
                                    <th class="px-4 py-3">Kode</th>
                                    <th class="px-4 py-3">Nama Pelanggan</th>
                                    <th class="px-4 py-3">Akses Portal / Email</th>
                                    <th class="px-4 py-3">Kontak Person</th>
                                    <th class="px-4 py-3">Telepon</th>
                                    <th class="px-4 py-3">Alamat</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800/80 font-sans">
                                @forelse($customers as $c)
                                    <tr class="hover:bg-zinc-800/40 transition-colors">
                                        <td class="px-4 py-3 font-mono font-bold text-amber-400">{{ $c->code }}</td>
                                        <td class="px-4 py-3 font-bold text-zinc-100">{{ $c->name }}</td>
                                        <td class="px-4 py-3">
                                            @if($c->email || ($c->user && $c->user->email))
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-mono font-bold bg-amber-950/80 text-amber-300 border border-amber-800/80">
                                                    🔑 {{ $c->email ?? $c->user->email }}
                                                </span>
                                            @else
                                                <span class="text-zinc-500 italic text-[11px]">Belum diatur</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-zinc-300">{{ $c->contact_person ?? '-' }}</td>
                                        <td class="px-4 py-3 text-zinc-400 font-mono">{{ $c->phone ?? '-' }}</td>
                                        <td class="px-4 py-3 text-zinc-400 truncate max-w-xs">{{ $c->address ?? '-' }}</td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap space-x-1.5">
                                            <button wire:click="openCustomerModal({{ $c->id }})" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-zinc-800 text-zinc-200 hover:bg-zinc-700">
                                                ✏️ Edit
                                            </button>
                                            <button wire:click="deleteCustomer({{ $c->id }})" onclick="return confirm('Apakah Anda yakin ingin menghapus data pelanggan ini?')" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-red-950 text-red-400 border border-red-800 hover:bg-red-900">
                                                🗑️ Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-zinc-500">Tidak ada data pelanggan yang cocok.</td>
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
                    <div class="border-b border-zinc-800 pb-3">
                        <h3 class="text-sm font-black text-amber-400 uppercase tracking-wider">Daftar Jenis Produk (Product Type)</h3>
                        <p class="text-xs text-zinc-400 mt-0.5">Master data varietas / grade jenis tembakau</p>
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
                            <tbody class="divide-y divide-zinc-800/80 font-sans">
                                @forelse($productTypes as $pt)
                                    <tr class="hover:bg-zinc-800/40 transition-colors">
                                        <td class="px-4 py-3 font-mono font-bold text-amber-400">{{ $pt->code }}</td>
                                        <td class="px-4 py-3 font-bold text-zinc-100">{{ $pt->name }}</td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap space-x-1.5">
                                            <button wire:click="openProductModal({{ $pt->id }})" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-zinc-800 text-zinc-200 hover:bg-zinc-700">
                                                ✏️ Edit
                                            </button>
                                            <button wire:click="deleteProduct({{ $pt->id }})" onclick="return confirm('Hapus jenis produk ini?')" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-red-950 text-red-400 border border-red-800 hover:bg-red-900">
                                                🗑️ Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-zinc-500">Tidak ada data jenis produk yang cocok.</td>
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
                    <div class="border-b border-zinc-800 pb-3">
                        <h3 class="text-sm font-black text-amber-400 uppercase tracking-wider">Daftar Asal Utama Tembakau (Primary Origin)</h3>
                        <p class="text-xs text-zinc-400 mt-0.5">Master data wilayah asal tembakau (Paiton, Lombok, Temanggung, dll)</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-zinc-300">
                            <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase border-b border-zinc-800">
                                <tr>
                                    <th class="px-4 py-3">No</th>
                                    <th class="px-4 py-3">Nama Daerah Origin</th>
                                    <th class="px-4 py-3">Kode Singkatan</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800/80 font-sans">
                                @forelse($origins as $idx => $org)
                                    <tr class="hover:bg-zinc-800/40 transition-colors">
                                        <td class="px-4 py-3 font-mono font-bold text-amber-400">{{ $idx + 1 }}</td>
                                        <td class="px-4 py-3 font-bold text-zinc-100">{{ $org->region_name }}</td>
                                        <td class="px-4 py-3 font-mono text-cyan-400">{{ $org->code ?: '-' }}</td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap space-x-1.5">
                                            <button wire:click="openOriginModal({{ $org->id }})" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-zinc-800 text-zinc-200 hover:bg-zinc-700">
                                                ✏️ Edit
                                            </button>
                                            <button wire:click="deleteOrigin({{ $org->id }})" onclick="return confirm('Hapus asal daerah ini?')" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-red-950 text-red-400 border border-red-800 hover:bg-red-900">
                                                🗑️ Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-zinc-500">Tidak ada data asal daerah yang cocok.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- TAB 4: MATERIAL TYPES (JENIS MUATAN DN SHIPMENT) [NEW] -->
            @if($activeTab === 'materials')
                <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 space-y-4 shadow-xl">
                    <div class="border-b border-zinc-800 pb-3">
                        <h3 class="text-sm font-black text-amber-400 uppercase tracking-wider">Daftar Jenis Muatan DN Pengiriman (Material Types)</h3>
                        <p class="text-xs text-zinc-400 mt-0.5">Master data opsi jenis muatan pada Surat Jalan DN Shipment (Produk, Bits/Stem, Dust, atau jenis kargo lainnya)</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-zinc-300">
                            <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase border-b border-zinc-800">
                                <tr>
                                    <th class="px-4 py-3">Kode Muatan</th>
                                    <th class="px-4 py-3">Nama Jenis Muatan</th>
                                    <th class="px-4 py-3">Deskripsi</th>
                                    <th class="px-4 py-3 text-right">Default Netto/Krg</th>
                                    <th class="px-4 py-3 text-right">Default Tare/Krg</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800/80 font-sans">
                                @forelse($materials as $mat)
                                    <tr class="hover:bg-zinc-800/40 transition-colors">
                                        <td class="px-4 py-3 font-mono font-bold text-cyan-400">{{ $mat->code }}</td>
                                        <td class="px-4 py-3 font-bold text-zinc-100 flex items-center gap-1.5">
                                            @if($mat->code === 'Product')
                                                <span class="text-base">🍃</span>
                                            @elseif($mat->code === 'Bits / Stem')
                                                <span class="text-base">🌿</span>
                                            @elseif($mat->code === 'Dust')
                                                <span class="text-base">💨</span>
                                            @else
                                                <span class="text-base">📦</span>
                                            @endif
                                            <span>{{ $mat->name }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-zinc-400">{{ $mat->description ?: '-' }}</td>
                                        <td class="px-4 py-3 text-right font-mono font-bold text-emerald-400">{{ number_format($mat->default_sack_weight, 2) }} kg</td>
                                        <td class="px-4 py-3 text-right font-mono text-zinc-400">{{ number_format($mat->default_tare_weight, 2) }} kg</td>
                                        <td class="px-4 py-3 text-center">
                                            @if($mat->is_active)
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-950 text-emerald-400 border border-emerald-800">
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-zinc-800 text-zinc-500 border border-zinc-700">
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap space-x-1.5">
                                            <button wire:click="openMaterialModal({{ $mat->id }})" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-zinc-800 text-zinc-200 hover:bg-zinc-700">
                                                ✏️ Edit
                                            </button>
                                            <button wire:click="deleteMaterial({{ $mat->id }})" onclick="return confirm('Hapus jenis muatan ini?')" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-red-950 text-red-400 border border-red-800 hover:bg-red-900">
                                                🗑️ Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-zinc-500">Tidak ada data jenis muatan yang cocok.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- TAB 5: PACK TYPES (JENIS KEMASAN) -->
            @if($activeTab === 'pack_types')
                <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 space-y-4 shadow-xl">
                    <div class="border-b border-zinc-800 pb-3">
                        <h3 class="text-sm font-black text-amber-400 uppercase tracking-wider">Daftar Jenis Kemasan (Pack Types)</h3>
                        <p class="text-xs text-zinc-400 mt-0.5">Master data opsi jenis kemasan saat Input MRL Pre-Launch & Pembuatan Batch (Bale, Sack, Box, C48, dsb.)</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-zinc-300">
                            <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase border-b border-zinc-800">
                                <tr>
                                    <th class="px-4 py-3">Kode Kemasan</th>
                                    <th class="px-4 py-3">Nama Jenis Kemasan</th>
                                    <th class="px-4 py-3">Deskripsi</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800/80 font-sans">
                                @forelse($packTypes as $pt)
                                    <tr class="hover:bg-zinc-800/40 transition-colors">
                                        <td class="px-4 py-3 font-mono font-bold text-amber-400">{{ $pt->code }}</td>
                                        <td class="px-4 py-3 font-bold text-zinc-100 flex items-center gap-1.5">
                                            @if(stripos($pt->code, 'box') !== false || stripos($pt->code, 'c48') !== false)
                                                <span class="text-base">📦</span>
                                            @elseif(stripos($pt->code, 'sack') !== false)
                                                <span class="text-base">🛍️</span>
                                            @else
                                                <span class="text-base">🏷️</span>
                                            @endif
                                            <span>{{ $pt->name }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-zinc-400">{{ $pt->description ?: '-' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @if($pt->is_active)
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-950 text-emerald-400 border border-emerald-800">
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-zinc-800 text-zinc-500 border border-zinc-700">
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap space-x-1.5">
                                            <button wire:click="openPackTypeModal({{ $pt->id }})" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-zinc-800 text-zinc-200 hover:bg-zinc-700">
                                                ✏️ Edit
                                            </button>
                                            <button wire:click="deletePackType({{ $pt->id }})" onclick="return confirm('Hapus jenis kemasan ini?')" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-red-950 text-red-400 border border-red-800 hover:bg-red-900">
                                                🗑️ Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-zinc-500">Tidak ada data jenis kemasan yang cocok.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

    <!-- CUSTOMER MODAL -->
    @if($showCustomerModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm overflow-y-auto">
        <div class="bg-zinc-900 border border-zinc-700 rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl my-auto">
            <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                <h3 class="text-base font-black text-amber-400">Form Data Pelanggan (Customer)</h3>
                <button type="button" wire:click="$set('showCustomerModal', false)" class="text-zinc-400 hover:text-white text-2xl font-bold p-1 leading-none">&times;</button>
            </div>
            <form wire:submit.prevent="saveCustomer" class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Kode Customer <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="customer_code" class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-mono outline-none focus:border-amber-500" placeholder="Contoh: CUST-FNG">
                    @error('customer_code') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Nama Pelanggan <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="customer_name" class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none focus:border-amber-500" placeholder="PT. Falih Nur Gemilang">
                    @error('customer_name') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Kontak Person</label>
                    <input type="text" wire:model="contact_person" class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none" placeholder="Budi Santoso">
                </div>
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Nomor Telepon</label>
                    <input type="text" wire:model="phone" class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-mono outline-none" placeholder="081234567890">
                </div>
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Alamat</label>
                    <textarea wire:model="address" rows="2" class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none" placeholder="Jl. Raya Industri No. 88, Surabaya"></textarea>
                </div>

                <!-- AKSES PORTAL CUSTOMER (EMAIL & PASSWORD) -->
                <div class="p-3.5 bg-amber-950/20 border border-amber-800/40 rounded-2xl space-y-3">
                    <div class="flex items-center gap-2 border-b border-amber-800/30 pb-2">
                        <span class="text-base">🔑</span>
                        <div>
                            <h4 class="text-xs font-black uppercase text-amber-400">Akses Portal Customer</h4>
                            <p class="text-[10px] text-zinc-400">Buat atau perbarui akun login portal customer ini</p>
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-zinc-300 mb-1">Email Customer (Username Login)</label>
                        <input type="email" wire:model="email" class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-mono outline-none focus:border-amber-500" placeholder="customer@falihnur.com">
                        @error('email') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-zinc-300 mb-1">Password Portal Customer</label>
                        <input type="password" wire:model="password" class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none focus:border-amber-500" placeholder="{{ $customer_id ? 'Kosongkan jika tidak ingin mengubah password' : 'Masukkan kata sandi portal (min. 6 karakter)' }}">
                        <p class="text-[11px] text-zinc-400 mt-1">Gunakan password ini untuk login ke Portal Customer dan mengakses data batch milik customer ini.</p>
                        @error('password') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="flex justify-end space-x-3 pt-3 border-t border-zinc-800">
                    <button type="button" wire:click="$set('showCustomerModal', false)" class="px-4 py-2.5 rounded-xl bg-zinc-800 text-zinc-300 font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white font-black">Simpan Pelanggan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- PRODUCT TYPE MODAL -->
    @if($showProductModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm overflow-y-auto">
        <div class="bg-zinc-900 border border-zinc-700 rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl my-auto">
            <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                <h3 class="text-base font-black text-amber-400">Form Data Jenis Produk</h3>
                <button type="button" wire:click="$set('showProductModal', false)" class="text-zinc-400 hover:text-white text-2xl font-bold p-1 leading-none">&times;</button>
            </div>
            <form wire:submit.prevent="saveProduct" class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Kode Jenis Produk <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="product_code" class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-mono outline-none focus:border-amber-500" placeholder="PAITON-P10T5">
                    @error('product_code') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Nama Jenis Produk <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="product_name" class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none focus:border-amber-500" placeholder="PAITON P10T5">
                    @error('product_name') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="flex justify-end space-x-3 pt-3 border-t border-zinc-800">
                    <button type="button" wire:click="$set('showProductModal', false)" class="px-4 py-2.5 rounded-xl bg-zinc-800 text-zinc-300 font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white font-black">Simpan Jenis Produk</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ORIGIN MODAL -->
    @if($showOriginModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm overflow-y-auto">
        <div class="bg-zinc-900 border border-zinc-700 rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl my-auto">
            <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                <h3 class="text-base font-black text-amber-400">Form Data Asal Utama Tembakau</h3>
                <button type="button" wire:click="$set('showOriginModal', false)" class="text-zinc-400 hover:text-white text-2xl font-bold p-1 leading-none">&times;</button>
            </div>
            <form wire:submit.prevent="saveOrigin" class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Nama Asal Daerah (Origin Region) <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="region_name" class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 uppercase outline-none focus:border-amber-500" placeholder="PAITON / LOMBOK / JEMBER">
                    @error('region_name') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Kode Singkatan (Opsional)</label>
                    <input type="text" wire:model="origin_code_abbr" class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 uppercase font-mono outline-none" placeholder="Contoh: PTN / LBK">
                </div>
                <div class="flex justify-end space-x-3 pt-3 border-t border-zinc-800">
                    <button type="button" wire:click="$set('showOriginModal', false)" class="px-4 py-2.5 rounded-xl bg-zinc-800 text-zinc-300 font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white font-black">Simpan Asal Daerah</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- MATERIAL TYPE MODAL [NEW] -->
    @if($showMaterialModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm overflow-y-auto">
        <div class="bg-zinc-900 border border-zinc-700 rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl my-auto">
            <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                <h3 class="text-base font-black text-amber-400">Form Data Jenis Muatan DN Shipment</h3>
                <button type="button" wire:click="$set('showMaterialModal', false)" class="text-zinc-400 hover:text-white text-2xl font-bold p-1 leading-none">&times;</button>
            </div>
            <form wire:submit.prevent="saveMaterial" class="space-y-3 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold uppercase text-zinc-300 mb-1">Kode Muatan <span class="text-red-400">*</span></label>
                        <input type="text" wire:model="material_code" class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-cyan-300 font-mono outline-none focus:border-amber-500" placeholder="Product / Bits / Dust / Strip">
                        @error('material_code') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-zinc-300 mb-1">Nama Jenis Muatan <span class="text-red-400">*</span></label>
                        <input type="text" wire:model="material_name" class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none focus:border-amber-500" placeholder="Product Utama / Gagang">
                        @error('material_name') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Deskripsi / Keterangan Muatan</label>
                    <input type="text" wire:model="material_description" class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none" placeholder="Keterangan singkat tentang muatan ini">
                    @error('material_description') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold uppercase text-zinc-300 mb-1">Default Berat Netto / Krg (kg) <span class="text-red-400">*</span></label>
                        <input type="number" step="0.01" wire:model="default_sack_weight" class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-emerald-400 font-mono font-bold outline-none focus:border-amber-500">
                        @error('default_sack_weight') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-zinc-300 mb-1">Default Tare / Krg (kg) <span class="text-red-400">*</span></label>
                        <input type="number" step="0.01" wire:model="default_tare_weight" class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-300 font-mono outline-none focus:border-amber-500">
                        @error('default_tare_weight') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="pt-1">
                    <label class="flex items-center space-x-2 cursor-pointer select-none">
                        <input type="checkbox" wire:model="is_active" class="rounded border-zinc-700 text-amber-600 focus:ring-amber-500 w-4 h-4 bg-zinc-950">
                        <span class="text-xs font-bold text-zinc-200">Status Aktif (Tampilkan di opsi dropdown Surat Jalan DN)</span>
                    </label>
                </div>

                <div class="flex justify-end space-x-3 pt-3 border-t border-zinc-800">
                    <button type="button" wire:click="$set('showMaterialModal', false)" class="px-4 py-2.5 rounded-xl bg-zinc-800 text-zinc-300 font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white font-black">Simpan Jenis Muatan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- PACK TYPE MODAL -->
    @if($showPackTypeModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm overflow-y-auto">
        <div class="bg-zinc-900 border border-zinc-700 rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl my-auto">
            <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                <h3 class="text-base font-black text-amber-400">Form Data Jenis Kemasan (Pack Type)</h3>
                <button type="button" wire:click="$set('showPackTypeModal', false)" class="text-zinc-400 hover:text-white text-2xl font-bold p-1 leading-none">&times;</button>
            </div>
            <form wire:submit.prevent="savePackType" class="space-y-3 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold uppercase text-zinc-300 mb-1">Kode Kemasan <span class="text-red-400">*</span></label>
                        <input type="text" wire:model="pack_type_code" class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-amber-300 font-mono outline-none focus:border-amber-500" placeholder="Bale / Sack / Box / C48">
                        @error('pack_type_code') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-zinc-300 mb-1">Nama Jenis Kemasan <span class="text-red-400">*</span></label>
                        <input type="text" wire:model="pack_type_name" class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none focus:border-amber-500" placeholder="Box / C48 / Sack (Karung)">
                        @error('pack_type_name') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Deskripsi / Keterangan Kemasan</label>
                    <input type="text" wire:model="pack_type_description" class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none" placeholder="Keterangan jenis kemasan">
                    @error('pack_type_description') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="pt-1">
                    <label class="flex items-center space-x-2 cursor-pointer select-none">
                        <input type="checkbox" wire:model="pack_type_is_active" class="rounded border-zinc-700 text-amber-600 focus:ring-amber-500 w-4 h-4 bg-zinc-950">
                        <span class="text-xs font-bold text-zinc-200">Status Aktif (Tampilkan di opsi dropdown Buat Batch)</span>
                    </label>
                </div>

                <div class="flex justify-end space-x-3 pt-3 border-t border-zinc-800">
                    <button type="button" wire:click="$set('showPackTypeModal', false)" class="px-4 py-2.5 rounded-xl bg-zinc-800 text-zinc-300 font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white font-black">Simpan Jenis Kemasan</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
