<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Register') }} – {{ __('Doodhwala') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/doodhwala.css'])
</head>
<body>
    <div class="app-shell flex flex-col justify-center min-h-screen px-6 py-10">
        <form method="POST" action="{{ route('register') }}" class="card p-5">
            @csrf

            <h1 class="text-xl font-extrabold text-slate-900 mb-4">{{ __('Register') }}</h1>

            <label class="lbl" for="name">{{ __('Name') }}</label>
            <input id="name" class="input-field mb-3" name="name" value="{{ old('name') }}" required />
            <x-input-error :messages="$errors->get('name')" class="mb-3" />

            <label class="lbl" for="phone_number">{{ __('Phone Number') }}</label>
            <input id="phone_number" class="input-field mb-3" type="tel" name="phone_number" value="{{ old('phone_number') }}" required />
            <x-input-error :messages="$errors->get('phone_number')" class="mb-3" />

            <label class="lbl" for="password">{{ __('Password') }}</label>
            <input id="password" class="input-field mb-3" type="password" name="password" required />
            <x-input-error :messages="$errors->get('password')" class="mb-3" />

            <label class="lbl" for="password_confirmation">{{ __('Confirm Password') }}</label>
            <input id="password_confirmation" class="input-field mb-4" type="password" name="password_confirmation" required />

            <button type="submit" class="btn-primary mb-3">{{ __('Register') }}</button>
            <a href="{{ route('login') }}" class="block text-center text-sm text-emerald-600 font-semibold">{{ __('Already registered?') }}</a>
        </form>
    </div>
</body>
</html>
