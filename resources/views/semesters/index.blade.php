@extends('layouts.app', ['title' => 'Semesters'])

@section('content')
<div x-data="semesterCrudPage({ years: @js($academicYears->map(fn($y) => ['id' => $y->id, 'name' => $y->name])->values()) })">
    <x-ui.page-header title="Semester Management" subtitle="Manage semester periods and active semester configuration.">
        <x-slot:actions>
            <button type="button" class="tera-btn tera-btn-primary" @click="openCreate">
                <i data-lucide="plus" class="w-4 h-4"></i>{{ __('ui.add_semester') }}
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="tera-table-wrap">
        <table class="tera-table">
            <thead>
            <tr>
                <th class="text-left">{{ __('ui.name') }}</th>
                <th class="text-left">{{ __('ui.code') }}</th>
                <th class="text-center">{{ __('ui.academic_year') }}</th>
                <th class="text-center">{{ __('ui.active') }}</th>
                <th class="text-right">{{ __('ui.action') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($semesters as $semester)
                <tr>
                    <td>{{ $semester->name }}</td>
                    <td>{{ $semester->code }}</td>
                    <td class="text-center">{{ $semester->academicYear?->name }}</td>
                    <td class="text-center"><span class="tera-badge {{ $semester->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">{{ $semester->is_active ? __('ui.active') : __('ui.inactive') }}</span></td>
                    <td class="text-right">
                        <div class="inline-flex gap-2">
                            <button type="button" class="tera-btn tera-btn-muted !px-3 !py-1.5" @click="openEdit({{ $semester->id }})">{{ __('ui.edit') }}</button>
                            <button type="button" class="tera-btn tera-btn-danger !px-3 !py-1.5" @click="destroyItem({{ $semester->id }}, @js($semester->name))">{{ __('ui.delete') }}</button>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $semesters->links() }}</div>

    <x-ui.modal name="showModal" :title="__('ui.semester_form')" maxWidth="max-w-3xl">
        <form @submit.prevent="submitForm" class="space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
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
                    <label class="tera-label">{{ __('ui.name') }}</label>
                    <input x-model="form.name" class="tera-input" required>
                    <p class="mt-1 text-xs text-red-600" x-text="errors.name?.[0]"></p>
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.code') }}</label>
                    <input x-model="form.code" class="tera-input" required>
                    <p class="mt-1 text-xs text-red-600" x-text="errors.code?.[0]"></p>
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.active') }}</label>
                    <select x-model="form.is_active" class="tera-select">
                        <option value="0">{{ __('ui.no') }}</option>
                        <option value="1">{{ __('ui.yes') }}</option>
                    </select>
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.start_date') }}</label>
                    <input type="date" x-model="form.start_date" class="tera-input">
                    <p class="mt-1 text-xs text-red-600" x-text="errors.start_date?.[0]"></p>
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.end_date') }}</label>
                    <input type="date" x-model="form.end_date" class="tera-input">
                    <p class="mt-1 text-xs text-red-600" x-text="errors.end_date?.[0]"></p>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="tera-btn tera-btn-muted" @click="showModal=false">{{ __('ui.cancel') }}</button>
                <button type="submit" class="tera-btn tera-btn-primary" :disabled="loading">
                    <span x-show="!loading" x-text="isEdit ? @js(__('ui.update_semester')) : @js(__('ui.create_semester'))"></span>
                    <span x-show="loading" x-cloak>{{ __('ui.saving') }}</span>
                </button>
            </div>
        </form>
    </x-ui.modal>
</div>

<script>
function semesterCrudPage({ years }) {
    return {
        years,
        showModal: false,
        loading: false,
        isEdit: false,
        editId: null,
        errors: {},
        form: {
            academic_year_id: years[0] ? String(years[0].id) : '',
            name: '',
            code: '',
            is_active: '0',
            start_date: '',
            end_date: '',
        },

        resetForm() {
            this.errors = {};
            this.form = {
                academic_year_id: this.years[0] ? String(this.years[0].id) : '',
                name: '',
                code: '',
                is_active: '0',
                start_date: '',
                end_date: '',
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
                const res = await fetch(`/super-admin/semesters/${id}/edit`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const payload = await res.json();
                if (!res.ok) throw new Error(payload.message || @js(__('ui.failed_to_load_semester')));

                this.isEdit = true;
                this.editId = id;
                this.form = {
                    academic_year_id: payload.data.academic_year_id ? String(payload.data.academic_year_id) : '',
                    name: payload.data.name ?? '',
                    code: payload.data.code ?? '',
                    is_active: payload.data.is_active ? '1' : '0',
                    start_date: payload.data.start_date ?? '',
                    end_date: payload.data.end_date ?? '',
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
            const url = this.isEdit ? `/super-admin/semesters/${this.editId}` : `/super-admin/semesters`;
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
                    throw new Error(payload.message || @js(__('ui.failed_to_save_semester')));
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
                title: @js(__('ui.delete_semester_question')),
                text: `Delete ${name}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                confirmButtonText: @js(__('ui.delete')),
            });
            if (!confirm.isConfirmed) return;

            try {
                const res = await fetch(`/super-admin/semesters/${id}`, {
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
                if (!res.ok) throw new Error(payload.message || @js(__('ui.failed_to_delete_semester')));
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


