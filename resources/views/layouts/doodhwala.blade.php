<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Doodhwala') }} – {{ $pageTitle ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css">
    @vite(['resources/css/doodhwala.css', 'resources/js/app.js'])
    @stack('scripts-head')
</head>
<body>
    @php
        $portalLabel    = $portalLabel    ?? (auth()->user()->isAdmin() ? __('Milkman Portal') : __('Customer Portal'));
        $bottomNav      = $bottomNav      ?? (auth()->user()->isAdmin() ? 'partials.admin-nav' : 'partials.customer-nav');
        $avatarClass    = $avatarClass    ?? (auth()->user()->isCustomer()
            ? 'w-10 h-10 bg-emerald-50 border-2 border-emerald-100 rounded-[13px] flex items-center justify-center overflow-hidden'
            : 'w-10 h-10 bg-emerald-600 rounded-[13px] flex items-center justify-center shadow-[0_4px_8px_rgba(5,150,105,.2)] overflow-hidden');
        $avatarTextClass = $avatarTextClass ?? (auth()->user()->isCustomer() ? 'text-emerald-600' : 'text-white');
        $avatarUrl      = auth()->user()->avatarUrl();
    @endphp
    <div class="app-shell">
        <div class="sticky top-0 z-40" style="background:#fff;border-bottom:1px solid rgba(0,0,0,.07);">
            {{-- Colour accent bar (3-part gradient) --}}
            <div style="height:3px;background:linear-gradient(90deg,#2563EB 0%,#16A34A 50%,#D97706 100%);"></div>
            <div class="flex items-center gap-3 px-4 pt-3 pb-3">
                @isset($backUrl)
                    <a href="{{ $backUrl }}" class="w-9 h-9 rounded-[12px] flex items-center justify-center shrink-0"
                       style="background:#F1F5F9;border:1px solid #E2E8F0;">
                        <i class="ph ph-caret-left" style="font-size:18px;color:#64748B;"></i>
                    </a>
                @endisset
                <div class="flex-1 min-w-0">
                    <p class="m-0 font-semibold uppercase tracking-[.08em]" style="font-size:10px;color:#94A3B8;">{{ $portalLabel ?? '' }}</p>
                    <p class="m-0 tracking-tight truncate" style="font-size:20px;font-weight:800;color:#0F172A;letter-spacing:-.02em;">{{ $pageTitle ?? '' }}</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('locale.switch', app()->getLocale() === 'hi' ? 'en' : 'hi') }}"
                       class="font-bold px-2.5 py-1.5 rounded-[10px] select-none"
                       style="font-size:11px;border:1.5px solid #E2E8F0;background:#F8FAFC;color:#475569;">{{ app()->getLocale() === 'hi' ? __('EN') : __('हिंदी') }}
                    </a>
                    @if($avatarUrl)
                        <button type="button"
                                class="w-10 h-10 rounded-[13px] overflow-hidden border-0 p-0 cursor-pointer shrink-0"
                                data-drawer-open="dUser" data-drawer-overlay="ovUser">
                            <img src="{{ $avatarUrl }}" class="block w-10 h-10 object-cover" alt="">
                        </button>
                    @else
                        <button type="button"
                                class="{{ $avatarClass ?? 'w-10 h-10 bg-emerald-600 rounded-[13px] flex items-center justify-center shadow-[0_4px_8px_rgba(5,150,105,.2)]' }} border-0 p-0 cursor-pointer"
                                data-drawer-open="dUser" data-drawer-overlay="ovUser">
                            <span class="text-[13px] font-extrabold {{ $avatarTextClass ?? 'text-white' }}">{{ auth()->user()->initials() }}</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        @yield('content')

        @isset($bottomNav)
            @include($bottomNav)
        @endisset
    </div>

    @stack('drawers')

    {{-- User menu drawer (always present) --}}
    <div class="drawer-overlay" id="ovUser" data-drawer-close="dUser" data-drawer-overlay="ovUser"></div>
    <div class="drawer" id="dUser">
        <div class="drawer-handle"></div>
        <div class="flex items-center gap-3 mb-5 pb-5 border-b border-slate-100">
            @if($avatarUrl)
                <img src="{{ $avatarUrl }}" class="block w-12 h-12 rounded-[14px] object-cover shrink-0" alt="">
            @else
                <div class="w-12 h-12 {{ auth()->user()->isAdmin() ? 'bg-emerald-600 shadow-[0_4px_8px_rgba(5,150,105,.2)]' : 'bg-blue-500' }} rounded-[14px] flex items-center justify-center shrink-0">
                    <span class="text-sm font-extrabold text-white">{{ auth()->user()->initials() }}</span>
                </div>
            @endif
            <div class="min-w-0">
                <p class="m-0 text-[17px] font-extrabold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                <span class="badge {{ auth()->user()->isAdmin() ? 'badge-green' : 'badge-blue' }} mt-0.5 inline-flex">
                    {{ auth()->user()->isAdmin() ? __('Milkman') : __('Customer') }}
                </span>
            </div>
        </div>
        <div class="flex flex-col gap-2.5">
            <a href="{{ route('profile.edit') }}" class="btn-secondary no-underline">
                ✏️ {{ __('Edit Profile') }}
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full btn-primary" style="background:#ef4444;box-shadow:none;">
                    🚪 {{ __('Logout') }}
                </button>
            </form>
        </div>
    </div>

    <div id="toast">✓ {{ __('Done!') }}</div>
    @stack('scripts')
</body>
</html>
