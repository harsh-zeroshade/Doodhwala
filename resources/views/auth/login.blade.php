<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Log in') }} – {{ __('Doodhwala') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/doodhwala.css'])
</head>
<body>
    <div class="app-shell flex flex-col justify-center min-h-screen px-6 py-10">
        <div class="mb-8 text-center">
            <h1 class="text-2xl font-extrabold text-slate-900">{{ __('Doodhwala') }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('Login to continue') }}</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

<form method="POST" action="{{ route('login') }}" class="card p-5">
            @csrf

            <label class="lbl" for="phone_number">{{ __('Phone Number') }}</label>
            <input id="phone_number" class="input-field mb-4" type="tel" name="phone_number" value="{{ old('phone_number') }}" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('phone_number')" class="mb-3" />

            <label class="lbl" for="password">{{ __('Password') }}</label>
            <input id="password" class="input-field mb-4" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mb-3" />

            <label class="inline-flex items-center mb-5 text-sm text-slate-600">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-600 mr-2">
                {{ __('Remember me') }}
            </label>

            <button type="submit" class="btn-primary">{{ __('Log in') }}</button>
        </form>

        <p class="text-center mt-5 text-sm text-slate-500">
            <a href="{{ route('register') }}" class="text-emerald-600 font-semibold">{{ __('Register') }}</a>
            ·
            <a href="{{ route('home') }}" class="text-slate-500">{{ __('Doodhwala') }}</a>
        </p>
    </div>
</body>
</html>
