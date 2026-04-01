@extends('layouts.app', ['title' => 'Subjects'])

@section('content')
<div x-data="subjectCrudPage()">
    <x-ui.page-header title="Subject Management" subtitle="Manage bilingual subject data for the school academic structure.">
        <x-slot:actions>
            <button type="button" class="tera-btn tera-btn-primary" @click="openCreate">
                <i data-lucide="plus" class="w-4 h-4"></i>{{ __('ui.add_subject') }}
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.table-toolbar :search-value="request('q')" search-placeholder="Search subject name or code">
        <x-slot:filters>
            <div>
                <label class="tera-label">{{ __('ui.active') }}</label>
                <select name="is_active" class="tera-select">
                    <option value="">All</option>
                    <option value="1" @selected(request('is_active') === '1')>{{ __('ui.active') }}</option>
                    <option value="0" @selected(request('is_active') === '0')>{{ __('ui.inactive') }}</option>
                </select>
            </div>
        </x-slot:filters>
    </x-ui.table-toolbar>

    <div class="tera-table-wrap">
        <table class="tera-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>{{ __('ui.subject_name_id') }}</th>
                    <th>{{ __('ui.subject_name_en') }}</th>
                    <th>{{ __('ui.code') }}</th>
                    <th>{{ __('ui.active') }}</th>
                    <th>{{ __('ui.action') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($subjects as $subject)
                <tr>
                    <td>{{ $subjects->firstItem() + $loop->index }}</td>
                    <td class="font-semibold">{{ $subject->name_id }}</td>
                    <td>{{ $subject->name_en ?: '-' }}</td>
                    <td>{{ $subject->code }}</td>
                    <td><span class="tera-badge {{ $subject->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">{{ $subject->is_active ? __('ui.active') : __('ui.inactive') }}</span></td>
                    <td>
                        <div class="inline-flex items-center gap-2">
                            <button type="button" class="tera-btn tera-btn-muted !px-3 !py-1.5" @click="openEdit({{ $subject->id }})">{{ __('ui.edit') }}</button>
                            <button type="button" class="tera-btn tera-btn-danger !px-3 !py-1.5" @click="destroyItem({{ $subject->id }}, @js($subject->name_id))">{{ __('ui.delete') }}</button>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $subjects->links() }}</div>

    <x-ui.modal name="showModal" :title="__('ui.subject_form')" maxWidth="max-w-3xl">
        <form @submit.prevent="submitForm" class="space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="tera-label">Name (Indonesian)</label>
                    <input x-model="form.name_id" class="tera-input" required>
                    <p class="mt-1 text-xs text-red-600" x-text="errors.name_id?.[0]"></p>
                </div>
                <div>
                    <label class="tera-label">Name (English)</label>
                    <input x-model="form.name_en" class="tera-input">
                    <p class="mt-1 text-xs text-red-600" x-text="errors.name_en?.[0]"></p>
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.code') }}</label>
                    <input x-model="form.code" class="tera-input">
                    <p class="mt-1 text-xs text-red-600" x-text="errors.code?.[0]"></p>
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.active') }}</label>
                    <select x-model="form.is_active" class="tera-select">
                        <option value="1">{{ __('ui.yes') }}</option>
                        <option value="0">{{ __('ui.no') }}</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="tera-label">{{ __('ui.description') }}</label>
                    <textarea x-model="form.description" class="tera-textarea" rows="4"></textarea>
                    <p class="mt-1 text-xs text-red-600" x-text="errors.description?.[0]"></p>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="tera-btn tera-btn-muted" @click="showModal=false">{{ __('ui.cancel') }}</button>
                <button type="submit" class="tera-btn tera-btn-primary" :disabled="loading">
                    <span x-show="!loading" x-text="isEdit ? @js(__('ui.update_subject')) : @js(__('ui.create_subject'))"></span>
                    <span x-show="loading" x-cloak>{{ __('ui.saving') }}</span>
                </button>
            </div>
        </form>
    </x-ui.modal>
</div>

<script>
function subjectCrudPage() {
    return {
        showModal: false,
        loading: false,
        isEdit: false,
        editId: null,
        errors: {},
        form: {
            name_id: '',
            name_en: '',
            code: '',
            description: '',
            is_active: '1',
        },

        resetForm() {
            this.errors = {};
            this.form = { name_id: '', name_en: '', code: '', description: '', is_active: '1' };
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
                const res = await fetch(`/subjects/${id}/edit`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const payload = await res.json();
                if (!res.ok) throw new Error(payload.message || @js(__('ui.failed_to_load_subject')));

                this.isEdit = true;
                this.editId = id;
                this.form = {
                    name_id: payload.data.name_id ?? '',
                    name_en: payload.data.name_en ?? '',
                    code: payload.data.code ?? '',
                    description: payload.data.description ?? '',
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
            const url = this.isEdit ? `/subjects/${this.editId}` : '/subjects';
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
                    throw new Error(payload.message || @js(__('ui.failed_to_save_subject')));
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
                title: @js(__('ui.delete_subject_question')),
                text: `Delete ${name}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                confirmButtonText: @js(__('ui.delete')),
            });
            if (!confirm.isConfirmed) return;

            try {
                const res = await fetch(`/subjects/${id}`, {
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
                if (!res.ok) throw new Error(payload.message || @js(__('ui.failed_to_delete_subject')));
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
