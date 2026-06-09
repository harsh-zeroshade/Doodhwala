<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Doodhwala') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/doodhwala.css'])
</head>
<body>
    <div class="app-shell flex flex-col items-center justify-center min-h-screen px-7 py-10 gap-9">
        <div class="text-center">
            <div class="w-[88px] h-[88px] bg-emerald-600 rounded-[28px] flex items-center justify-center mx-auto mb-5 shadow-[0_12px_32px_rgba(5,150,105,.35)]">
                <svg width="46" height="46" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 3H4a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1z"/><path d="M4 8v13h16V8"/><circle cx="12" cy="14" r="2"/></svg>
            </div>
            <h1 class="text-[32px] font-extrabold text-slate-900 m-0 mb-2 tracking-tight">{{ __('Doodhwala') }}</h1>
            <p class="text-[15px] text-slate-500 m-0">{{ __('Smart Milk Delivery Manager') }}</p>
        </div>

        <div class="w-full flex flex-col gap-3.5">
            <p class="text-xs font-bold text-slate-400 text-center tracking-widest uppercase m-0">{{ __('Select your role') }}</p>
            <a href="{{ route('login') }}?role=admin" class="btn-primary h-[62px] text-[17px] rounded-[18px] no-underline">
                {{ __('Milkman (Admin)') }}
            </a>
            <a href="{{ route('login') }}?role=customer" class="btn-secondary h-[62px] text-[17px] rounded-[18px] no-underline">
                {{ __('Customer') }}
            </a>
        </div>

        <div class="absolute top-4 right-4">
            <a href="{{ route('locale.switch', app()->getLocale() === 'hi' ? 'en' : 'hi') }}"
               class="text-[11px] font-bold px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600">
                {{ app()->getLocale() === 'hi' ? __('EN') : __('हिंदी') }}
            </a>
        </div>

        <p class="text-xs text-slate-300 text-center m-0">{{ __('Login to continue') }}</p>
    </div>
</body>
</html>
