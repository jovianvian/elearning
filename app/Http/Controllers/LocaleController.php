<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    public function update(Request $request, string $locale): RedirectResponse|JsonResponse
    {
        abort_unless(in_array($locale, ['id', 'en'], true), 404);

        session(['locale' => $locale]);

        if (auth()->check() && auth()->user()->profile) {
            auth()->user()->profile()->update(['preferred_language' => $locale]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Locale updated.',
                'locale' => $locale,
            ]);
        }

        return back();
    }
}
