@php
    $role = auth()->user()->role->code ?? '';

    $isActive = function (array $patterns): bool {
        foreach ($patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }
        return false;
    };

    $makeLeaf = function (string $label, string $icon, string $route, array $patterns) use ($isActive) {
        if (!\Illuminate\Support\Facades\Route::has($route)) {
            return null;
        }

        return [
            'type' => 'leaf',
            'label' => $label,
            'icon' => $icon,
            'route' => $route,
            'patterns' => $patterns,
            'active' => $isActive($patterns),
        ];
    };

    $makeTree = function (string $label, string $icon, array $children) {
        $children = collect($children)->filter()->values()->all();
        if (empty($children)) {
            return null;
        }

        $active = collect($children)->contains(fn ($child) => (bool) ($child['active'] ?? false));

        return [
            'type' => 'tree',
            'label' => $label,
            'icon' => $icon,
            'children' => $children,
            'active' => $active,
        ];
    };

    $sections = [];

    if ($role === 'super_admin') {
        $sections = [
            [
                'key' => 'dashboard',
                'label' => 'Dashboard',
                'active' => false,
                'items' => [
                    $makeLeaf('Dashboard', 'layout-dashboard', 'dashboard.super-admin', ['dashboard.super-admin']),
                ],
            ],
            [
                'key' => 'academic',
                'label' => 'Academic',
                'active' => false,
                'items' => [
                    $makeLeaf('Users', 'users', 'users.index', ['users.*']),
                    $makeLeaf('Classes', 'school', 'classes.index', ['classes.*']),
                    $makeLeaf('Subjects', 'book-open', 'subjects.index', ['subjects.*']),
                    $makeTree('Assignments', 'git-branch-plus', [
                        $makeLeaf('Class Student Assignments', 'user-check', 'assignments.class-students.index', ['assignments.class-students.*']),
                        $makeLeaf('Subject Teacher Assignments', 'user-cog', 'assignments.subject-teachers.index', ['assignments.subject-teachers.*']),
                    ]),
                    $makeLeaf('Courses', 'folders', 'courses.index', ['courses.*', 'my-courses.*']),
                ],
            ],
            [
                'key' => 'assessment',
                'label' => 'Assessment',
                'active' => false,
                'items' => [
                    $makeLeaf('Question Banks', 'library-big', 'question-banks.index', ['question-banks.*']),
                    $makeLeaf('Question Imports', 'file-up', 'question-imports.index', ['question-imports.*']),
                    $makeLeaf('Exams', 'notepad-text', 'exams.index', ['exams.*', 'student-exams.*']),
                    $makeLeaf('Grading', 'check-check', 'exam-grading.index', ['exam-grading.*']),
                ],
            ],
            [
                'key' => 'monitoring',
                'label' => 'Monitoring & Reports',
                'active' => false,
                'items' => [
                    $makeLeaf('Reports', 'bar-chart-3', 'reports.index', ['reports.*']),
                    $makeLeaf('Suspicious Logs', 'shield-alert', 'suspicious-activities.index', ['suspicious-activities.*']),
                    $makeLeaf('Audit Logs', 'scroll-text', 'super-admin.audit-logs.index', ['super-admin.audit-logs.*']),
                    $makeLeaf('Login Logs', 'fingerprint', 'super-admin.login-logs.index', ['super-admin.login-logs.*']),
                ],
            ],
            [
                'key' => 'system',
                'label' => 'System',
                'active' => false,
                'items' => [
                    $makeLeaf('Restore Center', 'rotate-ccw', 'super-admin.restore-center.index', ['super-admin.restore-center.*']),
                    $makeLeaf('Academic Years', 'calendar-days', 'super-admin.academic-years.index', ['super-admin.academic-years.*']),
                    $makeLeaf('Semesters', 'calendar-range', 'super-admin.semesters.index', ['super-admin.semesters.*']),
                    $makeLeaf('Settings', 'settings-2', 'super-admin.settings.edit', ['super-admin.settings.*']),
                ],
            ],
        ];
    } elseif ($role === 'admin') {
        $sections = [
            [
                'key' => 'dashboard',
                'label' => 'Dashboard',
                'active' => false,
                'items' => [
                    $makeLeaf('Dashboard', 'layout-dashboard', 'dashboard.admin', ['dashboard.admin']),
                ],
            ],
            [
                'key' => 'academic',
                'label' => 'Academic',
                'active' => false,
                'items' => [
                    $makeLeaf('Users', 'users', 'users.index', ['users.*']),
                    $makeLeaf('Classes', 'school', 'classes.index', ['classes.*']),
                    $makeLeaf('Subjects', 'book-open', 'subjects.index', ['subjects.*']),
                    $makeTree('Assignments', 'git-branch-plus', [
                        $makeLeaf('Class Student Assignments', 'user-check', 'assignments.class-students.index', ['assignments.class-students.*']),
                        $makeLeaf('Subject Teacher Assignments', 'user-cog', 'assignments.subject-teachers.index', ['assignments.subject-teachers.*']),
                    ]),
                    $makeLeaf('Courses', 'folders', 'courses.index', ['courses.*']),
                ],
            ],
            [
                'key' => 'assessment',
                'label' => 'Assessment',
                'active' => false,
                'items' => [
                    $makeLeaf('Question Banks', 'library-big', 'question-banks.index', ['question-banks.*']),
                    $makeLeaf('Question Imports', 'file-up', 'question-imports.index', ['question-imports.*']),
                    $makeLeaf('Exams', 'notepad-text', 'exams.index', ['exams.*']),
                    $makeLeaf('Grading', 'check-check', 'exam-grading.index', ['exam-grading.*']),
                ],
            ],
            [
                'key' => 'monitoring',
                'label' => 'Monitoring & Reports',
                'active' => false,
                'items' => [
                    $makeLeaf('Reports', 'bar-chart-3', 'reports.index', ['reports.*']),
                ],
            ],
        ];
    } elseif ($role === 'principal') {
        $sections = [[
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'active' => false,
            'items' => [
                $makeLeaf('Dashboard', 'layout-dashboard', 'dashboard.principal', ['dashboard.principal']),
                $makeLeaf('Exams', 'notepad-text', 'exams.index', ['exams.*']),
                $makeLeaf('Reports', 'bar-chart-3', 'reports.index', ['reports.*']),
            ],
        ]];
    } elseif ($role === 'teacher') {
        $sections = [[
            'key' => 'teacher-menu',
            'label' => 'Teacher Menu',
            'active' => false,
            'items' => [
                $makeLeaf('Dashboard', 'layout-dashboard', 'dashboard.teacher', ['dashboard.teacher']),
                $makeLeaf('My Courses', 'folders', 'my-courses.index', ['my-courses.*']),
                $makeLeaf('Question Banks', 'library-big', 'question-banks.index', ['question-banks.*']),
                $makeLeaf('Question Imports', 'file-up', 'question-imports.index', ['question-imports.*']),
                $makeLeaf('Exams', 'notepad-text', 'exams.index', ['exams.*']),
                $makeLeaf('Grading', 'check-check', 'exam-grading.index', ['exam-grading.*']),
                $makeLeaf('Suspicious Logs', 'shield-alert', 'suspicious-activities.index', ['suspicious-activities.*']),
                $makeLeaf('Reports', 'bar-chart-3', 'reports.index', ['reports.*']),
                $makeLeaf('Notifications', 'bell', 'notifications.index', ['notifications.*']),
            ],
        ]];
    } elseif ($role === 'student') {
        $sections = [[
            'key' => 'student-menu',
            'label' => 'Student Menu',
            'active' => false,
            'items' => [
                $makeLeaf('Dashboard', 'layout-dashboard', 'dashboard.student', ['dashboard.student']),
                $makeLeaf('My Courses', 'folders', 'my-courses.index', ['my-courses.*']),
                $makeLeaf('My Exams', 'notepad-text', 'student-exams.index', ['student-exams.*']),
                $makeLeaf('Notifications', 'bell', 'notifications.index', ['notifications.*']),
            ],
        ]];
    }

    $sections = collect($sections)->map(function ($section) {
        $section['items'] = collect($section['items'])->filter()->values()->all();
        $section['active'] = collect($section['items'])->contains(fn ($item) => (bool) ($item['active'] ?? false));
        $section['key'] = $section['key'] ?? \Illuminate\Support\Str::slug($section['label'] ?? 'group');
        return $section;
    })->filter(fn ($section) => !empty($section['items']))->values()->all();

    $menuBaseClasses = 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70';
    $menuActiveClasses = 'bg-white/22 text-white ring-1 ring-white/35 shadow-[inset_0_1px_0_rgba(255,255,255,.22),0_8px_18px_-14px_rgba(15,23,42,.85)]';
    $menuIdleClasses = 'text-white/90 hover:bg-white/12 hover:text-white';
@endphp

<aside
    class="fixed top-0 bottom-0 left-0 z-50 w-[88vw] max-w-[20rem] sm:w-[78vw] lg:w-[var(--shell-sidebar)] m-0 pt-0 bg-gradient-to-b from-deep via-primary to-sky-700 text-white transition-all duration-300 -translate-x-full lg:translate-x-0 border-r"
    style="border-color: var(--shell-divider);"
    :class="{'translate-x-0': sidebarOpen, 'lg:w-[var(--shell-sidebar-mini)]': sidebarMini, 'lg:w-[var(--shell-sidebar)]': !sidebarMini}"
>
    <div class="h-full flex flex-col">
        <div class="shell-header px-4 border-b flex items-center justify-between" style="border-color: var(--shell-divider);">
            <div class="flex items-center gap-3 min-w-0">
                <div class="h-10 w-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center font-black text-lg">{{ strtoupper(substr($teraApp['app_name'] ?? 'T', 0, 1)) }}</div>
                <div x-show="!sidebarMini" x-cloak class="min-w-0">
                    <p class="font-extrabold tracking-wide truncate">{{ $teraApp['app_name'] ?? config('app.name') }}</p>
                    <p class="text-[11px] text-white/70 truncate">{{ __('ui.platform') }} {{ $teraApp['school_name'] ?? '' }}</p>
                </div>
            </div>
            <button type="button" class="hidden lg:inline-flex p-2 rounded-lg hover:bg-white/15" @click="sidebarMini = !sidebarMini">
                <i data-lucide="panel-left-close" class="w-4 h-4" x-show="!sidebarMini"></i>
                <i data-lucide="panel-left-open" class="w-4 h-4" x-show="sidebarMini" x-cloak></i>
            </button>
        </div>

        <div class="px-4 py-3 border-b" style="border-color: var(--shell-surface-border-on-sidebar);">
            <div class="text-xs text-white/70" x-show="!sidebarMini" x-cloak>{{ auth()->user()->role->name ?? '-' }}</div>
            <div class="font-semibold text-sm truncate" x-show="!sidebarMini" x-cloak>{{ auth()->user()->full_name ?? '-' }}</div>
        </div>

        <nav class="flex-1 overflow-y-auto overflow-x-visible px-3 py-4 space-y-3">
            @foreach($sections as $section)
                <div x-data="{ open: @js($section['active']) }" class="space-y-1">
                    <button
                        type="button"
                        class="w-full flex items-center justify-between rounded-lg px-2 py-1.5 text-[11px] font-bold uppercase tracking-wide text-white/70 hover:text-white"
                        :class="sidebarMini ? 'justify-center' : ''"
                        @click="open = !open"
                        x-show="!sidebarMini"
                        x-cloak
                    >
                        <span>{{ $section['label'] }}</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="open || sidebarMini" x-cloak class="space-y-1">
                        @foreach($section['items'] as $item)
                            @if(($item['type'] ?? '') === 'leaf')
                                <a
                                    href="{{ route($item['route']) }}"
                                    class="{{ $menuBaseClasses }} {{ $item['active'] ? $menuActiveClasses : $menuIdleClasses }}"
                                    :class="sidebarMini ? 'justify-center px-2' : ''"
                                    aria-current="{{ $item['active'] ? 'page' : 'false' }}"
                                >
                                    <i data-lucide="{{ $item['icon'] }}" class="w-[18px] h-[18px] shrink-0"></i>
                                    <span x-show="!sidebarMini" x-cloak>{{ $item['label'] }}</span>
                                </a>
                            @else
                                <div x-data="{ openSub: @js($item['active'] ?? false) }" class="space-y-1 relative">
                                    <button
                                        type="button"
                                        class="{{ $menuBaseClasses }} {{ ($item['active'] ?? false) ? $menuActiveClasses : $menuIdleClasses }} w-full justify-between"
                                        :class="sidebarMini ? 'justify-center px-2' : ''"
                                        @click.stop="openSub = !openSub"
                                    >
                                        <span class="inline-flex items-center gap-3">
                                            <i data-lucide="{{ $item['icon'] }}" class="w-[18px] h-[18px] shrink-0"></i>
                                            <span x-show="!sidebarMini" x-cloak>{{ $item['label'] }}</span>
                                        </span>
                                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="openSub ? 'rotate-180' : ''" x-show="!sidebarMini" x-cloak></i>
                                    </button>

                                    <div x-show="openSub && !sidebarMini" x-cloak class="ml-4 pl-2 border-l border-white/20 space-y-1">
                                        @foreach($item['children'] as $child)
                                            <a
                                                href="{{ route($child['route']) }}"
                                                class="{{ $menuBaseClasses }} {{ $child['active'] ? $menuActiveClasses : $menuIdleClasses }} !py-2 !text-[13px]"
                                                aria-current="{{ $child['active'] ? 'page' : 'false' }}"
                                            >
                                                <i data-lucide="{{ $child['icon'] }}" class="w-4 h-4 shrink-0"></i>
                                                <span>{{ $child['label'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>

                                    <div
                                        x-show="openSub && sidebarMini"
                                        x-cloak
                                        class="hidden lg:flex lg:flex-col mt-1 space-y-1"
                                    >
                                        @foreach($item['children'] as $child)
                                            <a
                                                href="{{ route($child['route']) }}"
                                                class="{{ $menuBaseClasses }} {{ $child['active'] ? $menuActiveClasses : $menuIdleClasses }} justify-center !w-9 !h-9 !px-0 !py-0 !rounded-lg"
                                                aria-current="{{ $child['active'] ? 'page' : 'false' }}"
                                                title="{{ $child['label'] }}"
                                            >
                                                <i data-lucide="{{ $child['icon'] }}" class="w-4 h-4 shrink-0"></i>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        <div class="p-4 border-t" style="border-color: var(--shell-surface-border-on-sidebar);">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full tera-btn bg-white/15 hover:bg-white/25 text-white justify-center">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span x-show="!sidebarMini" x-cloak>{{ __('ui.logout') }}</span>
                </button>
            </form>
        </div>
    </div>
</aside>
