<!DOCTYPE html>
<html lang="id" class="dark h-full bg-zinc-950 text-zinc-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#18181b">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    
    <link rel="icon" type="image/jpeg" href="/images/icontobacco.jpeg">
    <link rel="shortcut icon" href="/images/icontobacco.jpeg">
    <title>{{ $title ?? 'TPMS' }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Chart.js for Historical Trend Line Chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .swal2-container {
            z-index: 999999 !important;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-zinc-950 font-sans antialiased selection:bg-amber-600 selection:text-white pb-24">
    <div x-data="{ 
        sidebarOpen: false, 
        customerSubmenuOpen: {{ request()->routeIs('customer.dashboard') ? 'true' : 'false' }}, 
        activeCustomerTab: '{{ request('activeTab', 'batch_overview') }}',
        masterDataSubmenuOpen: {{ request()->routeIs('admin.master-data*') ? 'true' : 'false' }},
        activeMasterDataTab: '{{ request('activeTab', 'customers') }}'
    }" 
    x-on:customer-tab-changed.window="activeCustomerTab = $event.detail"
    x-on:master-data-tab-changed.window="activeMasterDataTab = $event.detail"
    class="min-h-screen flex flex-col md:flex-row bg-zinc-950">
        
        <!-- Mobile Header Bar -->
        <header class="md:hidden flex items-center justify-between px-4 py-3 bg-zinc-900 border-b border-zinc-800 sticky top-0 z-40">
            <div class="flex items-center space-x-2">
                <img src="/images/icontobacco.jpeg" alt="Tobacco Logo" class="w-8 h-8 object-contain rounded-lg shadow">
                <span class="font-black text-base tracking-wider text-amber-500">TPMS <span class="text-zinc-300 font-normal text-xs">PWA</span></span>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" class="p-2 min-w-[48px] min-h-[48px] rounded-xl bg-zinc-800 text-zinc-300 hover:text-white focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </header>

        <!-- Sidebar Navigation -->
        <aside :class="sidebarOpen ? 'block' : 'hidden md:block'" class="w-full md:w-64 bg-zinc-900 border-r border-zinc-800 flex flex-col shrink-0">
            <!-- Brand Logo -->
            <div class="p-5 border-b border-zinc-800 hidden md:flex items-center space-x-3">
                <img src="/images/icontobacco.jpeg" alt="Tobacco Logo" class="w-10 h-10 object-contain rounded-xl shadow-lg shadow-amber-900/30 border border-amber-500/20">
                <div>
                    <h1 class="font-black text-base tracking-wide text-amber-400">Tobacco Production Management System</h1>
                    <p class="text-[10px] uppercase font-bold text-zinc-400 tracking-wider">Production & Quality PWA</p>
                </div>
            </div>

            <!-- Active User Card -->
            <div class="p-4 mx-3 my-3 bg-zinc-950 border border-zinc-800 rounded-2xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-zinc-200 truncate">{{ auth()->user()->name ?? 'Pengguna' }}</p>
                        <span class="inline-block px-2.5 py-0.5 mt-1 text-[10px] font-black tracking-wider uppercase rounded-full bg-amber-950 text-amber-300 border border-amber-800">
                            {{ strtoupper(auth()->user()->role ?? 'USER') }}
                        </span>
                        @if(auth()->user() && auth()->user()->shift)
                            <span class="text-[10px] text-zinc-400 block mt-0.5 font-mono">{{ auth()->user()->shift }} ({{ auth()->user()->group }})</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Navigation Links per Role -->
            <nav class="flex-1 px-3 space-y-2 py-2">
                <!-- 1. Karyawan (Worker) Menu -->
                @if(auth()->user() && (auth()->user()->isKaryawan() || auth()->user()->isAdmin() || auth()->user()->isSupervisor()))
                <a href="{{ route('karyawan.weighing') }}" class="flex items-center px-4 py-3 min-h-[48px] text-sm font-bold rounded-xl transition-all {{ request()->routeIs('karyawan.weighing') ? 'bg-amber-600 text-white shadow-md shadow-amber-900/30' : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-200' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 18h12l3-18H3zm3 0V4a2 2 0 012-2h8a2 2 0 012 2v2"></path></svg>
                    Lembar Timbangan Karung
                </a>
                @endif

                <!-- 2. Admin & Supervisor Management Menus -->
                @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isSupervisor()))
                <a href="{{ route('admin.batches') }}" class="flex items-center px-4 py-3 min-h-[48px] text-sm font-bold rounded-xl transition-all {{ request()->routeIs('admin.batches') ? 'bg-amber-600 text-white shadow-md shadow-amber-900/30' : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-200' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Manajemen Batch & MRL
                </a>

                <a href="{{ route('admin.dn-shipments') }}" class="flex items-center px-4 py-3 min-h-[48px] text-sm font-bold rounded-xl transition-all {{ request()->routeIs('admin.dn-shipments*') ? 'bg-amber-600 text-white shadow-md shadow-amber-900/30' : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-200' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    DN Shipment (Surat Jalan)
                </a>

                <a href="{{ route('admin.tracking') }}" class="flex items-center px-4 py-3 min-h-[48px] text-sm font-bold rounded-xl transition-all {{ request()->routeIs('admin.tracking') ? 'bg-amber-600 text-white shadow-md shadow-amber-900/30' : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-200' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Live Tracking Worker
                </a>

                <!-- Kelola Master Data Menu with Nested Submenu -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between px-4 py-3 min-h-[48px] text-sm font-bold rounded-xl transition-all cursor-pointer select-none {{ request()->routeIs('admin.master-data*') ? 'bg-amber-600/20 text-amber-300 border border-amber-500/30' : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-200' }}"
                         @click="masterDataSubmenuOpen = !masterDataSubmenuOpen">
                        <a href="{{ route('admin.master-data') }}" 
                           class="flex items-center flex-1 text-inherit"
                           @click.stop="masterDataSubmenuOpen = true">
                            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                            <span>Kelola Master Data</span>
                        </a>
                        <button type="button" class="p-1 text-zinc-400 hover:text-zinc-200">
                            <svg :class="masterDataSubmenuOpen ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </div>

                    <!-- Collapsible Master Data Submenu Items -->
                    <div x-show="masterDataSubmenuOpen" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="pl-3 pr-1 space-y-1 pt-1 border-l-2 border-amber-600/30 ml-4">
                        
                        <!-- 1. Pelanggan (Customer) -->
                        <a href="{{ route('admin.master-data', ['activeTab' => 'customers']) }}"
                           @if(request()->routeIs('admin.master-data*'))
                           @click.prevent="activeMasterDataTab = 'customers'; sidebarOpen = false; $dispatch('switch-master-data-tab', 'customers'); window.history.replaceState({}, '', '{{ route('admin.master-data') }}?activeTab=customers')"
                           @else
                           @click="sidebarOpen = false"
                           @endif
                           :class="activeMasterDataTab === 'customers' ? 'bg-amber-600 text-white font-black shadow-md shadow-amber-900/30' : 'text-zinc-400 hover:bg-zinc-800/80 hover:text-zinc-200'"
                           class="flex items-center px-3 py-2.5 rounded-xl text-xs font-semibold transition-all">
                             <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span class="truncate">Pelanggan</span>
                        </a>

                        <!-- 2. Jenis Produk (Product Type) -->
                        <a href="{{ route('admin.master-data', ['activeTab' => 'products']) }}"
                           @if(request()->routeIs('admin.master-data*'))
                           @click.prevent="activeMasterDataTab = 'products'; sidebarOpen = false; $dispatch('switch-master-data-tab', 'products'); window.history.replaceState({}, '', '{{ route('admin.master-data') }}?activeTab=products')"
                           @else
                           @click="sidebarOpen = false"
                           @endif
                           :class="activeMasterDataTab === 'products' ? 'bg-amber-600 text-white font-black shadow-md shadow-amber-900/30' : 'text-zinc-400 hover:bg-zinc-800/80 hover:text-zinc-200'"
                           class="flex items-center px-3 py-2.5 rounded-xl text-xs font-semibold transition-all">
                             <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"></path></svg>
                            <span class="truncate">Jenis Produk</span>
                        </a>

                        <!-- 3. Asal Tembakau (Primary Origin) -->
                        <a href="{{ route('admin.master-data', ['activeTab' => 'origins']) }}"
                           @if(request()->routeIs('admin.master-data*'))
                           @click.prevent="activeMasterDataTab = 'origins'; sidebarOpen = false; $dispatch('switch-master-data-tab', 'origins'); window.history.replaceState({}, '', '{{ route('admin.master-data') }}?activeTab=origins')"
                           @else
                           @click="sidebarOpen = false"
                           @endif
                           :class="activeMasterDataTab === 'origins' ? 'bg-amber-600 text-white font-black shadow-md shadow-amber-900/30' : 'text-zinc-400 hover:bg-zinc-800/80 hover:text-zinc-200'"
                           class="flex items-center px-3 py-2.5 rounded-xl text-xs font-semibold transition-all">
                             <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span class="truncate">Asal Tembakau</span>
                        </a>

                        <!-- 4. Jenis Muatan (Material Type) -->
                        <a href="{{ route('admin.master-data', ['activeTab' => 'materials']) }}"
                           @if(request()->routeIs('admin.master-data*'))
                           @click.prevent="activeMasterDataTab = 'materials'; sidebarOpen = false; $dispatch('switch-master-data-tab', 'materials'); window.history.replaceState({}, '', '{{ route('admin.master-data') }}?activeTab=materials')"
                           @else
                           @click="sidebarOpen = false"
                           @endif
                           :class="activeMasterDataTab === 'materials' ? 'bg-amber-600 text-white font-black shadow-md shadow-amber-900/30' : 'text-zinc-400 hover:bg-zinc-800/80 hover:text-zinc-200'"
                           class="flex items-center px-3 py-2.5 rounded-xl text-xs font-semibold transition-all">
                             <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            <span class="truncate">Jenis Muatan</span>
                        </a>
                    </div>
                </div>
                @endif

                @if(auth()->user() && auth()->user()->isAdmin())
                <a href="{{ route('admin.users') }}" class="flex items-center px-4 py-3 min-h-[48px] text-sm font-bold rounded-xl transition-all {{ request()->routeIs('admin.users') ? 'bg-amber-600 text-white shadow-md shadow-amber-900/30' : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-200' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Pengguna & Shift Setup
                </a>
                @endif

                <!-- 3. Customer Portal Menu with Nested Submenu -->
                @if(auth()->user() && (auth()->user()->isCustomer() || auth()->user()->isAdmin() || auth()->user()->isSupervisor()))
                <div class="space-y-1">
                    <div class="flex items-center justify-between px-4 py-3 min-h-[48px] text-sm font-bold rounded-xl transition-all cursor-pointer select-none {{ request()->routeIs('customer.dashboard') ? 'bg-amber-600/20 text-amber-300 border border-amber-500/30' : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-200' }}"
                         @click="customerSubmenuOpen = !customerSubmenuOpen">
                        <a href="{{ route('customer.dashboard') }}" 
                           class="flex items-center flex-1 text-inherit"
                           @click.stop="customerSubmenuOpen = true">
                            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span>Portal Customer</span>
                        </a>
                        <button type="button" class="p-1 text-zinc-400 hover:text-zinc-200">
                            <svg :class="customerSubmenuOpen ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </div>

                    <!-- Collapsible Customer Submenu Items -->
                    <div x-show="customerSubmenuOpen" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="pl-3 pr-1 space-y-1 pt-1 border-l-2 border-amber-600/30 ml-4">
                        
                        <!-- 1. Batch Overview -->
                        <a href="{{ route('customer.dashboard', ['activeTab' => 'batch_overview']) }}"
                           @if(request()->routeIs('customer.dashboard'))
                           @click.prevent="activeCustomerTab = 'batch_overview'; sidebarOpen = false; $dispatch('switch-customer-tab', 'batch_overview'); window.history.replaceState({}, '', '{{ route('customer.dashboard') }}?activeTab=batch_overview')"
                           @else
                           @click="sidebarOpen = false"
                           @endif
                           :class="activeCustomerTab === 'batch_overview' ? 'bg-amber-600 text-white font-black shadow-md shadow-amber-900/30' : 'text-zinc-400 hover:bg-zinc-800/80 hover:text-zinc-200'"
                           class="flex items-center px-3 py-2.5 rounded-xl text-xs font-semibold transition-all">
                            <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            <span class="truncate">Batch Overview</span>
                        </a>

                        <!-- 2. Historical Analytics -->
                        <a href="{{ route('customer.dashboard', ['activeTab' => 'historical_analytics']) }}"
                           @if(request()->routeIs('customer.dashboard'))
                           @click.prevent="activeCustomerTab = 'historical_analytics'; sidebarOpen = false; $dispatch('switch-customer-tab', 'historical_analytics'); window.history.replaceState({}, '', '{{ route('customer.dashboard') }}?activeTab=historical_analytics')"
                           @else
                           @click="sidebarOpen = false"
                           @endif
                           :class="activeCustomerTab === 'historical_analytics' ? 'bg-amber-600 text-white font-black shadow-md shadow-amber-900/30' : 'text-zinc-400 hover:bg-zinc-800/80 hover:text-zinc-200'"
                           class="flex items-center px-3 py-2.5 rounded-xl text-xs font-semibold transition-all">
                            <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            <span class="truncate">Historical Analytics</span>
                        </a>

                        <!-- 3. Receiving Reconciliation -->
                        <a href="{{ route('customer.dashboard', ['activeTab' => 'reconciliation']) }}"
                           @if(request()->routeIs('customer.dashboard'))
                           @click.prevent="activeCustomerTab = 'reconciliation'; sidebarOpen = false; $dispatch('switch-customer-tab', 'reconciliation'); window.history.replaceState({}, '', '{{ route('customer.dashboard') }}?activeTab=reconciliation')"
                           @else
                           @click="sidebarOpen = false"
                           @endif
                           :class="activeCustomerTab === 'reconciliation' ? 'bg-amber-600 text-white font-black shadow-md shadow-amber-900/30' : 'text-zinc-400 hover:bg-zinc-800/80 hover:text-zinc-200'"
                           class="flex items-center px-3 py-2.5 rounded-xl text-xs font-semibold transition-all">
                            <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="truncate">Receiving Reconciliation</span>
                        </a>

                        <!-- 4. Batch Traceability -->
                        <a href="{{ route('customer.dashboard', ['activeTab' => 'traceability']) }}"
                           @if(request()->routeIs('customer.dashboard'))
                           @click.prevent="activeCustomerTab = 'traceability'; sidebarOpen = false; $dispatch('switch-customer-tab', 'traceability'); window.history.replaceState({}, '', '{{ route('customer.dashboard') }}?activeTab=traceability')"
                           @else
                           @click="sidebarOpen = false"
                           @endif
                           :class="activeCustomerTab === 'traceability' ? 'bg-amber-600 text-white font-black shadow-md shadow-amber-900/30' : 'text-zinc-400 hover:bg-zinc-800/80 hover:text-zinc-200'"
                           class="flex items-center px-3 py-2.5 rounded-xl text-xs font-semibold transition-all">
                            <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                            <span class="truncate">Batch Traceability</span>
                        </a>

                        <!-- 5. Certificates -->
                        <a href="{{ route('customer.dashboard', ['activeTab' => 'certificates']) }}"
                           @if(request()->routeIs('customer.dashboard'))
                           @click.prevent="activeCustomerTab = 'certificates'; sidebarOpen = false; $dispatch('switch-customer-tab', 'certificates'); window.history.replaceState({}, '', '{{ route('customer.dashboard') }}?activeTab=certificates')"
                           @else
                           @click="sidebarOpen = false"
                           @endif
                           :class="activeCustomerTab === 'certificates' ? 'bg-amber-600 text-white font-black shadow-md shadow-amber-900/30' : 'text-zinc-400 hover:bg-zinc-800/80 hover:text-zinc-200'"
                           class="flex items-center px-3 py-2.5 rounded-xl text-xs font-semibold transition-all">
                            <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            <span class="truncate">Certificates</span>
                        </a>

                        <!-- 6. Yield Cost Calculator (Placed below Certificates) -->
                        <a href="{{ route('customer.dashboard', ['activeTab' => 'yield_calculator']) }}"
                           @if(request()->routeIs('customer.dashboard'))
                           @click.prevent="activeCustomerTab = 'yield_calculator'; sidebarOpen = false; $dispatch('switch-customer-tab', 'yield_calculator'); window.history.replaceState({}, '', '{{ route('customer.dashboard') }}?activeTab=yield_calculator')"
                           @else
                           @click="sidebarOpen = false"
                           @endif
                           :class="activeCustomerTab === 'yield_calculator' ? 'bg-amber-600 text-white font-black shadow-md shadow-amber-900/30' : 'text-zinc-400 hover:bg-zinc-800/80 hover:text-zinc-200'"
                           class="flex items-center px-3 py-2.5 rounded-xl text-xs font-semibold transition-all">
                            <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            <span class="truncate">Yield Cost Calculator</span>
                        </a>

                        <!-- 7. DN Shipment (Surat Jalan Pengiriman & Approval) -->
                        <a href="{{ route('customer.dashboard', ['activeTab' => 'dn_shipments']) }}"
                           @if(request()->routeIs('customer.dashboard'))
                           @click.prevent="activeCustomerTab = 'dn_shipments'; sidebarOpen = false; $dispatch('switch-customer-tab', 'dn_shipments'); window.history.replaceState({}, '', '{{ route('customer.dashboard') }}?activeTab=dn_shipments')"
                           @else
                           @click="sidebarOpen = false"
                           @endif
                           :class="activeCustomerTab === 'dn_shipments' ? 'bg-amber-600 text-white font-black shadow-md shadow-amber-900/30' : 'text-zinc-400 hover:bg-zinc-800/80 hover:text-zinc-200'"
                           class="flex items-center px-3 py-2.5 rounded-xl text-xs font-semibold transition-all">
                            <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            <span class="truncate">DN Shipment (Surat Jalan)</span>
                        </a>
                    </div>
                </div>
                @endif
            </nav>

            <!-- Logout -->
            <div class="p-4 border-t border-zinc-800">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center px-4 py-3 min-h-[48px] rounded-xl text-sm font-bold bg-zinc-800 text-zinc-300 hover:bg-red-950 hover:text-red-400 hover:border hover:border-red-800 transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Keluar (Logout)
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 p-4 md:p-8 overflow-y-auto">
            <!-- Flash Message Banner -->
            @if (session()->has('message'))
                <div x-data="{ show: true }" x-show="show" class="mb-6 p-4 rounded-2xl bg-emerald-950/90 border border-emerald-700/80 text-emerald-200 flex items-center justify-between shadow-lg">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="font-bold text-sm">{{ session('message') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-400 hover:text-white p-2 min-w-[44px] min-h-[44px]">
                        &times;
                    </button>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(reg => {
                    console.log('Tobacco PWA Service Worker Registered:', reg.scope);
                }).catch(err => {
                    console.warn('PWA SW Register Error:', err);
                });
            });
        }
    </script>

    @livewireScripts
    <script>
        function triggerGlobalErrorModal(title, text) {
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: title || 'Terjadi Kendala Sistem',
                    text: text || 'Terjadi kesalahan pada server. Silakan coba beberapa saat lagi.',
                    background: '#18181b',
                    color: '#f4f4f5',
                    confirmButtonColor: '#d97706',
                    confirmButtonText: 'Tutup',
                    heightAuto: false
                });
            } else {
                alert((title || 'Terjadi Kendala Sistem') + '\n' + (text || 'Silakan coba lagi.'));
            }
        }

        document.addEventListener('livewire:init', () => {
            Livewire.hook('request', ({ fail }) => {
                fail(({ status, content, preventDefault }) => {
                    if (status >= 400) {
                        preventDefault();
                        
                        // Handle 419 Session / CSRF Token Expired
                        if (status === 419) {
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Sesi Telah Berakhir',
                                    html: '<div class="text-sm text-zinc-300 space-y-2"><p>Sesi portal Anda telah kedaluwarsa demi keamanan sistem.</p><p class="text-amber-400 font-semibold text-xs">Halaman akan otomatis dimuat ulang untuk menyegarkan sesi...</p></div>',
                                    background: '#18181b',
                                    color: '#f4f4f5',
                                    confirmButtonColor: '#d97706',
                                    confirmButtonText: 'Muat Ulang Sekarang',
                                    timer: 4000,
                                    timerProgressBar: true,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    heightAuto: false
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                alert('Sesi telah berakhir. Halaman akan dimuat ulang.');
                                window.location.reload();
                            }
                            return;
                        }

                        // Handle 401 Unauthorized
                        if (status === 401) {
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Sesi Login Berakhir',
                                    text: 'Silakan login kembali untuk melanjutkan.',
                                    background: '#18181b',
                                    color: '#f4f4f5',
                                    confirmButtonColor: '#d97706',
                                    confirmButtonText: 'Ke Halaman Login',
                                    heightAuto: false
                                }).then(() => {
                                    window.location.href = '{{ route("login") }}';
                                });
                            } else {
                                window.location.href = '{{ route("login") }}';
                            }
                            return;
                        }

                        // Handle 403 Forbidden
                        if (status === 403) {
                            triggerGlobalErrorModal('Akses Ditolak', 'Anda tidak memiliki wewenang untuk melakukan aksi ini.');
                            return;
                        }

                        // Handle 404 Not Found
                        if (status === 404) {
                            triggerGlobalErrorModal('Data Tidak Ditemukan', 'Halaman atau data yang diminta tidak dapat ditemukan.');
                            return;
                        }

                        // Handle other 500 / 4xx server errors
                        let title = 'Terjadi Kendala Sistem (Status ' + status + ')';
                        let text = 'Terjadi kesalahan internal pada server. Silakan coba beberapa saat lagi atau hubungi administrator.';
                        triggerGlobalErrorModal(title, text);
                    }
                });
            });
        });

        // Background Session Keep-Alive & CSRF Synchronizer
        (function() {
            let isPinging = false;
            function pingSessionKeepAlive() {
                if (isPinging) return;
                isPinging = true;

                fetch('{{ route("session.keep-alive") }}', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(res => {
                    if (res.status === 419 || res.status === 401) {
                        console.warn('[Session] Session expired, auto-refreshing...');
                        window.location.reload();
                        return null;
                    }
                    return res.json();
                })
                .then(data => {
                    if (data && data.csrf_token) {
                        // Sync meta csrf token
                        const meta = document.querySelector('meta[name="csrf-token"]');
                        if (meta) {
                            meta.setAttribute('content', data.csrf_token);
                        }
                        // Sync axios default header if available
                        if (window.axios) {
                            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = data.csrf_token;
                        }
                    }
                })
                .catch(err => {
                    console.debug('[Session Keep-Alive] Network heartbeat debug:', err);
                })
                .finally(() => {
                    isPinging = false;
                });
            }

            // Periodic heartbeat every 10 minutes (600,000 ms)
            setInterval(pingSessionKeepAlive, 600000);

            // Trigger heartbeat when user returns to tab
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') {
                    pingSessionKeepAlive();
                }
            });

            // Trigger heartbeat on window focus
            window.addEventListener('focus', () => {
                pingSessionKeepAlive();
            });
        })();
    </script>
</body>
</html>
