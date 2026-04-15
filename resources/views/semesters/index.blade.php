@extends('layouts.app', ['title' => __('ui.semesters')])

@section('content')
<div x-data="semesterCrudPage({ years: @js($academicYears->map(fn($y) => ['id' => $y->id, 'name' => $y->name])->values()) })" data-async-list data-fragment="#semesters-table-fragment">
    <x-ui.page-header :title="__('ui.semester_management_title')" :subtitle="__('ui.semester_management_subtitle')">
        <x-slot:actions>
            <button type="button" class="tera-btn tera-btn-primary" @click="openCreate">
                <i data-lucide="plus" class="w-4 h-4"></i>{{ __('ui.add_semester') }}
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.table-toolbar :search-value="request('q')" :search-placeholder="__('ui.search_semester_name_or_code')">
        <x-slot:filters>
            <div>
                <label class="tera-label">{{ __('ui.academic_year') }}</label>
                <select name="academic_year_id" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" @selected((string)request('academic_year_id') === (string)$year->id)>{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tera-label">{{ __('ui.active') }}</label>
                <select name="is_active" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    <option value="1" @selected(request('is_active') === '1')>{{ __('ui.active') }}</option>
                    <option value="0" @selected(request('is_active') === '0')>{{ __('ui.inactive') }}</option>
                </select>
            </div>
        </x-slot:filters>
    </x-ui.table-toolbar>

    <div id="semesters-table-fragment">
        <div class="tera-table-wrap">
            <table class="tera-table">
                <thead>
                <tr>
                    <th>{{ __('ui.no') }}</th>
                    <th>{{ __('ui.name') }}</th>
                    <th>{{ __('ui.code') }}</th>
                    <th>{{ __('ui.academic_year') }}</th>
                    <th>{{ __('ui.active') }}</th>
                    <th>{{ __('ui.action') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($semesters as $semester)
                    <tr>
                        <td>{{ $semesters->firstItem() + $loop->index }}</td>
                        <td>{{ $semester->name }}</td>
                        <td>{{ $semester->code }}</td>
                        <td>{{ $semester->academicYear?->name }}</td>
                        <td><span class="tera-badge tera-status-badge {{ $semester->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">{{ $semester->is_active ? __('ui.active') : __('ui.inactive') }}</span></td>
                        <td>
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
    </div>

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
                const { response, payload } = await window.Teramia.fetchJson(`/super-admin/semesters/${id}/edit`);
                if (!response.ok || !payload?.ok) throw new Error(payload?.message || @js(__('ui.failed_to_load_semester')));

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
                window.Teramia.toast('error', error.message);
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
                const { response, payload } = await window.Teramia.fetchJson(url, {
                    method: 'POST',
                    body: JSON.stringify(body),
                });
                if (!response.ok) {
                    if (response.status === 422) {
                        this.errors = payload.errors || {};
                        return;
                    }
                    throw new Error(payload.message || @js(__('ui.failed_to_save_semester')));
                }

                this.showModal = false;
                await window.Teramia.toast('success', payload.message);
                await window.Teramia.refreshFragment(window.location.href, '#semesters-table-fragment');
            } catch (error) {
                window.Teramia.toast('error', error.message);
            } finally {
                this.loading = false;
            }
        },

        async destroyItem(id, name) {
            const confirm = await window.Teramia.confirmDelete(
                @js(__('ui.delete_semester_question')),
                `${@js(__('ui.delete'))} ${name}?`
            );
            if (!confirm.isConfirmed) return;

            try {
                const { response, payload } = await window.Teramia.fetchJson(`/super-admin/semesters/${id}`, {
                    method: 'POST',
                    body: JSON.stringify({ _method: 'DELETE' }),
                });
                if (!response.ok) throw new Error(payload.message || @js(__('ui.failed_to_delete_semester')));
                await window.Teramia.toast('success', payload.message);
                await window.Teramia.refreshFragment(window.location.href, '#semesters-table-fragment');
            } catch (error) {
                window.Teramia.toast('error', error.message);
            }
        },
    };
}
</script>
@endsection


