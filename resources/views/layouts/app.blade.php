<!doctype html>
<html lang="{{ app()->getLocale() }}" class="h-full m-0 p-0">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? ($teraApp['app_name'] ?? 'Teramia E-Learning') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif']
                    },
                    colors: {
                        primary: '#1D4ED8',
                        deep: '#1E3A8A',
                        yellowx: '#FACC15',
                        redx: '#DC2626',
                        skyx: '#38BDF8',
                        successx: '#16A34A',
                        surface: '#FFFFFF',
                        appbg: '#F8FAFC',
                        ink: '#0F172A'
                    },
                    boxShadow: {
                        soft: '0 10px 30px -15px rgba(2, 6, 23, 0.18)'
                    }
                }
            }
        };
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        html, body{
            margin: 0 !important;
            padding: 0 !important;
        }

        :root{
            --tera-primary: {{ $teraApp['primary_color'] ?? '#1D4ED8' }};
            --tera-secondary: {{ $teraApp['secondary_color'] ?? '#1E3A8A' }};
            --tera-accent: {{ $teraApp['accent_color'] ?? '#FACC15' }};
            --shell-sidebar: 18rem;
            --shell-sidebar-mini: 6rem;
            --shell-header-height: 72px;
            --shell-border: rgb(226 232 240 / 1);
            --shell-divider: rgb(148 163 184 / .30);
            --shell-surface-border-on-sidebar: rgb(255 255 255 / .24);
            --tera-radius: .75rem;
            --tera-radius-card: 1rem;
        }

        [x-cloak]{display:none!important}

        .tera-card{
            border-radius: var(--tera-radius-card);
            border:1px solid rgb(226 232 240 / 1);
            background:#fff;
            box-shadow:0 10px 30px -15px rgba(2,6,23,.14);
        }

        .tera-card-body{padding:1.25rem}

        .tera-btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:.5rem;
            border-radius:var(--tera-radius);
            padding:.6rem 1rem;
            font-size:.875rem;
            font-weight:600;
            transition:all .2s ease;
        }

        .tera-btn-primary{background:var(--tera-primary);color:#fff}
        .tera-btn-primary:hover{filter:brightness(.94); transform: translateY(-1px)}
        .tera-btn-muted{background:#fff;color:#0F172A;border:1px solid rgb(203 213 225 / 1)}
        .tera-btn-muted:hover{background:#f8fafc}
        .tera-btn-danger{background:#DC2626;color:#fff}
        .tera-btn-danger:hover{filter:brightness(.94)}
        .tera-btn-outline{background:transparent;border:1px solid var(--tera-primary);color:var(--tera-primary)}
        .tera-btn-outline:hover{background:rgb(239 246 255)}
        .tera-btn:focus-visible{outline:none; box-shadow:0 0 0 3px rgb(29 78 216 / .18)}

        .tera-input,.tera-select,.tera-textarea{
            width:100%;
            border-radius:var(--tera-radius);
            border:1px solid rgb(203 213 225 / 1);
            background:#fff;
            padding:.625rem .75rem;
            font-size:.875rem;
            color:#0F172A;
            outline:none;
            transition:.2s;
        }

        .tera-input:focus,.tera-select:focus,.tera-textarea:focus{
            border-color:var(--tera-primary);
            box-shadow:0 0 0 3px rgb(29 78 216 / .15);
        }

        .tera-label{
            display:block;
            font-size:.8125rem;
            font-weight:600;
            color:#334155;
            margin-bottom:.35rem;
        }

        .tera-h1{
            font-size:1.35rem;
            line-height:1.9rem;
            font-weight:800;
            color:#0F172A;
        }

        .tera-sub{
            font-size:.825rem;
            color:#64748b;
        }

        .tera-table-wrap{
            border-radius:var(--tera-radius-card);
            border:1px solid rgb(226 232 240 / 1);
            background:#fff;
            overflow:hidden;
            box-shadow:0 10px 30px -15px rgba(2,6,23,.10);
        }

        .tera-table{
            width:100%;
            font-size:.875rem;
        }

        .tera-table thead{
            background:#f8fafc;
            color:#475569;
        }

        .tera-table th{
            font-weight:700;
            font-size:.75rem;
            letter-spacing:.02em;
            text-transform:uppercase;
            padding:.8rem .9rem;
        }

        .tera-table td{
            padding:.85rem .9rem;
            border-top:1px solid rgb(241 245 249 / 1);
            vertical-align:top;
        }

        .tera-table tbody tr:hover{
            background:#f8fafc;
        }

        .tera-badge{
            display:inline-flex;
            align-items:center;
            padding:.22rem .55rem;
            border-radius:9999px;
            font-size:.72rem;
            font-weight:700;
        }

        .tera-page{
            max-width:1280px;
            margin:0 auto;
        }

        .shell-header{
            height: var(--shell-header-height) !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            margin-top: 0 !important;
        }

        .shell-gutter{
            padding-left: 1rem;
            padding-right: 1rem;
        }

        @media (min-width: 640px){
            .shell-gutter{
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }

        .sidebar-link-reset:focus,
        .sidebar-link-reset:focus-visible{
            outline:none;
            box-shadow:none;
        }
    </style>
</head>
<body class="h-full m-0 p-0 bg-appbg text-ink antialiased">
<div x-data="{ sidebarOpen: false, sidebarMini: false }" class="min-h-screen">
    <div
        x-show="sidebarOpen"
        x-cloak
        class="fixed inset-0 z-40 bg-slate-950/40 lg:hidden"
        @click="sidebarOpen = false"
    ></div>

    @include('layouts.partials.sidebar')

    <div class="transition-all duration-300 min-h-screen m-0 pt-0 lg:pl-[var(--shell-sidebar)]" :class="sidebarMini ? 'lg:pl-[var(--shell-sidebar-mini)]' : 'lg:pl-[var(--shell-sidebar)]'">
        @include('layouts.partials.topbar')

        <main class="flex-1 overflow-x-hidden shell-gutter pt-4 pb-6 sm:pt-5 sm:pb-8">
            <div class="tera-page space-y-5">
                @include('layouts.partials.flash')
                @yield('content')
            </div>
        </main>

        @if(!empty($teraApp['footer_text']))
            <footer class="shell-gutter pb-6">
                <div class="tera-page text-xs text-slate-500">{{ $teraApp['footer_text'] }}</div>
            </footer>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.teraSwitchLocale = async function (locale) {
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.content;

                const res = await fetch(`/locale/${locale}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token,
                    },
                });

                if (!res.ok) {
                    throw new Error('Failed to switch language.');
                }

                window.location.reload();
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: @json(__('ui.language_switch_failed')),
                    text: @json(__('ui.please_try_again')),
                });
            }
        };

        lucide.createIcons();

        document.querySelectorAll('input[type="datetime-local"], .js-flatpickr').forEach((el) => {
            flatpickr(el, { enableTime: true, dateFormat: 'Y-m-d H:i' });
        });

        document.querySelectorAll('form').forEach((form) => {
            const methodInput = form.querySelector('input[name="_method"]');
            if (methodInput && methodInput.value.toUpperCase() === 'DELETE') {
                form.addEventListener('submit', (e) => {
                    if (form.dataset.confirmed === '1') return;
                    e.preventDefault();

                    Swal.fire({
                        title: @json(__('ui.delete_data_question')),
                        text: @json(__('ui.delete_data_help')),
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#DC2626',
                        cancelButtonColor: '#334155',
                        confirmButtonText: @json(__('ui.yes_delete')),
                        cancelButtonText: @json(__('ui.cancel'))
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.dataset.confirmed = '1';
                            form.submit();
                        }
                    });
                });
            }
        });

        document.querySelectorAll('[data-chart]').forEach((canvas) => {
            try {
                if (canvas.dataset.chartInitialized === '1') return;

                const payload = JSON.parse(canvas.dataset.chart);
                const existing = Chart.getChart(canvas);
                if (existing) existing.destroy();

                const chart = new Chart(canvas, payload);
                canvas._teraChart = chart;
                canvas.dataset.chartInitialized = '1';
            } catch (err) {}
        });
    });
</script>
</body>
</html>
