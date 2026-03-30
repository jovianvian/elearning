<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Teramia E-Learning' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1D4ED8',
                        deep: '#1E3A8A',
                        yellowx: '#FACC15',
                        redx: '#DC2626',
                        skyx: '#38BDF8',
                        appbg: '#F8FAFC',
                        ink: '#0F172A'
                    },
                    boxShadow: {
                        soft: '0 10px 30px -15px rgba(2, 6, 23, 0.24)'
                    }
                }
            }
        };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body{font-family:"Plus Jakarta Sans",ui-sans-serif,system-ui,sans-serif}
        [x-cloak]{display:none!important}
    </style>
</head>
<body class="min-h-screen bg-appbg text-ink">
<div class="min-h-screen grid lg:grid-cols-[1.05fr_.95fr]">
    <section class="hidden lg:flex relative overflow-hidden bg-gradient-to-br from-deep via-primary to-sky-600 text-white p-12">
        <div class="absolute -left-16 -top-10 h-56 w-56 rounded-full bg-yellowx/30 blur-3xl"></div>
        <div class="absolute -right-12 bottom-5 h-64 w-64 rounded-full bg-redx/30 blur-3xl"></div>
        <div class="relative max-w-xl">
            <div class="inline-flex items-center gap-3 px-4 py-2 bg-white/15 rounded-full text-sm">
                <span class="h-2.5 w-2.5 rounded-full bg-yellowx"></span>
                Platform SMP Teramia
            </div>
            <h1 class="mt-8 text-4xl font-black leading-tight">Teramia E-Learning</h1>
            <p class="mt-4 text-white/85 leading-relaxed">
                Platform pembelajaran dan ujian online yang modern, terstruktur, dan siap operasional untuk lingkungan sekolah.
            </p>
            <div class="mt-8 grid grid-cols-2 gap-4 text-sm">
                <div class="rounded-2xl bg-white/15 p-4">Bilingual UI<br><strong>ID / EN</strong></div>
                <div class="rounded-2xl bg-white/15 p-4">5 Role Access<br><strong>RBAC Ready</strong></div>
                <div class="rounded-2xl bg-white/15 p-4">Exam Engine<br><strong>Timer & Auto-submit</strong></div>
                <div class="rounded-2xl bg-white/15 p-4">Reports & Logs<br><strong>Monitoring</strong></div>
            </div>
        </div>
    </section>

    <section class="flex items-center justify-center p-6 md:p-10">
        <div class="w-full max-w-md">
            @if(session('status'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">{{ $errors->first() }}</div>
            @endif

            <div class="rounded-3xl bg-white border border-slate-200 shadow-soft p-7 sm:p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-black">{{ $heading ?? 'Masuk' }}</h2>
                    @if(!empty($subheading))
                        <p class="text-sm text-slate-500 mt-1">{{ $subheading }}</p>
                    @endif
                </div>
                @yield('content')
            </div>
        </div>
    </section>
</div>
</body>
</html>

