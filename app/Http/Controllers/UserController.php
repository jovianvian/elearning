<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with(['role', 'schoolClass']);

        if ($q = trim((string) $request->string('q'))) {
            $query->where(function ($w) use ($q): void {
                $w->where('full_name', 'like', "%{$q}%")
                    ->orWhere('nis', 'like', "%{$q}%")
                    ->orWhere('nip', 'like', "%{$q}%")
                    ->orWhere('username', 'like', "%{$q}%");
            });
        }

        if ($roleId = $request->integer('role_id')) {
            $query->where('role_id', $roleId);
        }

        if ($classId = $request->integer('class_id')) {
            $query->where('school_class_id', $classId);
        }

        $users = $query->orderBy('full_name')->paginate(12)->withQueryString();
        $roles = Role::where('is_active', true)->orderBy('id')->get();
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();

        return view('users.index', compact('users', 'roles', 'classes'));
    }

    public function create(): View
    {
        $roles = Role::where('is_active', true)->orderBy('id')->get();
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();

        return view('users.create', compact('roles', 'classes'));
    }

    public function store(StoreUserRequest $request): RedirectResponse|JsonResponse
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

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'User created.',
                'data' => $user->load(['role', 'schoolClass']),
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User created.');
    }

    public function edit(Request $request, User $user): View|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'data' => [
                    'id' => $user->id,
                    'role_id' => $user->role_id,
                    'full_name' => $user->full_name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'nis' => $user->nis,
                    'nip' => $user->nip,
                    'school_class_id' => $user->school_class_id,
                    'is_active' => (bool) $user->is_active,
                    'must_change_password' => (bool) $user->must_change_password,
                ],
            ]);
        }

        $roles = Role::where('is_active', true)->orderBy('id')->get();
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();

        return view('users.edit', compact('user', 'roles', 'classes'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse|JsonResponse
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

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'User updated.',
                'data' => $user->fresh()->load(['role', 'schoolClass']),
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse|JsonResponse
    {
        if ($user->id === auth()->id()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Cannot delete current logged in account.',
                ], 422);
            }

            return back()->with('error', 'Cannot delete current logged in account.');
        }

        $user->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'User moved to trash.',
            ]);
        }

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
