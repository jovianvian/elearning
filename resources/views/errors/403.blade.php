<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses Ditolak</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
    <main class="mx-auto flex min-h-screen max-w-3xl items-center justify-center px-6 py-10">
        <section class="w-full rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
            <p class="text-sm font-semibold text-blue-600">Error 403</p>
            <h1 class="mt-2 text-2xl font-bold">Akses ditolak</h1>
            <p class="mt-3 text-sm text-slate-600">
                Anda tidak memiliki izin untuk membuka halaman ini.
            </p>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Kembali ke Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Masuk</a>
                @endauth
            </div>
        </section>
    </main>
</body>
</html>
