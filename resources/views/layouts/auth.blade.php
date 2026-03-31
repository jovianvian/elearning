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
        @keyframes teraFadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes teraFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes teraFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        .tera-reveal-up { animation: teraFadeUp .65s cubic-bezier(.22,1,.36,1) both; }
        .tera-reveal-in { animation: teraFadeIn .7s ease both; }
        .tera-float { animation: teraFloat 6s ease-in-out infinite; }
    </style>
</head>
<body class="min-h-screen bg-appbg text-ink">
<div class="min-h-screen grid lg:grid-cols-[1.08fr_.92fr]">
    <section class="hidden lg:flex relative overflow-hidden bg-gradient-to-br from-deep via-primary to-sky-600 text-white p-12 xl:p-14 tera-reveal-in">
        <div class="absolute -left-16 -top-14 h-64 w-64 rounded-full bg-yellowx/30 blur-3xl"></div>
        <div class="absolute right-0 top-1/3 h-44 w-44 rounded-full bg-white/15 blur-2xl"></div>
        <div class="absolute -right-20 bottom-4 h-72 w-72 rounded-full bg-redx/25 blur-3xl"></div>
        <div class="absolute inset-x-0 bottom-0 h-44 bg-gradient-to-t from-slate-950/25 to-transparent"></div>

        <div class="relative z-10 flex w-full flex-col justify-between">
            <div class="max-w-xl">
                <div class="inline-flex items-center gap-2.5 rounded-full border border-white/25 bg-white/10 px-4 py-2 text-sm backdrop-blur">
                    <span class="h-2.5 w-2.5 rounded-full bg-yellowx"></span>
                    Platform SMP Teramia
                </div>

                <h1 class="mt-8 text-4xl font-black leading-tight xl:text-5xl">Teramia E-Learning</h1>
                <p class="mt-4 max-w-lg text-base leading-relaxed text-white/90">
                    Satu platform untuk pembelajaran, ujian online, dan monitoring akademik harian yang rapi, cepat, dan nyaman dipakai.
                </p>

                <div class="mt-10 grid grid-cols-2 gap-4 text-sm">
                    <div class="rounded-2xl border border-white/20 bg-white/12 p-4 backdrop-blur-sm transition hover:bg-white/20 tera-float">Bilingual System<br><strong>ID / EN</strong></div>
                    <div class="rounded-2xl border border-white/20 bg-white/12 p-4 backdrop-blur-sm transition hover:bg-white/20">Role Based Access<br><strong>5 Main Roles</strong></div>
                    <div class="rounded-2xl border border-white/20 bg-white/12 p-4 backdrop-blur-sm transition hover:bg-white/20">Exam Engine<br><strong>Timer + Auto Submit</strong></div>
                    <div class="rounded-2xl border border-white/20 bg-white/12 p-4 backdrop-blur-sm transition hover:bg-white/20">Operational Reports<br><strong>School Ready</strong></div>
                </div>
            </div>

            <div class="mt-10 rounded-2xl border border-white/20 bg-white/10 px-5 py-4 backdrop-blur-sm">
                <p class="text-sm font-semibold text-white">Empowering SMP Teramia classrooms with structured digital learning.</p>
                <p class="mt-1 text-xs text-white/80">Secure access • Stable daily operations • Better academic visibility</p>
            </div>
        </div>
    </section>

    <section class="relative flex items-center justify-center p-5 md:p-8 lg:p-10">
        <div class="absolute inset-0 lg:hidden bg-gradient-to-b from-primary/8 via-white to-appbg"></div>
        <div class="relative z-10 w-full max-w-lg">
            @if(session('status'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">{{ $errors->first() }}</div>
            @endif

            <div class="rounded-[1.75rem] border border-slate-200/90 bg-white/95 p-7 shadow-[0_28px_70px_-30px_rgba(2,6,23,0.45)] sm:p-9 tera-reveal-up">
                <div class="mb-6">
                    <h2 class="text-2xl font-black tracking-tight text-slate-900">{{ $heading ?? 'Masuk' }}</h2>
                    @if(!empty($subheading))
                        <p class="mt-1.5 text-sm leading-relaxed text-slate-500">{{ $subheading }}</p>
                    @endif
                </div>
                @yield('content')
            </div>
        </div>
    </section>
</div>
</body>
</html>
