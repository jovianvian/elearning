@csrf
<div class="grid md:grid-cols-2 gap-4">
    <div><label class="tera-label">Role</label><select name="role_id" class="tera-select" required>@foreach($roles as $role)<option value="{{ $role->id }}" @selected(old('role_id', $user->role_id ?? '')==$role->id)>{{ $role->name }}</option>@endforeach</select></div>
    <div><label class="tera-label">Full Name</label><input name="full_name" class="tera-input" value="{{ old('full_name', $user->full_name ?? '') }}" required></div>
    <div><label class="tera-label">Username</label><input name="username" class="tera-input" value="{{ old('username', $user->username ?? '') }}"></div>
    <div><label class="tera-label">Email</label><input name="email" type="email" class="tera-input" value="{{ old('email', $user->email ?? '') }}"></div>
    <div><label class="tera-label">NIS</label><input name="nis" class="tera-input" value="{{ old('nis', $user->nis ?? '') }}"></div>
    <div><label class="tera-label">NIP</label><input name="nip" class="tera-input" value="{{ old('nip', $user->nip ?? '') }}"></div>
    <div><label class="tera-label">Class (student)</label><select name="school_class_id" class="tera-select"><option value="">-</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected(old('school_class_id', $user->school_class_id ?? '')==$class->id)>{{ $class->name }}</option>@endforeach</select></div>
    <div><label class="tera-label">Active</label><select name="is_active" class="tera-select"><option value="1" @selected(old('is_active', $user->is_active ?? true))>Yes</option><option value="0" @selected(!old('is_active', $user->is_active ?? true))>No</option></select></div>
    <div><label class="tera-label">Must Change Password</label><select name="must_change_password" class="tera-select"><option value="0" @selected(!old('must_change_password', $user->must_change_password ?? false))>No</option><option value="1" @selected(old('must_change_password', $user->must_change_password ?? false))>Yes</option></select></div>
    <div><label class="tera-label">Password {{ isset($user) ? '(optional)' : '' }}</label><input type="password" name="password" class="tera-input" {{ isset($user) ? '' : 'required' }}></div>
    <div><label class="tera-label">Confirm Password</label><input type="password" name="password_confirmation" class="tera-input" {{ isset($user) ? '' : 'required' }}></div>
</div>
