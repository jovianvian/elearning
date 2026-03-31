@extends('layouts.app', ['title' => 'Classes'])

@section('content')
<div x-data="classCrudPage({
    teachers: @js($teachers->map(fn($t) => ['id' => $t->id, 'full_name' => $t->full_name])->values()),
    years: @js($academicYears->map(fn($y) => ['id' => $y->id, 'name' => $y->name])->values())
})">
    <x-ui.page-header title="Class Management" subtitle="Manage active class structure for the current academic year.">
        <x-slot:actions>
            <button type="button" class="tera-btn tera-btn-primary" @click="openCreate">
                <i data-lucide="plus" class="w-4 h-4"></i>{{ __('ui.add_class') }}
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="tera-table-wrap">
        <table class="tera-table">
            <thead>
                <tr>
                    <th class="text-left">{{ __('ui.name') }}</th>
                    <th class="text-center">{{ __('ui.grade_level') }}</th>
                    <th class="text-center">{{ __('ui.academic_year') }}</th>
                    <th class="text-center">{{ __('ui.homeroom_teacher') }}</th>
                    <th class="text-center">{{ __('ui.active') }}</th>
                    <th class="text-right">{{ __('ui.action') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($classes as $class)
                <tr>
                    <td class="font-semibold">{{ $class->name }}</td>
                    <td class="text-center">{{ $class->grade_level }}</td>
                    <td class="text-center">{{ $class->academicYear?->name }}</td>
                    <td class="text-center">{{ $class->homeroomTeacher?->full_name ?: '-' }}</td>
                    <td class="text-center"><span class="tera-badge {{ $class->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">{{ $class->is_active ? __('ui.active') : __('ui.inactive') }}</span></td>
                    <td class="text-right">
                        <div class="inline-flex items-center gap-2">
                            <button type="button" class="tera-btn tera-btn-muted !px-3 !py-1.5" @click="openEdit({{ $class->id }})">{{ __('ui.edit') }}</button>
                            <button type="button" class="tera-btn tera-btn-danger !px-3 !py-1.5" @click="destroyItem({{ $class->id }}, @js($class->name))">{{ __('ui.delete') }}</button>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $classes->links() }}</div>

    <x-ui.modal name="showModal" :title="__('ui.class_form')" maxWidth="max-w-3xl">
        <form @submit.prevent="submitForm" class="space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="tera-label">{{ __('ui.class_name') }}</label>
                    <input x-model="form.name" class="tera-input" required>
                    <p class="mt-1 text-xs text-red-600" x-text="errors.name?.[0]"></p>
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.code') }}</label>
                    <input x-model="form.code" class="tera-input">
                    <p class="mt-1 text-xs text-red-600" x-text="errors.code?.[0]"></p>
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.grade_level') }}</label>
                    <select x-model="form.grade_level" class="tera-select">
                        <option value="7">7</option>
                        <option value="8">8</option>
                        <option value="9">9</option>
                    </select>
                    <p class="mt-1 text-xs text-red-600" x-text="errors.grade_level?.[0]"></p>
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.academic_year') }}</label>
                    <select x-model="form.academic_year_id" class="tera-select" required>
                        <template x-for="year in years" :key="year.id">
                            <option :value="String(year.id)" x-text="year.name"></option>
                        </template>
                    </select>
                    <p class="mt-1 text-xs text-red-600" x-text="errors.academic_year_id?.[0]"></p>
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.homeroom_teacher') }}</label>
                    <select x-model="form.homeroom_teacher_id" class="tera-select">
                        <option value="">-</option>
                        <template x-for="teacher in teachers" :key="teacher.id">
                            <option :value="String(teacher.id)" x-text="teacher.full_name"></option>
                        </template>
                    </select>
                    <p class="mt-1 text-xs text-red-600" x-text="errors.homeroom_teacher_id?.[0]"></p>
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.active') }}</label>
                    <select x-model="form.is_active" class="tera-select">
                        <option value="1">{{ __('ui.yes') }}</option>
                        <option value="0">{{ __('ui.no') }}</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="tera-btn tera-btn-muted" @click="showModal=false">{{ __('ui.cancel') }}</button>
                <button type="submit" class="tera-btn tera-btn-primary" :disabled="loading">
                    <span x-show="!loading" x-text="isEdit ? @js(__('ui.update_class')) : @js(__('ui.create_class'))"></span>
                    <span x-show="loading" x-cloak>{{ __('ui.saving') }}</span>
                </button>
            </div>
        </form>
    </x-ui.modal>
</div>

<script>
function classCrudPage({ teachers, years }) {
    return {
        teachers,
        years,
        showModal: false,
        loading: false,
        isEdit: false,
        editId: null,
        errors: {},
        form: {
            name: '',
            code: '',
            grade_level: '7',
            academic_year_id: years[0] ? String(years[0].id) : '',
            homeroom_teacher_id: '',
            is_active: '1',
        },

        resetForm() {
            this.errors = {};
            this.form = {
                name: '',
                code: '',
                grade_level: '7',
                academic_year_id: this.years[0] ? String(this.years[0].id) : '',
                homeroom_teacher_id: '',
                is_active: '1',
            };
        },

        openCreate() {
            this.isEdit = false;
            this.editId = null;
            this.resetForm();
            this.showModal = true;
        },

        async openEdit(id) {
            this.loading = true;
            this.errors = {};
            try {
                const res = await fetch(`/classes/${id}/edit`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const payload = await res.json();
                if (!res.ok) throw new Error(payload.message || @js(__('ui.failed_to_load_class')));

                this.isEdit = true;
                this.editId = id;
                this.form = {
                    name: payload.data.name ?? '',
                    code: payload.data.code ?? '',
                    grade_level: String(payload.data.grade_level ?? '7'),
                    academic_year_id: payload.data.academic_year_id ? String(payload.data.academic_year_id) : '',
                    homeroom_teacher_id: payload.data.homeroom_teacher_id ? String(payload.data.homeroom_teacher_id) : '',
                    is_active: payload.data.is_active ? '1' : '0',
                };
                this.showModal = true;
            } catch (error) {
                Swal.fire({ icon: 'error', title: @js(__('ui.error')), text: error.message });
            } finally {
                this.loading = false;
            }
        },

        async submitForm() {
            this.loading = true;
            this.errors = {};
            const url = this.isEdit ? `/classes/${this.editId}` : '/classes';
            const body = { ...this.form };
            if (this.isEdit) body._method = 'PUT';

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(body),
                });
                const payload = await res.json();
                if (!res.ok) {
                    if (res.status === 422) {
                        this.errors = payload.errors || {};
                        return;
                    }
                    throw new Error(payload.message || @js(__('ui.failed_to_save_class')));
                }

                this.showModal = false;
                Swal.fire({ icon: 'success', title: @js(__('ui.success')), text: payload.message, timer: 1200, showConfirmButton: false })
                    .then(() => window.location.reload());
            } catch (error) {
                Swal.fire({ icon: 'error', title: @js(__('ui.error')), text: error.message });
            } finally {
                this.loading = false;
            }
        },

        async destroyItem(id, name) {
            const confirm = await Swal.fire({
                title: @js(__('ui.delete_class_question')),
                text: `Delete ${name}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                confirmButtonText: @js(__('ui.delete')),
            });
            if (!confirm.isConfirmed) return;

            try {
                const res = await fetch(`/classes/${id}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ _method: 'DELETE' }),
                });
                const payload = await res.json();
                if (!res.ok) throw new Error(payload.message || @js(__('ui.failed_to_delete_class')));
                Swal.fire({ icon: 'success', title: @js(__('ui.deleted')), text: payload.message, timer: 1200, showConfirmButton: false })
                    .then(() => window.location.reload());
            } catch (error) {
                Swal.fire({ icon: 'error', title: @js(__('ui.error')), text: error.message });
            }
        },
    };
}
</script>
@endsection
