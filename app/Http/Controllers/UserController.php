<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with(['role', 'schoolClass'])->latest()->paginate(12);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = Role::where('is_active', true)->orderBy('id')->get();
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();

        return view('users.create', compact('roles', 'classes'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $role = Role::findOrFail($data['role_id']);

        $this->validateRoleSpecificRules($data, $role, null);

        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['must_change_password'] = (bool) ($data['must_change_password'] ?? false);

        $user = User::create($data);

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['preferred_language' => 'id']
        );

        return redirect()->route('users.index')->with('success', 'User created.');
    }

    public function edit(User $user): View
    {
        $roles = Role::where('is_active', true)->orderBy('id')->get();
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();

        return view('users.edit', compact('user', 'roles', 'classes'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $role = Role::findOrFail($data['role_id']);

        $this->validateRoleSpecificRules($data, $role, $user->id);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['must_change_password'] = (bool) ($data['must_change_password'] ?? false);

        $user->update($data);

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['preferred_language' => $user->profile?->preferred_language ?? 'id']
        );

        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete current logged in account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User moved to trash.');
    }

    private function validateRoleSpecificRules(array $data, Role $role, ?int $ignoreUserId): void
    {
        $errors = [];

        if (in_array($role->code, [Role::SUPER_ADMIN, Role::ADMIN, Role::PRINCIPAL], true)) {
            if (empty($data['email'])) {
                $errors['email'][] = 'Email is required for this role.';
            }

            if (empty($data['username'])) {
                $errors['username'][] = 'Username is required for this role.';
            }
        }

        if ($role->code === Role::TEACHER) {
            if (empty($data['email'])) {
                $errors['email'][] = 'Email is required for teacher.';
            }

            if (empty($data['nip']) && empty($data['username'])) {
                $errors['nip'][] = 'Teacher requires NIP or username.';
            }
        }

        if ($role->code === Role::STUDENT) {
            if (empty($data['nis'])) {
                $errors['nis'][] = 'NIS is required for student.';
            }

            if (empty($data['school_class_id'])) {
                $errors['school_class_id'][] = 'Student requires active class assignment.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
