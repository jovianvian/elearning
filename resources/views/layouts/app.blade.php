<!doctype html>
<html lang="{{ app()->getLocale() }}" class="h-full m-0 p-0">
<head>
    @php
        $buildingBackgroundUrl = null;
        $customBuilding = $teraApp['building_background'] ?? null;
        if (!empty($customBuilding)) {
            $buildingBackgroundUrl = $customBuilding;
        } elseif (file_exists(public_path('images/branding/school-building.jpg'))) {
            $buildingBackgroundUrl = asset('images/branding/school-building.jpg');
        }
    @endphp
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
            --tera-building-bg: none;
        }
        @if($buildingBackgroundUrl)
        :root{ --tera-building-bg: url('{{ $buildingBackgroundUrl }}'); }
        @endif

        [x-cloak]{display:none!important}

        body.tera-app-bg{
            background-color:#f8fafc;
            background-image:
                linear-gradient(rgba(248, 250, 252, .80), rgba(248, 250, 252, .80)),
                var(--tera-building-bg);
            background-size:cover;
            background-position:center;
            background-repeat:no-repeat;
            background-attachment:fixed;
        }

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
            border:1px solid transparent;
            transition:all .2s ease;
        }

        .tera-btn-primary{background:var(--tera-primary);color:#fff;border-color:rgba(15,23,42,.15)}
        .tera-btn-primary:hover{filter:brightness(.94); transform: translateY(-1px)}
        .tera-btn-muted{background:#f8fafc;color:#1e293b;border-color:#cbd5e1}
        .tera-btn-muted:hover{background:#f1f5f9}
        .tera-btn-reset{background:#eef2ff;color:#1e3a8a;border-color:#c7d2fe}
        .tera-btn-reset:hover{background:#e0e7ff}
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
            overflow-x:auto;
            overflow-y:hidden;
            -webkit-overflow-scrolling:touch;
            box-shadow:0 10px 30px -15px rgba(2,6,23,.10);
        }

        .tera-table{
            width:100%;
            font-size:.875rem;
        }

        .mobile-table-scroll{
            overflow-x:auto;
            overflow-y:hidden;
            -webkit-overflow-scrolling:touch;
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
            text-align: center !important;
        }

        .tera-table td{
            padding:.85rem .9rem;
            border-top:1px solid rgb(241 245 249 / 1);
            vertical-align:middle;
            text-align: center !important;
        }

        .tera-table tbody tr:hover{
            background:#f8fafc;
        }

        .tera-badge{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:.22rem .55rem;
            border-radius:9999px;
            font-size:.72rem;
            font-weight:700;
            line-height:1.1;
        }
        .tera-status-badge{
            min-width:64px;
            margin-inline:auto;
        }

        .tera-toolbar-main{
            display:grid;
            gap:.75rem;
            align-items:end;
        }
        .tera-toolbar-fields{
            display:grid;
            gap:.75rem;
        }
        .tera-toolbar-filters{
            display:grid;
            gap:.75rem;
        }
        .tera-toolbar-actions{
            display:flex;
            align-items:center;
            justify-content:flex-end;
            gap:.5rem;
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
            padding-left: .875rem;
            padding-right: .875rem;
        }

        @media (min-width: 640px){
            .shell-gutter{
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
            .tera-toolbar-filters{
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px){
            .tera-toolbar-main{
                grid-template-columns: minmax(0, 1fr) auto;
            }
            .tera-toolbar-fields{
                grid-template-columns: minmax(250px, 1.1fr) minmax(0, 1.9fr);
            }
            .tera-toolbar-filters{
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (min-width: 1400px){
            .tera-toolbar-filters{
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        @media (max-width: 1023px){
            .tera-page{
                max-width: 100%;
            }

            .tera-card-body{
                padding: 1rem;
            }

            .tera-table th,
            .tera-table td{
                padding: .7rem .65rem;
                font-size: .8rem;
            }

            .tera-table{
                min-width: 760px;
            }

            .mobile-table-scroll > table{
                min-width: 760px;
            }
            .tera-toolbar-actions{
                justify-content:flex-end;
            }
        }

        .sidebar-link-reset:focus,
        .sidebar-link-reset:focus-visible{
            outline:none;
            box-shadow:none;
        }
    </style>
</head>
<body class="h-full m-0 p-0 tera-app-bg text-ink antialiased">
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

        <main class="flex-1 overflow-x-hidden shell-gutter pt-3 pb-5 sm:pt-5 sm:pb-8">
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
        window.Teramia = {
            csrf() {
                return document.querySelector('meta[name="csrf-token"]')?.content || '';
            },

            async fetchJson(url, options = {}) {
                const headers = {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...options.headers,
                };

                if (options.body && !(options.body instanceof FormData)) {
                    headers['Content-Type'] = headers['Content-Type'] || 'application/json';
                }

                if (!headers['X-CSRF-TOKEN']) {
                    headers['X-CSRF-TOKEN'] = this.csrf();
                }

                const response = await fetch(url, { ...options, headers });
                let payload = null;
                try {
                    payload = await response.json();
                } catch (e) {
                    payload = { ok: false, message: 'Invalid server response.' };
                }

                return { response, payload };
            },

            async toast(type, message, timer = 1400) {
                await Swal.fire({
                    icon: type,
                    title: type === 'success' ? @json(__('ui.success')) : @json(__('ui.error')),
                    text: message,
                    timer,
                    showConfirmButton: false,
                });
            },

            async confirmDelete(title, text) {
                return Swal.fire({
                    title: title || @json(__('ui.delete_data_question')),
                    text: text || @json(__('ui.delete_data_help')),
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#DC2626',
                    cancelButtonColor: '#334155',
                    confirmButtonText: @json(__('ui.yes_delete')),
                    cancelButtonText: @json(__('ui.cancel'))
                });
            },

            async refreshFragment(url, selector) {
                const htmlRes = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const html = await htmlRes.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const incoming = doc.querySelector(selector);
                const current = document.querySelector(selector);
                if (incoming && current) {
                    current.innerHTML = incoming.innerHTML;
                    this.initDynamicUi(current);
                    return true;
                }
                return false;
            },

            initDynamicUi(scope = document) {
                const root = scope instanceof Element || scope instanceof Document ? scope : document;

                if (window.Alpine?.initTree && root instanceof Element) {
                    window.Alpine.initTree(root);
                }

                if (window.lucide?.createIcons) {
                    window.lucide.createIcons();
                }

                root.querySelectorAll('input[type="datetime-local"], .js-flatpickr').forEach((el) => {
                    if (el.dataset.flatpickrBound === '1') return;
                    flatpickr(el, { enableTime: true, dateFormat: 'Y-m-d H:i' });
                    el.dataset.flatpickrBound = '1';
                });

                root.querySelectorAll('form').forEach((form) => {
                    if (form.dataset.deleteConfirmBound === '1') return;

                    const methodInput = form.querySelector('input[name="_method"]');
                    if (!(methodInput && methodInput.value.toUpperCase() === 'DELETE')) return;

                    form.dataset.deleteConfirmBound = '1';
                    form.addEventListener('submit', (e) => {
                        if (form.dataset.confirmed === '1') return;
                        e.preventDefault();

                        window.Teramia.confirmDelete().then((result) => {
                            if (result.isConfirmed) {
                                form.dataset.confirmed = '1';
                                form.submit();
                            }
                        });
                    });
                });

                root.querySelectorAll('form').forEach((form) => {
                    if (form.dataset.submitLockBound === '1') return;
                    if ((form.method || '').toUpperCase() === 'GET') return;
                    if (form.dataset.noSubmitLock === 'true') return;

                    form.dataset.submitLockBound = '1';
                    form.addEventListener('submit', () => {
                        if (form.dataset.submitting === '1') return;
                        form.dataset.submitting = '1';

                        const clickable = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                        clickable.forEach((node) => {
                            if (!(node instanceof HTMLElement)) return;
                            if (node.dataset.originalHtml === undefined) {
                                node.dataset.originalHtml = node.innerHTML ?? '';
                            }
                            node.setAttribute('disabled', 'disabled');
                            node.classList.add('opacity-70', 'cursor-not-allowed');

                            const loadingText = node.dataset.loadingText || @json(__('ui.processing'));
                            if (node.tagName.toLowerCase() === 'button') {
                                node.innerHTML = loadingText;
                            } else if (node instanceof HTMLInputElement) {
                                node.value = loadingText;
                            }
                        });
                    });
                });

                root.querySelectorAll('[data-chart]').forEach((canvas) => {
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

                if (window.matchMedia('(max-width: 1023px)').matches) {
                    root.querySelectorAll('main table, table').forEach((table) => {
                        const parent = table.parentElement;
                        if (!parent) return;
                        if (parent.classList.contains('tera-table-wrap') || parent.classList.contains('mobile-table-scroll')) return;

                        const wrapper = document.createElement('div');
                        wrapper.className = 'mobile-table-scroll rounded-xl border border-slate-200 bg-white';
                        parent.insertBefore(wrapper, table);
                        wrapper.appendChild(table);
                    });
                }
            },

            wireAsyncList(rootElementOrSelector, fragmentSelector, formSelector = 'form[method="GET"]') {
                const root = typeof rootElementOrSelector === 'string'
                    ? document.querySelector(rootElementOrSelector)
                    : rootElementOrSelector;
                if (!root || root.dataset.asyncListBound === '1') return;

                root.dataset.asyncListBound = '1';
                const form = root.querySelector(formSelector);

                const navigate = async (url) => {
                    const nextUrl = typeof url === 'string' ? url : String(url);
                    root.dispatchEvent(new CustomEvent('teramia:async-start', { bubbles: true }));
                    try {
                        window.history.replaceState({}, '', nextUrl);
                        await this.refreshFragment(nextUrl, fragmentSelector);
                    } finally {
                        root.dispatchEvent(new CustomEvent('teramia:async-end', { bubbles: true }));
                    }
                };

                if (form) {
                    form.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        const params = new URLSearchParams(new FormData(form));
                        const action = form.getAttribute('action') || window.location.pathname;
                        const url = `${action}?${params.toString()}`;
                        await navigate(url);
                    });
                }

                root.addEventListener('click', async (e) => {
                    const link = e.target.closest('a[href]');
                    if (!link) return;

                    const href = link.getAttribute('href') || '';
                    if (!href || href.startsWith('#')) return;
                    if (!link.closest(fragmentSelector)) return;

                    const abs = new URL(href, window.location.origin);
                    if (!abs.searchParams.has('page')) return;

                    e.preventDefault();
                    await navigate(abs.pathname + abs.search);
                });
            },

            setUnreadCount(count) {
                const node = document.getElementById('topbar-unread-count');
                if (!node) return;
                const value = Number(count ?? 0);
                if (Number.isNaN(value) || value <= 0) {
                    node.remove();
                    return;
                }
                node.textContent = String(value);
            }
        };

        window.teraSwitchLocale = async function (locale) {
            try {
                const url = `/locale/${locale}?_ts=${Date.now()}`;
                const { response } = await window.Teramia.fetchJson(url, { method: 'GET' });

                if (!response.ok) {
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

        window.Teramia.initDynamicUi(document);

        document.querySelectorAll('[data-async-list][data-fragment]').forEach((el) => {
            const fragmentSelector = el.dataset.fragment;
            const formSelector = el.dataset.formSelector || 'form[method="GET"]';
            window.Teramia.wireAsyncList(el, fragmentSelector, formSelector);
        });
    });
</script>
</body>
</html>
