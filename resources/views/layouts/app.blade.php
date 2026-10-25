<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ app_name() }}</title>

    <!-- PWA & Mobile Web App Meta -->
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <meta name="theme-color" content="#064e3b">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Warung Aboen">
    <link rel="icon" type="image/png" href="/icons/icon-192.png">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                            950: '#022c22',
                        },
                        enterprise: {
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#090d16',
                        }
                    },
                    boxShadow: {
                        'glow-emerald': '0 0 25px -5px rgba(16, 185, 129, 0.25)',
                        'glow-teal': '0 0 25px -5px rgba(20, 184, 166, 0.25)',
                        'card': '0 2px 10px -2px rgba(15, 23, 42, 0.05), 0 1px 3px -1px rgba(15, 23, 42, 0.03)',
                        'card-hover': '0 12px 28px -4px rgba(15, 23, 42, 0.08), 0 4px 10px -2px rgba(15, 23, 42, 0.04)',
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            letter-spacing: -0.011em;
        }

        /* Enterprise scrollbars */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Glassmorphism helpers */
        .glass-panel {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .glass-dark {
            background: rgba(15, 23, 42, 0.88);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        /* Official Print Styles */
        @media print {
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            body {
                background: white !important;
                color: #000 !important;
                font-size: 11pt !important;
            }
            .print-card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
                break-inside: avoid;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="text-slate-800 antialiased h-full flex flex-col bg-slate-50 selection:bg-brand-500 selection:text-white"
      x-data="globalApp()"
      @keydown.window.prevent.ctrl.k="quickSearchOpen = true"
      @keydown.window.prevent.slash="quickSearchOpen = true">

    <div class="flex h-screen overflow-hidden">
        
        <!-- Mobile Backdrop for Sidebar -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
             class="fixed inset-0 z-40 bg-slate-950/70 backdrop-blur-xs md:hidden transition-opacity"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        <!-- Enterprise Dark Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-950 text-slate-300 transition-all duration-300 transform md:relative md:translate-x-0 flex flex-col justify-between border-r border-slate-800/80 shadow-2xl"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
            
            <!-- Sidebar Header & Nav -->
            <div class="flex flex-col flex-1 min-h-0">
                
                <!-- Logo & Brand Header -->
                <div class="h-20 flex items-center justify-between px-5 border-b border-slate-800/80 bg-slate-950/70">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 via-emerald-500 to-teal-400 flex items-center justify-center text-white shadow-lg shadow-emerald-500/25 group-hover:scale-105 transition-all">
                            <i class="fa-solid fa-store text-lg"></i>
                        </div>
                        <div class="overflow-hidden">
                            <span class="font-extrabold text-base text-white tracking-tight block truncate leading-tight group-hover:text-emerald-400 transition-colors">
                                {{ app_name() }}
                            </span>
                            <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest block flex items-center space-x-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block animate-pulse"></span>
                                <span>Enterprise Warung</span>
                            </span>
                        </div>
                    </a>
                    <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white p-2">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Fast Action: Point of Sale Terminal Button -->
                <div class="p-3.5 pb-2">
                    <a href="{{ route('pos.index') }}" 
                       class="flex items-center justify-between w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white font-bold text-xs shadow-lg shadow-emerald-500/20 transition-all transform hover:-translate-y-0.5 active:translate-y-0 group">
                        <span class="flex items-center space-x-2.5">
                            <i class="fa-solid fa-cash-register text-base group-hover:scale-110 transition-transform"></i>
                            <span class="tracking-wide">KASIR (POS)</span>
                        </span>
                        <span class="px-1.5 py-0.5 rounded bg-white/20 text-[10px] font-mono uppercase">F9</span>
                    </a>
                </div>

                <!-- Kasir Offline Mandiri HP (Tanpa Perlu Laptop) -->
                <div class="px-3.5 pb-2">
                    <a href="/kasir-hp.html" target="_blank"
                       class="flex items-center justify-between w-full py-2.5 px-3 rounded-xl bg-slate-900 border border-emerald-500/40 hover:border-emerald-400 hover:bg-slate-800 text-emerald-300 font-bold text-xs shadow-sm transition-all group">
                        <span class="flex items-center space-x-2.5">
                            <i class="fa-solid fa-mobile-screen-button text-sm text-emerald-400 group-hover:scale-110 transition-transform"></i>
                            <span>Kasir Mandiri HP</span>
                        </span>
                        <span class="px-1.5 py-0.5 rounded bg-emerald-500/20 text-[9px] text-emerald-300 font-bold uppercase tracking-wider">Offline</span>
                    </a>
                </div>

                <!-- Navigation Menu -->
                <nav class="flex-1 px-3 space-y-1 overflow-y-auto pb-4 text-xs">
                    
                    <!-- UTAMA -->
                    <div class="px-3 pt-2 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        Ringkasan
                    </div>

                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl font-semibold transition-all {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <i class="fa-solid fa-chart-pie w-4 text-center text-sm {{ request()->routeIs('dashboard') ? 'text-white' : 'text-emerald-400' }}"></i>
                        <span>Dashboard Utama</span>
                    </a>

                    <a href="{{ route('sales.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl font-semibold transition-all {{ request()->routeIs('sales.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <i class="fa-solid fa-receipt w-4 text-center text-sm {{ request()->routeIs('sales.*') ? 'text-white' : 'text-teal-400' }}"></i>
                        <span>Riwayat Penjualan</span>
                    </a>

                    <!-- PERSEDIAAN -->
                    <div class="px-3 pt-4 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        Persediaan & Kulak
                    </div>

                    <a href="{{ route('products.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl font-semibold transition-all {{ request()->routeIs('products.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <span class="flex items-center space-x-3">
                            <i class="fa-solid fa-boxes-stacked w-4 text-center text-sm {{ request()->routeIs('products.*') ? 'text-white' : 'text-indigo-400' }}"></i>
                            <span>Barang & Stok</span>
                        </span>
                        @php
                            $lowCount = \App\Models\Product::where('is_active', true)->whereColumn('stock', '<=', 'min_stock')->count();
                        @endphp
                        @if($lowCount > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-extrabold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                            {{ $lowCount }}
                        </span>
                        @endif
                    </a>

                    <a href="{{ route('categories.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl font-semibold transition-all {{ request()->routeIs('categories.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <i class="fa-solid fa-tags w-4 text-center text-sm {{ request()->routeIs('categories.*') ? 'text-white' : 'text-sky-400' }}"></i>
                        <span>Kategori Barang</span>
                    </a>

                    <a href="{{ route('purchases.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl font-semibold transition-all {{ request()->routeIs('purchases.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <i class="fa-solid fa-truck-ramp-box w-4 text-center text-sm {{ request()->routeIs('purchases.*') ? 'text-white' : 'text-amber-400' }}"></i>
                        <span>Pembelian (Kulak Stok)</span>
                    </a>

                    <!-- KEUANGAN -->
                    <div class="px-3 pt-4 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        Buku Kas & Biaya
                    </div>

                    <a href="{{ route('expenses.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl font-semibold transition-all {{ request()->routeIs('expenses.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <i class="fa-solid fa-money-bill-transfer w-4 text-center text-sm {{ request()->routeIs('expenses.*') ? 'text-white' : 'text-rose-400' }}"></i>
                        <span>Biaya Operasional</span>
                    </a>

                    <a href="{{ route('cash.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl font-semibold transition-all {{ request()->routeIs('cash.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <i class="fa-solid fa-wallet w-4 text-center text-sm {{ request()->routeIs('cash.*') ? 'text-white' : 'text-emerald-400' }}"></i>
                        <span>Buku Kas & Modal</span>
                    </a>

                    <!-- LAPORAN BISNIS -->
                    <div class="px-3 pt-4 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        Laporan Enterprise
                    </div>

                    <a href="{{ route('reports.sales') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl font-semibold transition-all {{ request()->routeIs('reports.sales') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <i class="fa-solid fa-file-invoice-dollar w-4 text-center text-sm {{ request()->routeIs('reports.sales') ? 'text-white' : 'text-blue-400' }}"></i>
                        <span>Laporan Penjualan</span>
                    </a>

                    <a href="{{ route('reports.stock') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl font-semibold transition-all {{ request()->routeIs('reports.stock') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <i class="fa-solid fa-warehouse w-4 text-center text-sm {{ request()->routeIs('reports.stock') ? 'text-white' : 'text-violet-400' }}"></i>
                        <span>Laporan Persediaan & Aset</span>
                    </a>

                    @if(auth()->user()->isOwner())
                    <a href="{{ route('reports.profit_loss') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl font-semibold transition-all {{ request()->routeIs('reports.profit_loss') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <i class="fa-solid fa-scale-balanced w-4 text-center text-sm {{ request()->routeIs('reports.profit_loss') ? 'text-white' : 'text-emerald-400' }}"></i>
                        <span>Laporan Laba Rugi</span>
                    </a>

                    <a href="{{ route('reports.cash_flow') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl font-semibold transition-all {{ request()->routeIs('reports.cash_flow') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <i class="fa-solid fa-arrow-trend-up w-4 text-center text-sm {{ request()->routeIs('reports.cash_flow') ? 'text-white' : 'text-teal-400' }}"></i>
                        <span>Laporan Arus Kas</span>
                    </a>

                    <!-- SISTEM & PENGATURAN -->
                    <div class="px-3 pt-4 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        Sistem & Pengaturan
                    </div>

                    <a href="{{ route('settings.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl font-semibold transition-all {{ request()->routeIs('settings.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <i class="fa-solid fa-sliders w-4 text-center text-sm {{ request()->routeIs('settings.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Pengaturan Toko</span>
                    </a>

                    <a href="{{ route('users.index') }}" class="flex items-center space-x-3 px-3 py-2 rounded-xl font-semibold transition-all {{ request()->routeIs('users.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        <i class="fa-solid fa-users-gear w-4 text-center text-sm {{ request()->routeIs('users.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Kelola Pengguna & Kasir</span>
                    </a>
                    @endif

                    <!-- PWA Quick Install / Petunjuk HP & Laptop -->
                    <button @click="triggerPwaInstall()" type="button" class="w-full flex items-center space-x-3 px-3 py-2 rounded-xl font-bold transition-all text-emerald-400 hover:bg-emerald-950/60 hover:text-emerald-300 group text-left">
                        <i class="fa-solid fa-mobile-screen-button w-4 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span>Pasang Aplikasi (HP & Laptop)</span>
                    </button>

                </nav>
            </div>

            <!-- User Status Footer in Sidebar -->
            <div class="p-3.5 border-t border-slate-800/80 bg-slate-950/90 flex items-center justify-between">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-slate-800 to-slate-700 border border-slate-600/50 flex items-center justify-center font-bold text-emerald-400 shrink-0 text-sm shadow-inner">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-bold text-white truncate leading-tight">{{ auth()->user()->name }}</p>
                        <span class="inline-flex items-center space-x-1 text-[10px] font-bold {{ auth()->user()->isOwner() ? 'text-amber-400' : 'text-emerald-400' }}">
                            <i class="fa-solid {{ auth()->user()->isOwner() ? 'fa-crown text-[9px]' : 'fa-user-tag text-[9px]' }}"></i>
                            <span>{{ auth()->user()->isOwner() ? 'Pemilik (Owner)' : 'Kasir / Keluarga' }}</span>
                        </span>
                    </div>
                </div>

                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" title="Keluar dari sistem" class="text-slate-400 hover:text-rose-400 p-2 rounded-lg hover:bg-slate-900 transition-colors">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>

        </aside>

        <!-- Main Workspace -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-100/70">
            
            <!-- Modern Enterprise Topbar -->
            <header class="h-16 bg-white/95 backdrop-blur-md border-b border-slate-200/80 flex items-center justify-between px-4 sm:px-6 z-20 shadow-sm no-print">
                
                <!-- Left: Title & Quick Search -->
                <div class="flex items-center space-x-4">
                    <button @click="sidebarOpen = true" class="md:hidden text-slate-600 hover:text-slate-900 p-2 rounded-xl hover:bg-slate-100">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>

                    <div>
                        <h1 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight leading-tight flex items-center space-x-2">
                            <span>@yield('page_title', 'Dashboard')</span>
                        </h1>
                        <p class="text-[11px] text-slate-500 hidden sm:block">@yield('page_subtitle', 'Sistem Pembukuan & Kasir Digital Warung Aboen')</p>
                    </div>
                </div>

                <!-- Right: Enterprise Shortcuts, Clock, & Notification -->
                <div class="flex items-center space-x-3">
                    
                    <!-- Search Trigger (Ctrl+K) -->
                    <button @click="quickSearchOpen = true" type="button"
                            class="hidden md:flex items-center space-x-2 px-3 py-1.5 bg-slate-100 hover:bg-slate-200/80 text-slate-500 rounded-xl text-xs font-medium border border-slate-200 transition-colors">
                        <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                        <span>Cari menu / barang...</span>
                        <kbd class="px-1.5 py-0.5 text-[10px] font-mono bg-white border border-slate-300 rounded shadow-xs text-slate-600">Ctrl K</kbd>
                    </button>

                    <!-- Live Clock Badge -->
                    <div class="hidden lg:flex items-center space-x-2 text-xs font-semibold text-slate-700 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200/80">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span id="live-clock" class="font-mono text-[11px] font-bold">{{ date('d M Y, H:i:s') }} WIB</span>
                    </div>

                    <!-- Low Stock Alert Bell -->
                    @if($lowCount > 0)
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" type="button"
                                class="relative p-2 text-amber-600 hover:text-amber-700 bg-amber-50 hover:bg-amber-100/80 rounded-xl border border-amber-200/80 transition-colors"
                                title="{{ $lowCount }} barang stok tipis">
                            <i class="fa-solid fa-bell text-sm"></i>
                            <span class="absolute -top-1 -right-1 w-4 h-4 bg-rose-600 text-white rounded-full text-[9px] font-black flex items-center justify-center border border-white animate-pulse">
                                {{ $lowCount }}
                            </span>
                        </button>

                        <!-- Notification Dropdown -->
                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-2xl border border-slate-200 p-3 z-50 animate-scale-up text-xs">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100 font-bold text-slate-800">
                                <span>Peringatan Stok Menipis</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-rose-100 text-rose-700">{{ $lowCount }} Produk</span>
                            </div>
                            <p class="py-2 text-[11px] text-slate-500">Ada {{ $lowCount }} barang yang stoknya di bawah batas minimum.</p>
                            <a href="{{ route('products.index', ['low_stock' => 1]) }}" class="block w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-center font-bold transition-colors">
                                Kelola & Restock Barang &rarr;
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- Kasir Offline HP Badge/Button -->
                    <a href="/kasir-hp.html" target="_blank"
                       class="flex items-center space-x-1.5 px-2.5 sm:px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 rounded-xl text-xs font-bold transition-all shadow-xs"
                       title="Buka Kasir Offline HP - Bisa dipakai seharian tanpa buka laptop">
                        <i class="fa-solid fa-mobile-screen text-emerald-600"></i>
                        <span class="hidden sm:inline">Kasir HP Offline</span>
                        <span class="sm:hidden">Offline HP</span>
                    </a>

                    <!-- Mobile POS shortcut -->
                    <a href="{{ route('pos.index') }}" class="sm:hidden px-3 py-1.5 bg-emerald-600 text-white rounded-xl text-xs font-bold flex items-center space-x-1.5 shadow-sm">
                        <i class="fa-solid fa-cash-register"></i>
                        <span>Kasir</span>
                    </a>

                </div>
            </header>

            <!-- Scrollable Page Body with Safe Bottom Spacing on Mobile -->
            <main class="flex-1 overflow-y-auto p-3.5 sm:p-6 lg:p-8 pb-28 md:pb-8">
                
                <!-- Flash Alerts -->
                @if(session('success'))
                <div class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start space-x-3 shadow-sm" x-data="{ show: true }" x-show="show">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg mt-0.5"></i>
                    <div class="flex-1 font-semibold text-xs sm:text-sm">
                        {{ session('success') }}
                    </div>
                    <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-5 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start space-x-3 shadow-sm" x-data="{ show: true }" x-show="show">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg mt-0.5"></i>
                    <div class="flex-1 font-semibold text-xs sm:text-sm">
                        {{ session('error') }}
                    </div>
                    <button @click="show = false" class="text-rose-500 hover:text-rose-700">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                @endif

                <!-- Yield Content -->
                @yield('content')
            </main>

            <!-- Native Mobile Bottom App Bar (APK Style for Smartphone / HP) -->
            <nav class="md:hidden fixed bottom-0 inset-x-0 z-30 bg-white/95 backdrop-blur-lg border-t border-slate-200/90 px-2 py-2 flex items-center justify-around shadow-2xl no-print">
                <!-- Beranda -->
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-1 px-2.5 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'text-emerald-600 font-extrabold' : 'text-slate-400 hover:text-slate-700 font-medium' }}">
                    <i class="fa-solid fa-house text-lg"></i>
                    <span class="text-[10px] mt-0.5">Beranda</span>
                </a>

                <!-- Barang & Stok -->
                <a href="{{ route('products.index') }}" class="flex flex-col items-center py-1 px-2.5 rounded-xl transition-all {{ request()->routeIs('products.*') ? 'text-emerald-600 font-extrabold' : 'text-slate-400 hover:text-slate-700 font-medium' }}">
                    <i class="fa-solid fa-boxes-stacked text-lg"></i>
                    <span class="text-[10px] mt-0.5">Barang</span>
                </a>

                <!-- Floating Central POS Kasir Button -->
                <a href="{{ route('pos.index') }}" class="flex flex-col items-center -mt-6 group">
                    <div class="w-14 h-14 rounded-full bg-gradient-to-tr from-emerald-600 via-emerald-500 to-teal-400 text-white flex items-center justify-center text-xl shadow-lg shadow-emerald-500/40 group-active:scale-95 transition-all border-4 border-slate-100">
                        <i class="fa-solid fa-cash-register"></i>
                    </div>
                    <span class="text-[10px] font-black text-emerald-700 mt-0.5">KASIR</span>
                </a>

                <!-- Riwayat Nota -->
                <a href="{{ route('sales.index') }}" class="flex flex-col items-center py-1 px-2.5 rounded-xl transition-all {{ request()->routeIs('sales.*') ? 'text-emerald-600 font-extrabold' : 'text-slate-400 hover:text-slate-700 font-medium' }}">
                    <i class="fa-solid fa-receipt text-lg"></i>
                    <span class="text-[10px] mt-0.5">Nota</span>
                </a>

                <!-- Menu Drawer -->
                <button @click="sidebarOpen = true" type="button" class="flex flex-col items-center py-1 px-2.5 rounded-xl transition-all text-slate-400 hover:text-slate-700 font-medium">
                    <i class="fa-solid fa-bars text-lg"></i>
                    <span class="text-[10px] mt-0.5">Menu</span>
                </button>
            </nav>

        </div>

    </div>

    <!-- Quick Search Modal (Ctrl+K) -->
    <div x-show="quickSearchOpen" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm flex items-start justify-center pt-20 p-4" x-cloak>
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full border border-slate-200 overflow-hidden" @click.away="quickSearchOpen = false">
            <div class="p-4 border-b border-slate-100 flex items-center space-x-3 bg-slate-50/50">
                <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                <input type="text" x-model="searchKeyword" placeholder="Cari menu cepat atau barang dagangan..."
                       class="w-full bg-transparent border-none text-sm font-semibold text-slate-800 focus:outline-none"
                       autofocus>
                <kbd class="px-2 py-0.5 text-[10px] font-mono bg-white border border-slate-200 rounded text-slate-400">ESC</kbd>
            </div>
            
            <div class="p-3 max-h-80 overflow-y-auto space-y-1 text-xs">
                <p class="px-3 py-1 font-bold text-slate-400 uppercase text-[10px]">Navigasi Cepat</p>
                <a href="{{ route('pos.index') }}" class="flex items-center space-x-3 p-2.5 rounded-xl hover:bg-emerald-50 hover:text-emerald-800 transition-colors">
                    <i class="fa-solid fa-cash-register text-emerald-600 w-5 text-center"></i>
                    <span class="font-bold">Buka Kasir (POS)</span>
                </a>
                <a href="{{ route('sales.index') }}" class="flex items-center space-x-3 p-2.5 rounded-xl hover:bg-emerald-50 hover:text-emerald-800 transition-colors">
                    <i class="fa-solid fa-receipt text-teal-600 w-5 text-center"></i>
                    <span class="font-bold">Riwayat Penjualan</span>
                </a>
                <a href="{{ route('products.index') }}" class="flex items-center space-x-3 p-2.5 rounded-xl hover:bg-emerald-50 hover:text-emerald-800 transition-colors">
                    <i class="fa-solid fa-boxes-stacked text-indigo-600 w-5 text-center"></i>
                    <span class="font-bold">Katalog Barang & Stok</span>
                </a>
                <a href="{{ route('purchases.create') }}" class="flex items-center space-x-3 p-2.5 rounded-xl hover:bg-emerald-50 hover:text-emerald-800 transition-colors">
                    <i class="fa-solid fa-truck-ramp-box text-amber-600 w-5 text-center"></i>
                    <span class="font-bold">Beli Stok Persediaan (Kulak)</span>
                </a>
                <a href="{{ route('reports.sales') }}" class="flex items-center space-x-3 p-2.5 rounded-xl hover:bg-emerald-50 hover:text-emerald-800 transition-colors">
                    <i class="fa-solid fa-file-invoice-dollar text-blue-600 w-5 text-center"></i>
                    <span class="font-bold">Laporan Penjualan (Harian / Bulanan / Tahunan)</span>
                </a>
                @if(auth()->user()->isOwner())
                <a href="{{ route('reports.profit_loss') }}" class="flex items-center space-x-3 p-2.5 rounded-xl hover:bg-emerald-50 hover:text-emerald-800 transition-colors">
                    <i class="fa-solid fa-scale-balanced text-teal-600 w-5 text-center"></i>
                    <span class="font-bold">Laporan Laba Rugi</span>
                </a>
                <a href="{{ route('reports.cash_flow') }}" class="flex items-center space-x-3 p-2.5 rounded-xl hover:bg-emerald-50 hover:text-emerald-800 transition-colors">
                    <i class="fa-solid fa-arrow-trend-up text-emerald-600 w-5 text-center"></i>
                    <span class="font-bold">Laporan Arus Kas</span>
                </a>
                <a href="{{ route('settings.index') }}" class="flex items-center space-x-3 p-2.5 rounded-xl hover:bg-emerald-50 hover:text-emerald-800 transition-colors">
                    <i class="fa-solid fa-sliders text-slate-600 w-5 text-center"></i>
                    <span class="font-bold">Pengaturan Toko Warung Aboen</span>
                </a>
                @endif
            </div>
        </div>
    </div>

    <!-- PWA Install Guide Modal (Untuk HP & Laptop) -->
    <div x-show="showInstallModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 text-slate-800 border border-slate-100 animate-scale-up" @click.away="showInstallModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div class="flex items-center space-x-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base text-slate-900">Pasang Aplikasi Warung Aboen</h3>
                        <p class="text-xs text-slate-500">Bisa dibuka langsung seperti aplikasi HP & Desktop</p>
                    </div>
                </div>
                <button @click="showInstallModal = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="space-y-3.5 text-xs">
                <!-- Android Guide -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                    <div class="flex items-center space-x-2 text-emerald-700 font-bold">
                        <i class="fa-brands fa-android text-base"></i>
                        <span class="text-xs">Di HP Android (Chrome / Browser Bawaan)</span>
                    </div>
                    <ol class="list-decimal list-inside space-y-1 text-slate-600 pl-1 leading-relaxed">
                        <li>Buka link aplikasi di browser Chrome HP Anda.</li>
                        <li>Ketuk menu titik tiga (<strong>⋮</strong>) di pojok kanan atas browser.</li>
                        <li>Pilih <strong>"Instal aplikasi"</strong> atau <strong>"Tambahkan ke Layar Utama" (Add to Home screen)</strong>.</li>
                        <li>Ikon <strong>Warung Aboen</strong> akan langsung muncul di layar HP layaknya aplikasi Play Store!</li>
                    </ol>
                </div>

                <!-- iPhone Guide -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                    <div class="flex items-center space-x-2 text-slate-800 font-bold">
                        <i class="fa-brands fa-apple text-base"></i>
                        <span class="text-xs">Di HP iPhone / iPad (Safari)</span>
                    </div>
                    <ol class="list-decimal list-inside space-y-1 text-slate-600 pl-1 leading-relaxed">
                        <li>Buka aplikasi menggunakan browser <strong>Safari</strong>.</li>
                        <li>Ketuk tombol <strong>Bagikan (Share / <i class="fa-solid fa-arrow-up-from-bracket"></i>)</strong> di bagian bawah layar.</li>
                        <li>Gulir ke bawah dan ketuk <strong>"Tambah ke Layar Utama" (Add to Home Screen)</strong>.</li>
                        <li>Aplikasi siap dibuka layar penuh tanpa bilah browser!</li>
                    </ol>
                </div>

                <!-- Laptop Guide -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                    <div class="flex items-center space-x-2 text-indigo-700 font-bold">
                        <i class="fa-solid fa-laptop text-base"></i>
                        <span class="text-xs">Di Laptop / Komputer (Google Chrome / Microsoft Edge)</span>
                    </div>
                    <ol class="list-decimal list-inside space-y-1 text-slate-600 pl-1 leading-relaxed">
                        <li>Ketuk ikon <strong>Instal (<i class="fa-solid fa-download"></i>)</strong> di sebelah kanan bilah alamat (URL bar) browser.</li>
                        <li>Atau ketuk menu (<strong>⋮</strong>) &rarr; Simpan dan Bagikan &rarr; <strong>"Instal Warung Aboen"</strong>.</li>
                        <li>Aplikasi akan berjalan di jendela tersendiri seperti software kasir desktop modern!</li>
                    </ol>
                </div>
            </div>

            <div class="mt-5 pt-3 border-t border-slate-100 flex justify-end">
                <button type="button" @click="showInstallModal = false" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-colors">
                    Saya Mengerti
                </button>
            </div>
        </div>
    </div>

    <!-- Live clock script -->
    <script>
        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }

        let deferredPrompt = null;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
        });

        function updateLiveClock() {
            const clockEl = document.getElementById('live-clock');
            if (clockEl) {
                const now = new Date();
                const options = { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
                clockEl.textContent = now.toLocaleDateString('id-ID', options).replace(/\./g, ':') + ' WIB';
            }
        }
        setInterval(updateLiveClock, 1000);

        function globalApp() {
            return {
                sidebarOpen: false,
                quickSearchOpen: false,
                searchKeyword: '',
                showInstallModal: false,
                triggerPwaInstall() {
                    if (deferredPrompt) {
                        deferredPrompt.prompt();
                        deferredPrompt.userChoice.then((choiceResult) => {
                            deferredPrompt = null;
                        });
                    } else {
                        this.showInstallModal = true;
                    }
                }
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
