@extends('layouts.auth', [
    'title' => __('auth.reset_password'),
    'heading' => __('auth.reset_password'),
    'subheading' => 'Buat password baru untuk melanjutkan akses akun.'
])

@section('content')
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4" x-data="{ showA:false, showB:false }">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
            <input type="email" name="email" value="{{ $email }}" required class="w-full rounded-xl border-slate-300 px-3 py-2.5 text-sm focus:border-primary focus:ring-primary">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">{{ __('auth.new_password') }}</label>
            <div class="relative">
                <input :type="showA ? 'text' : 'password'" name="password" required class="w-full rounded-xl border-slate-300 px-3 py-2.5 pr-10 text-sm focus:border-primary focus:ring-primary">
                <button type="button" class="absolute inset-y-0 right-0 px-3 text-slate-500" @click="showA = !showA">👁</button>
            </div>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">{{ __('auth.confirm_password') }}</label>
            <div class="relative">
                <input :type="showB ? 'text' : 'password'" name="password_confirmation" required class="w-full rounded-xl border-slate-300 px-3 py-2.5 pr-10 text-sm focus:border-primary focus:ring-primary">
                <button type="button" class="absolute inset-y-0 right-0 px-3 text-slate-500" @click="showB = !showB">👁</button>
            </div>
        </div>
        <button class="w-full inline-flex justify-center rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition">
            {{ __('auth.reset_password') }}
        </button>
    </form>
@endsection
