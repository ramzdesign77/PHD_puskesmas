<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SIMKES Puskesmas Sumbersari - Sistem Informasi Manajemen Kesehatan Lingkungan">
    <title>@yield('title', 'Dashboard') — SIMKES Puskesmas Sumbersari</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com" rel="preconnect" />
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&amp;family=JetBrains+Mono:wght@400;500;600&amp;display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..24,400..600,0..1,-25..0" rel="stylesheet" />

<style>
  .material-symbols-outlined {
    font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 22;
  }
</style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { inter: ['Inter', 'sans-serif'] },
                    colors: {
                        'medical-red':   '#E63946',
                        'medical-dark':  '#C1121F',
                        'medical-light': '#FFE8EA',
                    },
                    transitionProperty: {
                        'width': 'width',
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .sidebar-link {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 16px; border-radius: 12px;
            color: #64748B; font-weight: 500; font-size: 14px;
            transition: all 0.2s ease; text-decoration: none;
        }
        .sidebar-link:hover { background: #FFF0F1; color: #E63946; }
        .sidebar-link.active { background: linear-gradient(135deg, #E63946 0%, #C1121F 100%); color: white; box-shadow: 0 4px 15px rgba(230,57,70,0.35); }
        .sidebar-link.active .sidebar-icon { color: white; }
        .sidebar-icon { width: 20px; text-align: center; font-size: 16px; }

        .navbar-glass {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(229,231,235,0.8);
        }
        .medical-gradient { background: linear-gradient(135deg, #E63946 0%, #C1121F 100%); }
        
        /* Buttons */
        .btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            background: linear-gradient(135deg, #E63946 0%, #C1121F 100%);
            color: white; border: none; cursor: pointer;
            padding: 8px 16px; border-radius: 12px; font-weight: 600; font-size: 14px;
            transition: all 0.2s ease; text-decoration: none; box-shadow: 0 2px 4px rgba(230,57,70,0.2);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(230,57,70,0.35); }
        .btn-secondary {
            display: inline-flex; align-items: center; gap: 8px;
            background: #F8FAFC; color: #475569; border: 1px solid #E2E8F0;
            cursor: pointer; padding: 8px 16px; border-radius: 12px; font-weight: 600; font-size: 14px;
            transition: all 0.2s ease; text-decoration: none;
        }
        .btn-secondary:hover { background: #F1F5F9; color: #1E293B; }
        .btn-sm { padding: 6px 12px; font-size: 12px; border-radius: 8px; }

        /* Cards */
        .card { 
            background: white; 
            border-radius: 0.75rem; /* rounded-xl */
            border: 1px solid #F3F4F6; /* gray-100 */
            box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); /* shadow-sm */
        }

        /* Data Tables */
        .data-table { width: 100%; text-align: left; border-collapse: collapse; }
        .data-table th { 
            padding: 16px 24px; font-size: 12px; font-weight: 600; 
            color: #64748B; text-transform: uppercase; letter-spacing: 0.05em; 
            border-bottom: 1px solid #F1F5F9; background: #F8FAFC;
            text-align: left;
        }
        .data-table td { 
            padding: 16px 24px; font-size: 14px; color: #334155; 
            border-bottom: 1px solid #F1F5F9; vertical-align: middle;
        }
        .data-table tbody tr { transition: background-color 0.2s ease; }
        .data-table tbody tr:hover { background-color: #F8FAFC; }

        /* Badges */
        .badge {
            display: inline-flex; items-center; gap: 4px;
            padding: 4px 12px; font-size: 12px; font-weight: 600;
            border-radius: 9999px; /* rounded-full */
            border: 1px solid transparent;
        }
        .badge-pending { background: #FEF3C7; color: #B45309; border-color: #FDE68A; } /* yellow */
        .badge-assigned, .badge-progress { background: #EFF6FF; color: #1D4ED8; border-color: #BFDBFE; } /* blue */
        .badge-resolved { background: #ECFCCB; color: #4D7C0F; border-color: #D9F99D; } /* green */
        .badge-rejected { background: #FEE2E2; color: #B91C1C; border-color: #FECACA; } /* red */

        .fade-in { animation: fadeIn 0.4s ease; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
        .sidebar-enter { transition: transform 0.3s ease, opacity 0.3s ease; }
        .notification-dot {
            width: 8px; height: 8px; background: #E63946;
            border-radius: 50%; position: absolute; top: -2px; right: -2px;
            border: 2px solid white;
        }
        .role-badge-citizen  { background: #EFF6FF; color: #1D4ED8; }
        .role-badge-officer  { background: #F0FDF4; color: #16A34A; }
        .role-badge-admin    { background: #FFF7ED; color: #C2410C; }
    </style>
    @yield('styles')
</head>
<body class="bg-gray-50 h-full" x-data="{ sidebarOpen: true, mobileMenuOpen: false }">

<div class="flex h-screen overflow-hidden">

    {{-- ═══════════════════════════════════════════ --}}
    {{--  SIDEBAR                                    --}}
    {{-- ═══════════════════════════════════════════ --}}
    {{-- Desktop Sidebar --}}
    <aside class="hidden md:flex flex-col bg-white shadow-lg transition-all duration-300 z-30"
           :class="sidebarOpen ? 'w-64' : 'w-20'">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-5 py-5 border-b border-gray-100">
            <div class="medical-gradient w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-md">
                <i class="fas fa-clinic-medical text-white text-lg"></i>
            </div>
            <div x-show="sidebarOpen" x-transition.opacity class="overflow-hidden">
                <p class="font-bold text-gray-800 text-sm leading-tight">SIMKES</p>
                <p class="text-gray-400 text-xs">Puskesmas Sumbersari</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <div x-show="sidebarOpen" x-transition.opacity class="px-3 pb-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Menu Utama</p>
            </div>

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}" id="nav-dashboard"
               class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="sidebar-icon"><i class="fas fa-th-large"></i></span>
                <span x-show="sidebarOpen" x-transition.opacity>Dashboard</span>
            </a>

            {{-- Laporan --}}
            <a href="{{ route('reports.index') }}" id="nav-reports"
               class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <span class="sidebar-icon"><i class="fas fa-file-alt"></i></span>
                <span x-show="sidebarOpen" x-transition.opacity>
                    @if(session('role') === 'citizen')   Kirim Laporan
                    @elseif(session('role') === 'officer') Data Laporan
                    @else Rekap Laporan
                    @endif
                </span>
            </a>

            {{-- Edukasi untuk masyarakat dan petugas --}}
            @if(session('role') !== 'admin')
            <a href="{{ route('education.index') }}" id="nav-education"
               class="sidebar-link {{ request()->routeIs('education.index') ? 'active' : '' }}">
                <span class="sidebar-icon"><i class="fas fa-book-open"></i></span>
                <span x-show="sidebarOpen" x-transition.opacity>Edukasi Kesehatan</span>
            </a>
            @endif

            {{-- Jadwal --}}
            <a href="{{ route('schedules.index') }}" id="nav-schedules"
               class="sidebar-link {{ request()->routeIs('schedules.*') ? 'active' : '' }}">
                <span class="sidebar-icon"><i class="fas fa-calendar-alt"></i></span>
                <span x-show="sidebarOpen" x-transition.opacity>
                    @if(session('role') === 'citizen')   Jadwal Kunjungan
                    @elseif(session('role') === 'officer') Jadwal Saya
                    @else Kelola Jadwal
                    @endif
                </span>
            </a>

            {{-- Admin-only links --}}
            @if(session('role') === 'admin')
            <div x-show="sidebarOpen" x-transition.opacity class="px-3 pt-4 pb-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Manajemen</p>
            </div>
            <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <span class="sidebar-icon"><i class="fas fa-users-cog"></i></span>
                <span x-show="sidebarOpen" x-transition.opacity>Manajemen Petugas</span>
            </a>
            <a href="{{ route('education.manage') }}" class="sidebar-link {{ request()->routeIs('education.manage') ? 'active' : '' }}">
                <span class="sidebar-icon"><i class="fas fa-book-medical"></i></span>
                <span x-show="sidebarOpen" x-transition.opacity>Manajemen Edukasi</span>
            </a>
            <a href="{{ route('analytics.index') }}" class="sidebar-link {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
                <span class="sidebar-icon"><i class="fas fa-chart-bar"></i></span>
                <span x-show="sidebarOpen" x-transition.opacity>Laporan & Analitik</span>
            </a>
            @endif
        </nav>

        {{-- User Info Bottom --}}
        <div x-show="sidebarOpen" class="mt-auto border-t border-gray-100 p-4">
            <div class="flex w-full items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 shadow-sm">
                <div class="medical-gradient flex h-10 w-10 shrink-0 items-center justify-center rounded-xl shadow-md">
                    <span class="text-sm font-bold text-white">
                        {{ strtoupper(substr(session('user_name', 'U'), 0, 1)) }}
                    </span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-gray-800">{{ session('user_name', 'User') }}</p>
                    <p class="truncate text-xs text-gray-400">
                        @if(session('role') === 'citizen') Masyarakat
                        @elseif(session('role') === 'officer') Petugas Kesling
                        @else Kepala Puskesmas
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Collapse Toggle --}}
        <button @click="sidebarOpen = !sidebarOpen" id="sidebar-toggle"
                class="p-3 border-t border-gray-100 text-gray-400 hover:text-medical-red hover:bg-gray-50 transition-colors">
            <i class="fas" :class="sidebarOpen ? 'fa-chevron-left' : 'fa-chevron-right'"></i>
        </button>
    </aside>

    {{-- Mobile Overlay --}}
    <div x-show="mobileMenuOpen" x-transition.opacity
         @click="mobileMenuOpen = false"
         class="fixed inset-0 bg-black/50 z-40 md:hidden"></div>

    {{-- Mobile Sidebar --}}
    <aside x-show="mobileMenuOpen"
           x-transition:enter="transition transform duration-300"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition transform duration-300"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full"
           class="fixed left-0 top-0 bottom-0 w-72 bg-white shadow-2xl z-50 md:hidden flex flex-col">

        <div class="flex items-center gap-3 px-5 py-5 border-b border-gray-100">
            <div class="medical-gradient w-10 h-10 rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-clinic-medical text-white text-lg"></i>
            </div>
            <div>
                <p class="font-bold text-gray-800 text-sm">SIMKES</p>
                <p class="text-gray-400 text-xs">Puskesmas Sumbersari</p>
            </div>
            <button @click="mobileMenuOpen = false" class="ml-auto text-gray-400 hover:text-red-500">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="sidebar-icon"><i class="fas fa-th-large"></i></span> Dashboard
            </a>
            <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <span class="sidebar-icon"><i class="fas fa-file-alt"></i></span>
                @if(session('role') === 'citizen') Kirim Laporan @elseif(session('role') === 'officer') Data Laporan @else Rekap Laporan @endif
            </a>
            @if(session('role') !== 'admin')
            <a href="{{ route('education.index') }}" class="sidebar-link {{ request()->routeIs('education.*') ? 'active' : '' }}">
                <span class="sidebar-icon"><i class="fas fa-book-open"></i></span> Edukasi Kesehatan
            </a>
            @endif
            @if(session('role') === 'admin')
            <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <span class="sidebar-icon"><i class="fas fa-users-cog"></i></span> Manajemen Petugas
            </a>
            <a href="{{ route('education.manage') }}" class="sidebar-link {{ request()->routeIs('education.manage') ? 'active' : '' }}">
                <span class="sidebar-icon"><i class="fas fa-book-medical"></i></span> Manajemen Edukasi
            </a>
            <a href="{{ route('analytics.index') }}" class="sidebar-link {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
                <span class="sidebar-icon"><i class="fas fa-chart-bar"></i></span> Laporan & Analitik
            </a>
            @endif
            <a href="{{ route('schedules.index') }}" class="sidebar-link {{ request()->routeIs('schedules.*') ? 'active' : '' }}">
                <span class="sidebar-icon"><i class="fas fa-calendar-alt"></i></span>
                @if(session('role') === 'citizen') Jadwal Kunjungan @elseif(session('role') === 'officer') Jadwal Saya @else Kelola Jadwal @endif
            </a>
        </nav>
    </aside>

    {{-- ═══════════════════════════════════════════ --}}
    {{--  MAIN CONTENT AREA                          --}}
    {{-- ═══════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- TOP NAVBAR --}}
        <header class="navbar-glass sticky top-0 z-20 flex items-center justify-between px-4 md:px-6 py-3">
            {{-- Left: Hamburger + Page Title --}}
            <div class="flex items-center gap-3">
                <button @click="mobileMenuOpen = !mobileMenuOpen" id="mobile-menu-btn"
                        class="md:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-bars text-lg"></i>
                </button>
                <div>
                    <h2 class="font-bold text-gray-800 text-base md:text-lg leading-tight">@yield('page-title', 'Dashboard')</h2>
                    <p class="text-xs text-gray-400 hidden md:block">@yield('page-subtitle', 'Selamat datang di SIMKES Puskesmas Sumbersari')</p>
                </div>
            </div>

            {{-- Right: Notifications + Role Badge + Logout --}}
            <div class="flex items-center gap-2 md:gap-4">
                {{-- Notification Bell --}}
                <button class="relative p-2 rounded-xl text-gray-500 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-bell text-lg"></i>
                    <span class="notification-dot"></span>
                </button>

                {{-- Role Badge --}}
                <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-xl
                    {{ session('role') === 'citizen' ? 'role-badge-citizen' : (session('role') === 'officer' ? 'role-badge-officer' : 'role-badge-admin') }}">
                    <i class="fas
                        {{ session('role') === 'citizen' ? 'fa-user' : (session('role') === 'officer' ? 'fa-user-md' : 'fa-user-shield') }}
                        text-xs"></i>
                    <span class="text-xs font-semibold">
                        @if(session('role') === 'citizen') Masyarakat
                        @elseif(session('role') === 'officer') Petugas Kesling
                        @else Kepala Puskesmas
                        @endif
                    </span>
                </div>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" id="btn-logout"
                            class="flex items-center gap-2 px-3 py-2 rounded-xl text-gray-500 hover:bg-red-50 hover:text-red-500 transition-colors text-sm font-medium">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="hidden md:inline">Logout</span>
                    </button>
                </form>
            </div>
        </header>

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="mx-4 md:mx-6 mt-4" x-data="{ show: true }" x-show="show" x-transition
             x-init="setTimeout(() => show = false, 5000)">
            <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl">
                <i class="fas fa-check-circle text-green-500"></i>
                <p class="text-sm font-medium flex-1">{{ session('success') }}</p>
                <button @click="show = false" class="text-green-400 hover:text-green-600"><i class="fas fa-times"></i></button>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mx-4 md:mx-6 mt-4" x-data="{ show: true }" x-show="show" x-transition>
            <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
                <i class="fas fa-exclamation-circle text-red-500"></i>
                <p class="text-sm font-medium flex-1">{{ session('error') }}</p>
                <button @click="show = false" class="text-red-400 hover:text-red-600"><i class="fas fa-times"></i></button>
            </div>
        </div>
        @endif

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto p-4 md:p-6 fade-in">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="border-t border-gray-100 bg-white px-6 py-3">
            <p class="text-xs text-gray-400 text-center">
                &copy; {{ date('Y') }} SIMKES Puskesmas Sumbersari — Dinas Kesehatan Kabupaten
            </p>
        </footer>
    </div>
</div>

@yield('scripts')
</body>
</html>
