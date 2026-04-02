@extends('layouts.app', ['title' => 'Question Banks'])

@section('content')
<div x-data="questionBankCrudPage({ subjects: @js(($subjects ?? collect())->map(fn($s) => ['id' => $s->id, 'name' => $s->name_id, 'code' => $s->code])->values()) })">
    <x-ui.page-header title="Question Bank Management" subtitle="Manage shared and private subject question banks.">
        <x-slot:actions>
            <a href="{{ route('question-banks.create') }}" class="tera-btn tera-btn-primary">
                <i data-lucide="plus" class="w-4 h-4"></i>{{ __('ui.add_question_bank') }}
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.table-toolbar :search-value="request('q')" search-placeholder="Search bank title or subject">
        <x-slot:filters>
            <div>
                <label class="tera-label">{{ __('ui.subjects') }}</label>
                <select name="subject_id" class="tera-select">
                    <option value="">All</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((string)request('subject_id') === (string)$subject->id)>{{ $subject->name_id }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tera-label">{{ __('ui.visibility') }}</label>
                <select name="visibility" class="tera-select">
                    <option value="">All</option>
                    <option value="subject_shared" @selected(request('visibility') === 'subject_shared')>{{ __('ui.shared') }}</option>
                    <option value="private" @selected(request('visibility') === 'private')>{{ __('ui.private') }}</option>
                </select>
            </div>
        </x-slot:filters>
    </x-ui.table-toolbar>

    <div class="tera-table-wrap">
        <table class="tera-table">
            <thead>
            <tr>
                <th>No</th>
                <th>{{ __('ui.name') }}</th>
                <th>{{ __('ui.subjects') }}</th>
                <th>{{ __('ui.visibility') }}</th>
                <th>{{ __('ui.questions') }}</th>
                <th>{{ __('ui.creator') }}</th>
                <th>{{ __('ui.action') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($banks as $bank)
                <tr>
                    <td>{{ $banks->firstItem() + $loop->index }}</td>
                    <td class="font-semibold">{{ $bank->title }}</td>
                    <td>{{ $bank->subject->name_id ?? '-' }}</td>
                    <td>
                        @if($bank->visibility === 'subject_shared')
                            <span class="tera-badge bg-skyx/20 text-sky-700">{{ __('ui.shared') }}</span>
                        @else
                            <span class="tera-badge bg-slate-200 text-slate-700">{{ __('ui.private') }}</span>
                        @endif
                    </td>
                    <td>{{ $bank->questions_count }}</td>
                    <td>{{ $bank->creator->full_name ?? '-' }}</td>
                    <td>
                        <div class="inline-flex justify-center gap-2">
                            <a href="{{ route('question-banks.show', $bank) }}" class="tera-btn tera-btn-muted !px-3 !py-1.5">{{ __('ui.view') }}</a>
                            <a href="{{ route('question-banks.edit', $bank) }}" class="tera-btn tera-btn-muted !px-3 !py-1.5">{{ __('ui.edit') }}</a>
                            <button type="button" class="tera-btn tera-btn-danger !px-3 !py-1.5" @click="destroyItem({{ $bank->id }}, @js($bank->title))">{{ __('ui.delete') }}</button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">{{ __('ui.no_question_banks') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $banks->links() }}</div>

    <x-ui.modal name="showModal" :title="__('ui.question_bank_form')" maxWidth="max-w-3xl">
        <form @submit.prevent="submitForm" class="space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="tera-label">{{ __('ui.subjects') }}</label>
                    <select x-model="form.subject_id" class="tera-select" required>
                        <option value="">{{ __('ui.select_subject') }}</option>
                        <template x-for="subject in subjects" :key="subject.id">
                            <option :value="String(subject.id)" x-text="`${subject.name} (${subject.code})`"></option>
                        </template>
                    </select>
                    <p class="mt-1 text-xs text-red-600" x-text="errors.subject_id?.[0]"></p>
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.visibility') }}</label>
                    <select x-model="form.visibility" class="tera-select">
                        <option value="subject_shared">{{ __('ui.subject_shared') }}</option>
                        <option value="private">{{ __('ui.private') }}</option>
                    </select>
                    <p class="mt-1 text-xs text-red-600" x-text="errors.visibility?.[0]"></p>
                </div>
            </div>

            <div>
                <label class="tera-label">{{ __('ui.name') }}</label>
                <input x-model="form.title" class="tera-input" required>
                <p class="mt-1 text-xs text-red-600" x-text="errors.title?.[0]"></p>
            </div>

            <div>
                <label class="tera-label">{{ __('ui.description') }}</label>
                <textarea x-model="form.description" class="tera-textarea" rows="4"></textarea>
                <p class="mt-1 text-xs text-red-600" x-text="errors.description?.[0]"></p>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="tera-btn tera-btn-muted" @click="showModal=false">{{ __('ui.cancel') }}</button>
                <button type="submit" class="tera-btn tera-btn-primary" :disabled="loading">
                    <span x-show="!loading" x-text="isEdit ? @js(__('ui.update_question_bank')) : @js(__('ui.create_question_bank'))"></span>
                    <span x-show="loading" x-cloak>{{ __('ui.saving') }}</span>
                </button>
            </div>
        </form>
    </x-ui.modal>
</div>

<script>
function questionBankCrudPage({ subjects }) {
    return {
        subjects,
        showModal: false,
        loading: false,
        isEdit: false,
        editId: null,
        errors: {},
        form: {
            subject_id: subjects[0] ? String(subjects[0].id) : '',
            visibility: 'subject_shared',
            title: '',
            description: '',
        },

        resetForm() {
            this.errors = {};
            this.form = {
                subject_id: this.subjects[0] ? String(this.subjects[0].id) : '',
                visibility: 'subject_shared',
                title: '',
                description: '',
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
                const res = await fetch(`/question-banks/${id}/edit`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const payload = await res.json();
                if (!res.ok) throw new Error(payload.message || @js(__('ui.failed_to_load_question_bank')));

                this.isEdit = true;
                this.editId = id;
                this.form = {
                    subject_id: payload.data.subject_id ? String(payload.data.subject_id) : '',
                    visibility: payload.data.visibility ?? 'subject_shared',
                    title: payload.data.title ?? '',
                    description: payload.data.description ?? '',
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
            const url = this.isEdit ? `/question-banks/${this.editId}` : '/question-banks';
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
                    throw new Error(payload.message || @js(__('ui.failed_to_save_question_bank')));
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

        async destroyItem(id, title) {
            const confirm = await Swal.fire({
                title: @js(__('ui.delete_question_bank_question')),
                text: `Delete ${title}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                confirmButtonText: @js(__('ui.delete')),
            });
            if (!confirm.isConfirmed) return;

            try {
                const res = await fetch(`/question-banks/${id}`, {
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
                if (!res.ok) throw new Error(payload.message || @js(__('ui.failed_to_delete_question_bank')));
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

