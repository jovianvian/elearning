@extends('layouts.auth', [
    'title' => __('auth.forgot_password'),
    'heading' => __('auth.forgot_password'),
    'subheading' => 'Masukkan email akun untuk menerima link reset password.'
])

@section('content')
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
            <input type="email" name="email" required class="w-full rounded-xl border-slate-300 px-3 py-2.5 text-sm focus:border-primary focus:ring-primary">
        </div>
        <div class="flex flex-wrap gap-2">
            <button class="inline-flex justify-center rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition">
                {{ __('auth.send_reset_link') }}
            </button>
            <a href="{{ route('login') }}" class="inline-flex justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                {{ __('auth.login') }}
            </a>
        </div>
    </form>
@endsection
