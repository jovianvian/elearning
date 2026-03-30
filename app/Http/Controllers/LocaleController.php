<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    public function update(string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, ['id', 'en'], true), 404);

        session(['locale' => $locale]);

        if (auth()->check() && auth()->user()->profile) {
            auth()->user()->profile()->update(['preferred_language' => $locale]);
        }

        return back();
    }
}
