@extends('layouts.app', ['title' => 'Academic Years'])

@section('content')
<div x-data="academicYearCrudPage()">
    <x-ui.page-header title="Academic Year Management" subtitle="Manage academic year periods and active year configuration.">
        <x-slot:actions>
            <button type="button" class="tera-btn tera-btn-primary" @click="openCreate">
                <i data-lucide="plus" class="w-4 h-4"></i>{{ __('ui.add_academic_year') }}
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="tera-table-wrap">
        <table class="tera-table">
            <thead>
            <tr>
                <th class="text-left">{{ __('ui.name') }}</th>
                <th class="text-center">{{ __('ui.start') }}</th>
                <th class="text-center">{{ __('ui.end') }}</th>
                <th class="text-center">{{ __('ui.active') }}</th>
                <th class="text-right">{{ __('ui.action') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($academicYears as $year)
                <tr>
                    <td class="font-semibold">{{ $year->name }}</td>
                    <td class="text-center">{{ $year->start_date?->format('Y-m-d') }}</td>
                    <td class="text-center">{{ $year->end_date?->format('Y-m-d') }}</td>
                    <td class="text-center"><span class="tera-badge {{ $year->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">{{ $year->is_active ? __('ui.active') : __('ui.inactive') }}</span></td>
                    <td class="text-right">
                        <div class="inline-flex gap-2">
                            <button type="button" class="tera-btn tera-btn-muted !px-3 !py-1.5" @click="openEdit({{ $year->id }})">{{ __('ui.edit') }}</button>
                            <button type="button" class="tera-btn tera-btn-danger !px-3 !py-1.5" @click="destroyItem({{ $year->id }}, @js($year->name))">{{ __('ui.delete') }}</button>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $academicYears->links() }}</div>

    <x-ui.modal name="showModal" :title="__('ui.academic_year_form')" maxWidth="max-w-2xl">
        <form @submit.prevent="submitForm" class="space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="tera-label">{{ __('ui.name') }}</label>
                    <input x-model="form.name" class="tera-input" required>
                    <p class="mt-1 text-xs text-red-600" x-text="errors.name?.[0]"></p>
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.is_active') }}</label>
                    <select x-model="form.is_active" class="tera-select">
                        <option value="0">{{ __('ui.no') }}</option>
                        <option value="1">{{ __('ui.yes') }}</option>
                    </select>
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.start_date') }}</label>
                    <input type="date" x-model="form.start_date" class="tera-input" required>
                    <p class="mt-1 text-xs text-red-600" x-text="errors.start_date?.[0]"></p>
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.end_date') }}</label>
                    <input type="date" x-model="form.end_date" class="tera-input" required>
                    <p class="mt-1 text-xs text-red-600" x-text="errors.end_date?.[0]"></p>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="tera-btn tera-btn-muted" @click="showModal=false">{{ __('ui.cancel') }}</button>
                <button type="submit" class="tera-btn tera-btn-primary" :disabled="loading">
                    <span x-show="!loading" x-text="isEdit ? @js(__('ui.update_academic_year')) : @js(__('ui.create_academic_year'))"></span>
                    <span x-show="loading" x-cloak>{{ __('ui.saving') }}</span>
                </button>
            </div>
        </form>
    </x-ui.modal>
</div>

<script>
function academicYearCrudPage() {
    return {
        showModal: false,
        loading: false,
        isEdit: false,
        editId: null,
        errors: {},
        form: { name: '', is_active: '0', start_date: '', end_date: '' },

        openCreate() {
            this.isEdit = false;
            this.editId = null;
            this.errors = {};
            this.form = { name: '', is_active: '0', start_date: '', end_date: '' };
            this.showModal = true;
        },

        async openEdit(id) {
            this.loading = true;
            this.errors = {};
            try {
                const res = await fetch(`/super-admin/academic-years/${id}/edit`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const payload = await res.json();
                if (!res.ok) throw new Error(payload.message || @js(__('ui.failed_to_load_academic_year')));

                this.isEdit = true;
                this.editId = id;
                this.form = {
                    name: payload.data.name ?? '',
                    is_active: payload.data.is_active ? '1' : '0',
                    start_date: payload.data.start_date ? String(payload.data.start_date).slice(0, 10) : '',
                    end_date: payload.data.end_date ? String(payload.data.end_date).slice(0, 10) : '',
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
            const url = this.isEdit ? `/super-admin/academic-years/${this.editId}` : `/super-admin/academic-years`;
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
                    throw new Error(payload.message || @js(__('ui.failed_to_save_academic_year')));
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
                title: @js(__('ui.delete_academic_year_question')),
                text: `Delete ${name}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                confirmButtonText: @js(__('ui.delete')),
            });
            if (!confirm.isConfirmed) return;

            try {
                const res = await fetch(`/super-admin/academic-years/${id}`, {
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
                if (!res.ok) throw new Error(payload.message || @js(__('ui.failed_to_delete_academic_year')));
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


