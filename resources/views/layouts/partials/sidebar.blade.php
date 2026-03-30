@php
    $role = auth()->user()->role->code ?? '';
    $items = [];

    if ($role === 'super_admin') {
        $items = [
            ['label' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'dashboard.super-admin'],
            ['label' => 'Users', 'icon' => 'users', 'route' => 'users.index'],
            ['label' => 'Classes', 'icon' => 'school', 'route' => 'classes.index'],
            ['label' => 'Subjects', 'icon' => 'book-open', 'route' => 'subjects.index'],
            ['label' => 'Assignments', 'icon' => 'git-branch-plus', 'route' => 'assignments.class-students.index'],
            ['label' => 'Courses', 'icon' => 'folders', 'route' => 'courses.index'],
            ['label' => 'Question Banks', 'icon' => 'library-big', 'route' => 'question-banks.index'],
            ['label' => 'Question Imports', 'icon' => 'file-up', 'route' => 'question-imports.index'],
            ['label' => 'Exams', 'icon' => 'notepad-text', 'route' => 'exams.index'],
            ['label' => 'Grading', 'icon' => 'check-check', 'route' => 'exam-grading.index'],
            ['label' => 'Reports', 'icon' => 'bar-chart-3', 'route' => 'reports.index'],
            ['label' => 'Suspicious Logs', 'icon' => 'shield-alert', 'route' => 'suspicious-activities.index'],
            ['label' => 'Audit Logs', 'icon' => 'scroll-text', 'route' => 'super-admin.audit-logs.index'],
            ['label' => 'Login Logs', 'icon' => 'fingerprint', 'route' => 'super-admin.login-logs.index'],
            ['label' => 'Restore Center', 'icon' => 'rotate-ccw', 'route' => 'super-admin.restore-center.index'],
            ['label' => 'Academic Years', 'icon' => 'calendar-days', 'route' => 'super-admin.academic-years.index'],
            ['label' => 'Semesters', 'icon' => 'calendar-range', 'route' => 'super-admin.semesters.index'],
            ['label' => 'Settings', 'icon' => 'settings-2', 'route' => 'super-admin.settings.edit'],
        ];
    } elseif ($role === 'admin') {
        $items = [
            ['label' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'dashboard.admin'],
            ['label' => 'Users', 'icon' => 'users', 'route' => 'users.index'],
            ['label' => 'Classes', 'icon' => 'school', 'route' => 'classes.index'],
            ['label' => 'Subjects', 'icon' => 'book-open', 'route' => 'subjects.index'],
            ['label' => 'Assignments', 'icon' => 'git-branch-plus', 'route' => 'assignments.class-students.index'],
            ['label' => 'Courses', 'icon' => 'folders', 'route' => 'courses.index'],
            ['label' => 'Question Banks', 'icon' => 'library-big', 'route' => 'question-banks.index'],
            ['label' => 'Question Imports', 'icon' => 'file-up', 'route' => 'question-imports.index'],
            ['label' => 'Exams', 'icon' => 'notepad-text', 'route' => 'exams.index'],
            ['label' => 'Grading', 'icon' => 'check-check', 'route' => 'exam-grading.index'],
            ['label' => 'Reports', 'icon' => 'bar-chart-3', 'route' => 'reports.index'],
        ];
    } elseif ($role === 'principal') {
        $items = [
            ['label' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'dashboard.principal'],
            ['label' => 'Exams', 'icon' => 'notepad-text', 'route' => 'exams.index'],
            ['label' => 'Reports', 'icon' => 'bar-chart-3', 'route' => 'reports.index'],
        ];
    } elseif ($role === 'teacher') {
        $items = [
            ['label' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'dashboard.teacher'],
            ['label' => 'My Courses', 'icon' => 'folders', 'route' => 'my-courses.index'],
            ['label' => 'Question Banks', 'icon' => 'library-big', 'route' => 'question-banks.index'],
            ['label' => 'Question Imports', 'icon' => 'file-up', 'route' => 'question-imports.index'],
            ['label' => 'Exams', 'icon' => 'notepad-text', 'route' => 'exams.index'],
            ['label' => 'Grading', 'icon' => 'check-check', 'route' => 'exam-grading.index'],
            ['label' => 'Suspicious Logs', 'icon' => 'shield-alert', 'route' => 'suspicious-activities.index'],
            ['label' => 'Reports', 'icon' => 'bar-chart-3', 'route' => 'reports.index'],
            ['label' => 'Notifications', 'icon' => 'bell', 'route' => 'notifications.index'],
        ];
    } elseif ($role === 'student') {
        $items = [
            ['label' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'dashboard.student'],
            ['label' => 'My Courses', 'icon' => 'folders', 'route' => 'my-courses.index'],
            ['label' => 'My Exams', 'icon' => 'notepad-text', 'route' => 'student-exams.index'],
            ['label' => 'Notifications', 'icon' => 'bell', 'route' => 'notifications.index'],
        ];
    }
@endphp

@php
    $items = collect($items)
        ->filter(fn (array $item) => isset($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']))
        ->values()
        ->all();
@endphp

<aside
    class="fixed inset-y-0 left-0 z-50 w-72 bg-gradient-to-b from-deep via-primary to-sky-700 text-white transition-all duration-300 -translate-x-full lg:translate-x-0"
    :class="{'translate-x-0': sidebarOpen, 'w-24': sidebarMini, 'w-72': !sidebarMini}"
>
    <div class="h-full flex flex-col">
        <div class="px-4 py-5 border-b border-white/15 flex items-center justify-between">
            <div class="flex items-center gap-3 min-w-0">
                <div class="h-10 w-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center font-black text-lg">T</div>
                <div x-show="!sidebarMini" x-cloak class="min-w-0">
                    <p class="font-extrabold tracking-wide truncate">Teramia</p>
                    <p class="text-[11px] text-white/70 truncate">E-Learning Platform</p>
                </div>
            </div>
            <button type="button" class="hidden lg:inline-flex p-2 rounded-lg hover:bg-white/15" @click="sidebarMini = !sidebarMini">
                <i data-lucide="panel-left-close" class="w-4 h-4" x-show="!sidebarMini"></i>
                <i data-lucide="panel-left-open" class="w-4 h-4" x-show="sidebarMini" x-cloak></i>
            </button>
        </div>

        <div class="px-4 py-3 border-b border-white/15">
            <div class="text-xs text-white/70" x-show="!sidebarMini" x-cloak>{{ auth()->user()->role->name ?? '-' }}</div>
            <div class="font-semibold text-sm truncate" x-show="!sidebarMini" x-cloak>{{ auth()->user()->full_name ?? '-' }}</div>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            @foreach($items as $item)
                @php
                    $currentRouteName = request()->route()?->getName() ?? '';
                    $active = request()->routeIs($item['route'])
                        || ($currentRouteName !== '' && str_starts_with($currentRouteName, explode('.', $item['route'])[0].'.'));
                @endphp
                <a
                    href="{{ route($item['route']) }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition"
                    :class="sidebarMini ? 'justify-center px-2' : ''"
                    @if($active)
                        style="background: rgba(255,255,255,.18); font-weight:700;"
                    @else
                        style="color: rgba(255,255,255,.9);"
                    @endif
                >
                    <i data-lucide="{{ $item['icon'] }}" class="w-[18px] h-[18px] shrink-0"></i>
                    <span x-show="!sidebarMini" x-cloak>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="p-4 border-t border-white/15">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full tera-btn bg-white/15 hover:bg-white/25 text-white justify-center">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span x-show="!sidebarMini" x-cloak>Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>
