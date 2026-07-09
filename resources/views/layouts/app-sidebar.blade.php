<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — ' : '' }}{{ config('app.name', 'Scentiment') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">

    <!-- Scripts & CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- UI Enhancement Libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- jQuery + DataTables (backoffice) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" defer></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js" defer></script>

    <style>
        :root {
            --sidebar-w: 260px;
            --topbar-h: 56px;
            --color-gold: #C6A961;
            --color-charcoal: #2B2B2B;
            --color-ivory: #FAF7F0;
        }
        body { font-family: 'Inter', sans-serif; }
        .font-outfit { font-family: 'Outfit', sans-serif; }

        /* ─── Sidebar ─── */
        /* Handled via Tailwind utility classes dynamically */
        #app-sidebar .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 14px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #64748b;
            text-decoration: none;
            transition: background 0.15s, color 0.15s;
        }
        #app-sidebar .nav-item:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        #app-sidebar .nav-item.active {
            background: #0f172a;
            color: #fff;
        }
        #app-sidebar .nav-item.active svg { color: #fff; }
        #app-sidebar .nav-group-label {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #94a3b8;
            padding: 0 14px;
            margin-top: 20px;
            margin-bottom: 4px;
        }
        /* Admin sub-items indent */
        #app-sidebar .nav-sub-item {
            padding-left: 38px;
        }
    </style>

    {{ $head ?? '' }}
</head>
<body class="bg-slate-50 text-slate-800 antialiased" x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' }" x-init="$watch('sidebarCollapsed', val => localStorage.setItem('sidebarCollapsed', val))">

    <!-- Mobile Overlay -->
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 z-20 bg-black/40 lg:hidden"
         style="display:none">
    </div>

    <!-- Sidebar -->
    <aside id="app-sidebar"
           :class="[
               sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
               sidebarCollapsed ? 'lg:w-20' : 'lg:w-64'
           ]"
           class="-translate-x-full lg:translate-x-0 fixed top-0 left-0 bottom-0 z-30 bg-white border-r border-slate-200 flex flex-col overflow-y-auto transition-all duration-300 w-64">

        <!-- Logo / Brand -->
        <div class="flex items-center justify-center lg:justify-start gap-3 px-5 h-14 border-b border-slate-100 shrink-0">
            <div class="w-8 h-8 rounded-lg bg-slate-900 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
            </div>
            <span x-show="!sidebarCollapsed" class="font-outfit font-bold text-slate-900 text-base leading-tight">Scentiment</span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-x-hidden">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}"
               class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
               :class="sidebarCollapsed ? 'justify-center !px-0' : ''"
               title="Dashboard">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                <span x-show="!sidebarCollapsed">Dashboard</span>
            </a>

            <div class="nav-group-label truncate" x-show="!sidebarCollapsed">Form</div>

            <!-- Form Saya -->
            <a href="{{ route('forms.index') }}"
               class="nav-item {{ request()->routeIs('forms.index') && !request()->has('filter') ? 'active' : '' }}"
               :class="sidebarCollapsed ? 'justify-center !px-0 mt-2' : ''"
               title="Form Saya">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                <span x-show="!sidebarCollapsed">Form Saya</span>
            </a>

            <!-- Dibagikan ke Saya -->
            <a href="{{ route('forms.index', ['filter' => 'shared']) }}"
               class="nav-item {{ request()->routeIs('forms.index') && request()->query('filter') === 'shared' ? 'active' : '' }}"
               :class="sidebarCollapsed ? 'justify-center !px-0 mt-2' : ''"
               title="Dibagikan ke Saya">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                <span x-show="!sidebarCollapsed">Dibagikan ke Saya</span>
            </a>

            @can('manage_own_forms')
            <!-- Template -->
            <a href="{{ route('templates.index') }}"
               class="nav-item {{ request()->routeIs('templates.*') ? 'active' : '' }}"
               :class="sidebarCollapsed ? 'justify-center !px-0 mt-2' : ''"
               title="Template">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" /></svg>
                <span x-show="!sidebarCollapsed">Template</span>
            </a>
            @endcan

            <div class="nav-group-label truncate" x-show="!sidebarCollapsed">Data</div>

            <!-- Riwayat Export -->
            <a href="{{ route('exports.index') }}"
               class="nav-item {{ request()->routeIs('exports.*') ? 'active' : '' }}"
               :class="sidebarCollapsed ? 'justify-center !px-0 mt-2' : ''"
               title="Riwayat Export">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                <span x-show="!sidebarCollapsed">Riwayat Export</span>
            </a>

            @can('manage_all_forms')
            <div class="nav-group-label truncate" x-show="!sidebarCollapsed">Administrasi</div>

            <!-- Pengguna & Role -->
            <a href="{{ route('admin.users') }}"
               class="nav-item nav-sub-item {{ request()->routeIs('admin.users') ? 'active' : '' }}"
               :class="sidebarCollapsed ? 'justify-center !px-0 mt-2' : ''"
               title="Pengguna & Role">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                <span x-show="!sidebarCollapsed">Pengguna & Role</span>
            </a>

            <!-- Audit Log -->
            <a href="{{ route('admin.audit') }}"
               class="nav-item nav-sub-item {{ request()->routeIs('admin.audit') ? 'active' : '' }}"
               :class="sidebarCollapsed ? 'justify-center !px-0 mt-2' : ''"
               title="Audit Log">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                <span x-show="!sidebarCollapsed">Audit Log</span>
            </a>

            <!-- Konfigurasi -->
            <a href="{{ route('admin.settings') }}"
               class="nav-item nav-sub-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}"
               :class="sidebarCollapsed ? 'justify-center !px-0 mt-2' : ''"
               title="Konfigurasi">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <span x-show="!sidebarCollapsed">Konfigurasi</span>
            </a>

            <!-- Trash -->
            <a href="{{ route('admin.trash') }}"
               class="nav-item nav-sub-item {{ request()->routeIs('admin.trash') ? 'active' : '' }}"
               :class="sidebarCollapsed ? 'justify-center !px-0 mt-2' : ''"
               title="Trash">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                <span x-show="!sidebarCollapsed">Trash</span>
            </a>
            @endcan
        </nav>

        <!-- User Profile Footer -->
        <div class="border-t border-slate-100 px-3 py-3 shrink-0" x-data="{ open: false }">
            <button @click="open = !open"
                    class="w-full flex items-center gap-3 py-2 rounded-lg hover:bg-slate-50 transition-colors text-left"
                    :class="sidebarCollapsed ? 'justify-center px-0' : 'px-3'">
                <div class="w-8 h-8 rounded-full bg-slate-900 flex items-center justify-center shrink-0">
                    <span class="text-xs font-bold text-white">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                </div>
                <div class="flex-1 min-w-0" x-show="!sidebarCollapsed">
                    <div class="text-sm font-semibold text-slate-800 truncate">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-slate-400 truncate">{{ Auth::user()->email }}</div>
                </div>
                <svg x-show="!sidebarCollapsed" class="h-4 w-4 text-slate-400 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </button>

            <div x-show="open"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-cloak
                 class="mt-1 bg-white border border-slate-200 rounded-lg shadow-lg overflow-hidden">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    Profil Saya
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'" class="min-h-screen flex flex-col transition-all duration-300">

        <!-- Top Bar -->
        <header class="sticky top-0 z-10 h-14 bg-white border-b border-slate-200 flex items-center px-4 sm:px-6 gap-4 shrink-0">
            <!-- Mobile Hamburger -->
            <button @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden p-2 rounded-md text-slate-500 hover:bg-slate-100 transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>

            <!-- Desktop Sidebar Toggle -->
            <button @click="sidebarCollapsed = !sidebarCollapsed"
                    class="hidden lg:block p-2 rounded-md text-slate-500 hover:bg-slate-100 transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>

            <!-- Page Title (slot) -->
            <div class="flex-1 min-w-0">
                @isset($topbar)
                    {{ $topbar }}
                @else
                    <h1 class="font-outfit font-semibold text-base text-slate-900 truncate">
                        {{ $title ?? config('app.name') }}
                    </h1>
                @endisset
            </div>

            <!-- Right actions (slot) -->
            @isset($topbarActions)
                <div class="flex items-center gap-2 shrink-0">
                    {{ $topbarActions }}
                </div>
            @endisset
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="mt-auto border-t border-slate-200 bg-white py-4 px-4 sm:px-6 text-center lg:text-left flex flex-col lg:flex-row justify-between items-center gap-2 text-xs text-slate-500">
            <div>
                &copy; {{ date('Y') }} <strong>Heaven Scent</strong>. All rights reserved.
            </div>
            <div class="flex gap-4">
                <a href="#" class="hover:text-slate-800 transition">Kebijakan Privasi</a>
                <a href="#" class="hover:text-slate-800 transition">Syarat & Ketentuan</a>
                <a href="#" class="hover:text-slate-800 transition">Bantuan</a>
            </div>
        </footer>
    </div>

    <!-- Flash Messages via Notyf -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Notyf !== 'undefined') {
                window.notyf = new Notyf({ duration: 5000, position: { x: 'right', y: 'top' }, ripple: true });
                @if(session('success'))
                    window.notyf.success(@json(session('success')));
                @endif
                @if(session('error'))
                    window.notyf.error(@json(session('error')));
                @endif
            }

            // Global SweetAlert2 handler for delete forms
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const message = form.getAttribute('data-confirm') || 'Apakah Anda yakin ingin menghapus data ini?';
                    Swal.fire({
                        title: 'Konfirmasi',
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                        customClass: {
                            popup: 'font-inter',
                            title: 'font-outfit font-semibold'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>

    {{ $scripts ?? '' }}
</body>
</html>
