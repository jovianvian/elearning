<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale');

        if (! $locale && auth()->check()) {
            $locale = auth()->user()->profile?->preferred_language;
        }

        if (! $locale) {
            $locale = Schema::hasTable('app_settings')
                ? (AppSetting::query()->value('default_locale') ?? config('app.locale', 'id'))
                : config('app.locale', 'id');
        }

        if (! in_array($locale, ['id', 'en'], true)) {
            $locale = 'id';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
