@extends('layouts.app', ['title' => 'Users'])

@section('content')
<div
    x-data="userCrudPage({
        roles: @js($roles->map(fn($r) => ['id' => $r->id, 'name' => $r->name])->values()),
        classes: @js($classes->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values())
    })"
>
    <x-ui.page-header title="User Management" subtitle="Manage Super Admin, Admin, Principal, Teacher, and Student accounts.">
        <x-slot:actions>
            <button type="button" class="tera-btn tera-btn-primary" @click="openCreate">
                <i data-lucide="user-plus" class="w-4 h-4"></i>{{ __('ui.add_user') }}
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="tera-table-wrap">
        <table class="tera-table">
            <thead>
                <tr>
                    <th class="text-left">{{ __('ui.name') }}</th>
                    <th class="text-left">{{ __('ui.role') }}</th>
                    <th class="text-center">NIS</th>
                    <th class="text-center">NIP</th>
                    <th class="text-left">{{ __('ui.email') }}</th>
                    <th class="text-center">{{ __('ui.status') }}</th>
                    <th class="text-right">{{ __('ui.action') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr id="user-row-{{ $user->id }}">
                    <td>
                        <p class="font-semibold text-slate-800">{{ $user->full_name }}</p>
                        <p class="text-xs text-slate-500">{{ $user->username }}</p>
                    </td>
                    <td>{{ $user->role?->name }}</td>
                    <td class="text-center">{{ $user->nis ?: '-' }}</td>
                    <td class="text-center">{{ $user->nip ?: '-' }}</td>
                    <td>{{ $user->email ?: '-' }}</td>
                    <td class="text-center">
                        <span class="tera-badge {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                            {{ $user->is_active ? __('ui.active') : __('ui.inactive') }}
                        </span>
                    </td>
                    <td class="text-right">
                        <div class="inline-flex items-center gap-2">
                            <button type="button" class="tera-btn tera-btn-muted !px-3 !py-1.5" @click="openEdit({{ $user->id }})">{{ __('ui.edit') }}</button>
                            <button type="button" class="tera-btn tera-btn-danger !px-3 !py-1.5" @click="destroyUser({{ $user->id }}, @js($user->full_name))">{{ __('ui.delete') }}</button>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>

    <x-ui.modal name="showModal" :title="__('ui.user_form')" maxWidth="max-w-4xl">
        <form @submit.prevent="submitForm" class="space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="tera-label">{{ __('ui.role') }}</label>
                    <select x-model.number="form.role_id" class="tera-select" required>
                        <template x-for="role in roles" :key="role.id">
                            <option :value="role.id" x-text="role.name"></option>
                        </template>
                    </select>
                    <p class="mt-1 text-xs text-red-600" x-text="errors.role_id?.[0]"></p>
                </div>

                <div>
                    <label class="tera-label">{{ __('ui.full_name') }}</label>
                    <input x-model="form.full_name" class="tera-input" required>
                    <p class="mt-1 text-xs text-red-600" x-text="errors.full_name?.[0]"></p>
                </div>

                <div>
                    <label class="tera-label">{{ __('ui.username') }}</label>
                    <input x-model="form.username" class="tera-input">
                    <p class="mt-1 text-xs text-red-600" x-text="errors.username?.[0]"></p>
                </div>

                <div>
                    <label class="tera-label">{{ __('ui.email') }}</label>
                    <input type="email" x-model="form.email" class="tera-input">
                    <p class="mt-1 text-xs text-red-600" x-text="errors.email?.[0]"></p>
                </div>

                <div>
                    <label class="tera-label">NIS</label>
                    <input x-model="form.nis" class="tera-input">
                    <p class="mt-1 text-xs text-red-600" x-text="errors.nis?.[0]"></p>
                </div>

                <div>
                    <label class="tera-label">NIP</label>
                    <input x-model="form.nip" class="tera-input">
                    <p class="mt-1 text-xs text-red-600" x-text="errors.nip?.[0]"></p>
                </div>

                <div>
                    <label class="tera-label">{{ __('ui.classes') }} (student)</label>
                    <select x-model="form.school_class_id" class="tera-select">
                        <option value="">-</option>
                        <template x-for="klass in classes" :key="klass.id">
                            <option :value="String(klass.id)" x-text="klass.name"></option>
                        </template>
                    </select>
                    <p class="mt-1 text-xs text-red-600" x-text="errors.school_class_id?.[0]"></p>
                </div>

                <div>
                    <label class="tera-label">{{ __('ui.active') }}</label>
                    <select x-model="form.is_active" class="tera-select">
                        <option value="1">{{ __('ui.yes') }}</option>
                        <option value="0">{{ __('ui.no') }}</option>
                    </select>
                </div>

                <div>
                    <label class="tera-label">Must Change Password</label>
                    <select x-model="form.must_change_password" class="tera-select">
                        <option value="0">{{ __('ui.no') }}</option>
                        <option value="1">{{ __('ui.yes') }}</option>
                    </select>
                </div>

                <div>
                    <label class="tera-label" x-text="isEdit ? 'Password (optional)' : 'Password'"></label>
                    <input type="password" x-model="form.password" class="tera-input" :required="!isEdit">
                    <p class="mt-1 text-xs text-red-600" x-text="errors.password?.[0]"></p>
                </div>

                <div>
                    <label class="tera-label">Confirm Password</label>
                    <input type="password" x-model="form.password_confirmation" class="tera-input" :required="!isEdit">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" class="tera-btn tera-btn-muted" @click="closeModal">{{ __('ui.cancel') }}</button>
                <button type="submit" class="tera-btn tera-btn-primary" :disabled="loading">
                    <span x-show="!loading" x-text="isEdit ? @js(__('ui.update_user')) : @js(__('ui.create_user'))"></span>
                    <span x-show="loading" x-cloak>{{ __('ui.saving') }}</span>
                </button>
            </div>
        </form>
    </x-ui.modal>
</div>

<script>
function userCrudPage({ roles, classes }) {
    return {
        roles,
        classes,
        showModal: false,
        loading: false,
        isEdit: false,
        editId: null,
        errors: {},
        form: {
            role_id: roles[0]?.id ?? '',
            full_name: '',
            username: '',
            email: '',
            nis: '',
            nip: '',
            school_class_id: '',
            is_active: '1',
            must_change_password: '0',
            password: '',
            password_confirmation: '',
        },

        resetForm() {
            this.errors = {};
            this.form = {
                role_id: this.roles[0]?.id ?? '',
                full_name: '',
                username: '',
                email: '',
                nis: '',
                nip: '',
                school_class_id: '',
                is_active: '1',
                must_change_password: '0',
                password: '',
                password_confirmation: '',
            };
        },

        openCreate() {
            this.isEdit = false;
            this.editId = null;
            this.resetForm();
            this.showModal = true;
        },

        async openEdit(userId) {
            this.loading = true;
            this.errors = {};
            try {
                const res = await fetch(`/users/${userId}/edit`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const payload = await res.json();
                if (!res.ok) throw new Error(payload.message || @js(__('ui.failed_to_load_user')));

                this.isEdit = true;
                this.editId = userId;
                this.form = {
                    role_id: payload.data.role_id ?? this.roles[0]?.id ?? '',
                    full_name: payload.data.full_name ?? '',
                    username: payload.data.username ?? '',
                    email: payload.data.email ?? '',
                    nis: payload.data.nis ?? '',
                    nip: payload.data.nip ?? '',
                    school_class_id: payload.data.school_class_id ? String(payload.data.school_class_id) : '',
                    is_active: payload.data.is_active ? '1' : '0',
                    must_change_password: payload.data.must_change_password ? '1' : '0',
                    password: '',
                    password_confirmation: '',
                };
                this.showModal = true;
            } catch (error) {
                Swal.fire({ icon: 'error', title: @js(__('ui.error')), text: error.message || @js(__('ui.failed_to_load_user')) });
            } finally {
                this.loading = false;
            }
        },

        closeModal() {
            if (this.loading) return;
            this.showModal = false;
        },

        async submitForm() {
            this.loading = true;
            this.errors = {};

            const url = this.isEdit ? `/users/${this.editId}` : '/users';
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

                    throw new Error(payload.message || @js(__('ui.failed_to_save_user')));
                }

                this.showModal = false;
                Swal.fire({
                    icon: 'success',
                    title: @js(__('ui.success')),
                    text: payload.message || @js(__('ui.save')),
                    timer: 1300,
                    showConfirmButton: false,
                }).then(() => window.location.reload());
            } catch (error) {
                Swal.fire({ icon: 'error', title: @js(__('ui.error')), text: error.message || @js(__('ui.failed_to_save_user')) });
            } finally {
                this.loading = false;
            }
        },

        async destroyUser(userId, name) {
            const confirm = await Swal.fire({
                title: @js(__('ui.delete_user_question')),
                text: `Delete ${name}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                confirmButtonText: @js(__('ui.delete')),
                cancelButtonText: @js(__('ui.cancel')),
            });

            if (!confirm.isConfirmed) return;

            try {
                const res = await fetch(`/users/${userId}`, {
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
                if (!res.ok) throw new Error(payload.message || @js(__('ui.failed_to_delete_user')));

                Swal.fire({
                    icon: 'success',
                    title: @js(__('ui.deleted')),
                    text: payload.message || @js(__('ui.deleted')),
                    timer: 1200,
                    showConfirmButton: false,
                }).then(() => window.location.reload());
            } catch (error) {
                Swal.fire({ icon: 'error', title: @js(__('ui.error')), text: error.message || @js(__('ui.failed_to_delete_user')) });
            }
        },
    };
}
</script>
@endsection

