@extends('layouts.auth', [
    'title' => __('auth.title'),
    'heading' => __('auth.login'),
    'subheading' => 'Masuk menggunakan NIS, NIP, username, atau email sesuai role.'
])

@section('content')
    <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4" x-data="{ showPassword: false }">
        @csrf
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">{{ __('auth.identifier') }}</label>
            <input type="text" name="login" value="{{ old('login') }}" required autofocus class="w-full rounded-xl border-slate-300 px-3 py-2.5 text-sm focus:border-primary focus:ring-primary">
            <p class="text-xs text-slate-500 mt-1">Siswa: NIS/Username/Email • Guru: NIP/Username/Email • Admin/Principal/Super Admin: Username/Email</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">{{ __('auth.password') }}</label>
            <div class="relative">
                <input :type="showPassword ? 'text' : 'password'" name="password" required class="w-full rounded-xl border-slate-300 px-3 py-2.5 pr-10 text-sm focus:border-primary focus:ring-primary">
                <button type="button" class="absolute inset-y-0 right-0 px-3 text-slate-500" @click="showPassword = !showPassword">
                    <span x-show="!showPassword">ğŸ‘</span>
                    <span x-show="showPassword" x-cloak>ğŸ™ˆ</span>
                </button>
            </div>
        </div>

        <label class="inline-flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" id="remember" class="rounded border-slate-300 text-primary focus:ring-primary">
            {{ __('auth.remember') }}
        </label>

        <button class="w-full inline-flex justify-center rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition">
            {{ __('auth.login') }}
        </button>
    </form>

    <div class="mt-5 flex items-center justify-between gap-3">
        <a href="{{ route('password.request') }}" class="text-sm font-semibold text-primary hover:underline">{{ __('auth.forgot_password') }}</a>
        <div class="inline-flex items-center rounded-xl border border-slate-200 bg-slate-50 p-1">
            <form method="POST" action="{{ route('locale.update', 'id') }}" class="inline">@csrf<button class="px-2.5 py-1 text-xs rounded-lg {{ app()->getLocale() === 'id' ? 'bg-primary text-white' : 'text-slate-600' }}">ID</button></form>
            <form method="POST" action="{{ route('locale.update', 'en') }}" class="inline">@csrf<button class="px-2.5 py-1 text-xs rounded-lg {{ app()->getLocale() === 'en' ? 'bg-primary text-white' : 'text-slate-600' }}">EN</button></form>
        </div>
    </div>
@endsection
