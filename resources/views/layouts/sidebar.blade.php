<!-- Left Sidebar (Desktop - Bright Emerald Gradient, Fixed/Sticky) -->
<aside class="hidden lg:flex lg:flex-col lg:w-64 bg-gradient-to-b from-emerald-600 via-emerald-700 to-teal-800 text-white shrink-0 sticky top-0 h-screen border-r border-emerald-500/30 shadow-xl z-30">
    <!-- Brand / Logo -->
    <div class="h-20 shrink-0 flex items-center px-6 border-b border-white/15 bg-white/10 backdrop-blur-xs">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
            @php
                $appLogo = \App\Models\Parameter::get('app_logo');
                $appName = \App\Models\Parameter::get('app_name', config('app.name', 'Pijat.In'));
            @endphp
            @if ($appLogo)
                <img src="{{ asset('storage/' . $appLogo) }}" alt="{{ $appName }}" class="w-10 h-10 object-contain rounded-xl bg-white p-1 shadow-md shrink-0">
            @else
                <div class="w-10 h-10 rounded-xl bg-white text-emerald-700 flex items-center justify-center shadow-md shrink-0">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                    </svg>
                </div>
            @endif
            <div class="overflow-hidden">
                <span class="text-xl font-black tracking-tight text-white block leading-none truncate">{{ $appName }}</span>
            </div>
        </a>
    </div>

    <!-- Navigation Menu -->
    <nav id="sidebar-nav" class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto no-scrollbar [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
        @if (Auth::user()->isSuperadmin())
            <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-emerald-100/80">
                Menu Super Admin
            </div>
            
            <a href="{{ route('superadmin.regions.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.regions.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('superadmin.regions.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                Master Wilayah
            </a>

            <a href="{{ route('superadmin.admins.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.admins.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('superadmin.admins.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Penugasan Admin
            </a>

            <a href="{{ route('superadmin.complaint-categories.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.complaint-categories.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('superadmin.complaint-categories.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                Kategori Aduan
            </a>

            <a href="{{ route('admin.orders.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.orders.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.orders.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                Pesanan Wilayah
            </a>

            <a href="{{ route('admin.catalog.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.catalog.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.catalog.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Katalog Layanan
            </a>

            <a href="{{ route('superadmin.finance.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.finance.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('superadmin.finance.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Keuangan Nasional
            </a>

            <div class="px-3 pt-4 pb-2 text-[10px] font-bold uppercase tracking-wider text-emerald-100/80">
                Tata Kelola & Sistem
            </div>

            <a href="{{ route('admin.notifications.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.notifications.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.notifications.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                Notifikasi & Antrean
            </a>

            <a href="{{ route('superadmin.parameters.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.parameters.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('superadmin.parameters.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Parameter Sistem
            </a>

            <a href="{{ route('superadmin.branding.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.branding.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('superadmin.branding.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                </svg>
                Identitas Aplikasi
            </a>

            <a href="{{ route('superadmin.banners.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.banners.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('superadmin.banners.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Banner Promosi
            </a>

            <a href="{{ route('superadmin.banks.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.banks.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('superadmin.banks.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                Master Rekening Bank
            </a>

            <a href="{{ route('superadmin.audit-logs.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.audit-logs.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('superadmin.audit-logs.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Log Audit Terpusat
            </a>

            <a href="{{ route('superadmin.legal-documents.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.legal-documents.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('superadmin.legal-documents.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                </svg>
                Dokumen Legal & SOP
            </a>

            <a href="{{ route('superadmin.faqs.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('superadmin.faqs.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('superadmin.faqs.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Manajemen FAQ
            </a>

        @elseif (Auth::user()->isAdmin())
            <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-emerald-100/80">
                Menu Operasional
            </div>

            <a href="{{ route('admin.dashboard') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.dashboard') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                Dashboard Wilayah
            </a>

            <a href="{{ route('admin.notifications.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.notifications.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.notifications.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                Notifikasi & Antrean
            </a>

            <a href="{{ route('admin.orders.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.orders.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.orders.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                Pesanan Wilayah
            </a>

            <a href="{{ route('admin.complaints.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.complaints.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.complaints.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                Laporan Aduan
            </a>

            <a href="{{ route('admin.support-chats.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.support-chats.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.support-chats.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                </svg>
                Live Chat Bantuan
            </a>

            <a href="{{ route('admin.penalties.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.penalties.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.penalties.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Poin & Sanksi
            </a>

            <a href="{{ route('admin.appeals.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.appeals.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.appeals.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Banding Sanksi
            </a>

            <a href="{{ route('admin.mitra.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.mitra.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.mitra.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Verifikasi Mitra
            </a>

            <a href="{{ route('admin.users.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.users.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.users.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Manajemen Pengguna
            </a>

            <a href="{{ route('admin.finance.index') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.finance.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.finance.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Keuangan Wilayah
            </a>

            @if (Auth::user()->canAccessMenu('KATALOG'))
                <a href="{{ route('admin.catalog.index') }}" 
                   class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.catalog.*') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.catalog.*') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Katalog Layanan
                </a>
            @endif
        @else
            <a href="{{ route('dashboard') }}" 
               class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-white text-emerald-900 shadow-md font-bold' : 'text-white/90 hover:bg-white/15 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard') ? 'text-emerald-700' : 'text-emerald-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>
        @endif
    </nav>

    <!-- Bottom User Profile Card (Fixed at Bottom) -->
    <div class="shrink-0 p-4 border-t border-white/15 bg-black/15 backdrop-blur-xs">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3 overflow-hidden">
                <div class="w-9 h-9 rounded-full bg-white text-emerald-800 font-bold flex items-center justify-center shrink-0 text-sm shadow">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="overflow-hidden">
                    <p class="text-sm font-semibold text-white truncate leading-tight">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-emerald-100/80 truncate mt-0.5">{{ Auth::user()->email }}</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-1">
                @if (Auth::user()->isAdmin() || Auth::user()->isSuperadmin())
                    <a href="{{ route('admin.profile.edit') }}" title="Profil & Kata Sandi" class="p-2 text-emerald-200 hover:text-white hover:bg-white/15 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Keluar / Logout" class="p-2 text-emerald-200 hover:text-white hover:bg-white/15 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Preservasi Posisi Scroll Sidebar Backoffice (Aman & Pasif) -->
    <script>
        (function () {
            const nav = document.getElementById('sidebar-nav');
            if (!nav) return;

            const saved = sessionStorage.getItem('pijatin_sidebar_scroll');
            if (saved !== null) {
                nav.scrollTop = parseInt(saved, 10);
            } else {
                const activeItem = nav.querySelector('a.bg-white');
                if (activeItem) {
                    activeItem.scrollIntoView({ block: 'nearest' });
                }
            }

            nav.addEventListener('scroll', function () {
                try {
                    sessionStorage.setItem('pijatin_sidebar_scroll', nav.scrollTop.toString());
                } catch (e) {}
            }, { passive: true });
        })();
    </script>
</aside>
