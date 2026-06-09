<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, config('doodhwala.supported_locales', ['en', 'hi']), true)) {
            $locale = config('doodhwala.default_locale', 'en');
        }

        $request->session()->put('locale', $locale);

        return back();
    }
}
