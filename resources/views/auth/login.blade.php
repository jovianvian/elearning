@extends('layouts.auth', [
    'title' => __('auth.title'),
    'heading' => __('auth.login'),
    'subheading' => __('auth.login_subheading')
])

@section('content')
    <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5" x-data="{ showPassword: false, isSubmitting: false }" @submit="isSubmitting = true">
        @csrf

        <div class="space-y-2">
            <label class="block text-sm font-semibold text-slate-700">{{ __('auth.identifier') }}</label>
            <input
                type="text"
                name="login"
                value="{{ old('login') }}"
                required
                autofocus
                class="w-full rounded-xl border border-slate-300 bg-slate-50/70 px-3.5 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-primary focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-100"
                placeholder="NIS / NIP / Username / Email"
            >
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-semibold text-slate-700">{{ __('auth.password') }}</label>
            <div class="relative">
                <input
                    :type="showPassword ? 'text' : 'password'"
                    name="password"
                    required
                    class="w-full rounded-xl border border-slate-300 bg-slate-50/70 px-3.5 py-3 pr-12 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-primary focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-100"
                    placeholder="••••••••"
                >
                <button type="button" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-500 hover:text-primary" @click="showPassword = !showPassword">
                    <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <svg x-show="showPassword" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.58 10.58A3 3 0 0 0 12 15a3 3 0 0 0 2.42-4.42M9.88 5.09A10.02 10.02 0 0 1 12 5c4.48 0 8.27 2.94 9.54 7a10.96 10.96 0 0 1-4.11 5.62M6.61 6.61A10.97 10.97 0 0 0 2.46 12c1.27 4.06 5.06 7 9.54 7 1.61 0 3.14-.38 4.5-1.06" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('auth.identifier_guide_title') }}</p>
            <div class="mt-2 space-y-1.5 text-sm text-slate-700">
                <div><span class="font-semibold text-slate-900">{{ __('auth.identifier_student') }}</span></div>
                <div><span class="font-semibold text-slate-900">{{ __('auth.identifier_teacher') }}</span></div>
                <div><span class="font-semibold text-slate-900">{{ __('auth.identifier_admin') }}</span></div>
            </div>
        </div>

        <label class="inline-flex items-center gap-2.5 text-sm text-slate-600">
            <input type="checkbox" name="remember" id="remember" class="rounded border-slate-300 text-primary focus:ring-2 focus:ring-primary/40">
            {{ __('auth.remember') }}
        </label>

        <button
            :disabled="isSubmitting"
            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary to-deep px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-200 transition hover:from-blue-700 hover:to-deep focus:outline-none focus:ring-4 focus:ring-blue-200 disabled:cursor-not-allowed disabled:opacity-80"
        >
            <svg x-show="isSubmitting" x-cloak class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
            </svg>
            <span x-show="!isSubmitting">{{ __('auth.login') }}</span>
            <span x-show="isSubmitting" x-cloak>{{ __('auth.signing_in') }}</span>
        </button>
    </form>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('password.request') }}" class="text-sm font-semibold text-primary hover:underline">{{ __('auth.forgot_password') }}</a>

        <div class="inline-flex items-center rounded-xl border border-slate-300 bg-white p-1.5 shadow-sm">
            <form method="POST" action="{{ route('locale.update', 'id') }}" class="inline">
                @csrf
                <button class="min-w-[42px] px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ app()->getLocale() === 'id' ? 'bg-primary text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100' }}">ID</button>
            </form>
            <form method="POST" action="{{ route('locale.update', 'en') }}" class="inline">
                @csrf
                <button class="min-w-[42px] px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ app()->getLocale() === 'en' ? 'bg-primary text-white shadow-sm' : 'text-slate-700 hover:bg-slate-100' }}">EN</button>
            </form>
        </div>
    </div>
@endsection
