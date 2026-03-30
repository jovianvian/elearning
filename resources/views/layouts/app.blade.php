<!doctype html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Teramia E-Learning' }}</title>
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
        [x-cloak]{display:none!important}
        .tera-card{border-radius: 1rem; border:1px solid rgb(226 232 240 / 1); background: #fff; box-shadow: 0 10px 30px -15px rgba(2,6,23,.14);}
        .tera-card-body{padding: 1.25rem;}
        .tera-btn{display:inline-flex;align-items:center;gap:.5rem;border-radius:.75rem;padding:.6rem 1rem;font-size:.875rem;font-weight:600;transition:.2s}
        .tera-btn-primary{background:#1D4ED8;color:#fff}
        .tera-btn-primary:hover{background:#1E40AF}
        .tera-btn-muted{background:#fff;color:#0F172A;border:1px solid rgb(203 213 225 / 1)}
        .tera-btn-danger{background:#DC2626;color:#fff}
        .tera-input,.tera-select,.tera-textarea{
            width:100%;border-radius:.75rem;border:1px solid rgb(203 213 225 / 1);background:#fff;
            padding:.625rem .75rem;font-size:.875rem;color:#0F172A;outline:none;transition:.2s
        }
        .tera-input:focus,.tera-select:focus,.tera-textarea:focus{
            border-color:#1D4ED8; box-shadow:0 0 0 3px rgb(29 78 216 / .15)
        }
        .tera-label{display:block;font-size:.8125rem;font-weight:600;color:#334155;margin-bottom:.35rem}
        .tera-h1{font-size:1.35rem;line-height:1.9rem;font-weight:800;color:#0F172A}
        .tera-sub{font-size:.825rem;color:#64748b}
        .tera-table-wrap{border-radius:1rem;border:1px solid rgb(226 232 240 / 1);background:#fff;overflow:hidden;box-shadow:0 10px 30px -15px rgba(2,6,23,.10)}
        .tera-table{width:100%;font-size:.875rem}
        .tera-table thead{background:#f8fafc;color:#475569}
        .tera-table th{font-weight:700;font-size:.75rem;letter-spacing:.02em;text-transform:uppercase;padding:.8rem .9rem}
        .tera-table td{padding:.85rem .9rem;border-top:1px solid rgb(241 245 249 / 1);vertical-align:top}
        .tera-table tbody tr:hover{background:#f8fafc}
        .tera-badge{display:inline-flex;align-items:center;padding:.22rem .55rem;border-radius:9999px;font-size:.72rem;font-weight:700}
        .tera-page{max-width:1280px;margin:0 auto}
    </style>
</head>
<body class="h-full bg-appbg text-ink antialiased">
<div x-data="{ sidebarOpen: false, sidebarMini: false }" class="min-h-screen">
    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 bg-slate-950/40 lg:hidden" @click="sidebarOpen = false"></div>

    @include('layouts.partials.sidebar')

    <div class="lg:pl-72 transition-all duration-300" :class="sidebarMini ? 'lg:pl-24' : 'lg:pl-72'">
        @include('layouts.partials.topbar')

        <main class="px-4 sm:px-6 py-6 sm:py-8">
            <div class="tera-page space-y-5">
                @include('layouts.partials.flash')
                @yield('content')
            </div>
        </main>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
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
                        title: 'Hapus data ini?',
                        text: 'Aksi ini dapat dipulihkan dari Restore Center untuk entitas tertentu.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#DC2626',
                        cancelButtonColor: '#334155',
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal'
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
                if (canvas.dataset.chartInitialized === '1') {
                    return;
                }

                const payload = JSON.parse(canvas.dataset.chart);
                const existing = Chart.getChart(canvas);
                if (existing) {
                    existing.destroy();
                }

                const chart = new Chart(canvas, payload);
                canvas._teraChart = chart;
                canvas.dataset.chartInitialized = '1';
            } catch (err) {}
        });
    });
</script>
</body>
</html>
