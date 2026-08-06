<!DOCTYPE html>
<html lang="id" class="dark h-full bg-zinc-950 text-zinc-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#18181b">
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
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex flex-col md:flex-row bg-zinc-950">
        
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

                <a href="{{ route('admin.tracking') }}" class="flex items-center px-4 py-3 min-h-[48px] text-sm font-bold rounded-xl transition-all {{ request()->routeIs('admin.tracking') ? 'bg-amber-600 text-white shadow-md shadow-amber-900/30' : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-200' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Live Tracking Worker
                </a>

                <a href="{{ route('admin.master-data') }}" class="flex items-center px-4 py-3 min-h-[48px] text-sm font-bold rounded-xl transition-all {{ request()->routeIs('admin.master-data') ? 'bg-amber-600 text-white shadow-md shadow-amber-900/30' : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-200' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                    Kelola Master Data
                </a>
                @endif

                @if(auth()->user() && auth()->user()->isAdmin())
                <a href="{{ route('admin.users') }}" class="flex items-center px-4 py-3 min-h-[48px] text-sm font-bold rounded-xl transition-all {{ request()->routeIs('admin.users') ? 'bg-amber-600 text-white shadow-md shadow-amber-900/30' : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-200' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Pengguna & Shift Setup
                </a>
                @endif

                <!-- 3. Customer Portal Menu -->
                @if(auth()->user() && (auth()->user()->isCustomer() || auth()->user()->isAdmin() || auth()->user()->isSupervisor()))
                <a href="{{ route('customer.dashboard') }}" class="flex items-center px-4 py-3 min-h-[48px] text-sm font-bold rounded-xl transition-all {{ request()->routeIs('customer.dashboard') ? 'bg-amber-600 text-white shadow-md shadow-amber-900/30' : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-200' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Portal Customer
                </a>
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
                        
                        let title = 'Terjadi Kendala Sistem (Status ' + status + ')';
                        let text = 'Terjadi kesalahan internal pada server. Silakan coba beberapa saat lagi atau hubungi administrator.';

                        if (status === 403) {
                            title = 'Akses Ditolak';
                            text = 'Anda tidak memiliki wewenang untuk melakukan aksi ini.';
                        } else if (status === 404) {
                            title = 'Data Tidak Ditemukan';
                            text = 'Halaman atau data yang diminta tidak dapat ditemukan.';
                        }

                        triggerGlobalErrorModal(title, text);
                    }
                });
            });
        });
    </script>
</body>
</html>
