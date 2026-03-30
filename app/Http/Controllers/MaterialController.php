<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use App\Models\Material;
use App\Models\Role;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function index(): View
    {
        $query = Material::with(['subject.schoolClass', 'creator'])->latest();

        if (auth()->user()->hasRole(Role::GURU)) {
            $query->where('created_by', auth()->id());
        }

        if (auth()->user()->hasRole(Role::SISWA)) {
            $query->whereNotNull('published_at');

            if (auth()->user()->school_class_id) {
                $query->whereHas('subject', function ($q): void {
                    $q->where('school_class_id', auth()->user()->school_class_id);
                });
            }
        }

        $materials = $query->paginate(10);

        return view('materials.index', compact('materials'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->hasRole(Role::ADMIN, Role::GURU), 403);

        $subjects = Subject::with('schoolClass')
            ->when(auth()->user()->hasRole(Role::GURU), fn ($q) => $q->where('teacher_id', auth()->id()))
            ->orderBy('name')
            ->get();

        return view('materials.create', compact('subjects'));
    }

    public function store(StoreMaterialRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(Role::ADMIN, Role::GURU), 403);

        $data = $request->validated();
        $this->assertSubjectOwnershipForGuru((int) $data['subject_id']);
        $data['created_by'] = auth()->id();

        Material::create($data);

        return redirect()->route('materials.index')->with('success', 'Materi berhasil ditambahkan.');
    }

    public function show(Material $material): View
    {
        $this->authorizeRead($material);

        $material->load(['subject.schoolClass', 'creator']);

        return view('materials.show', compact('material'));
    }

    public function edit(Material $material): View
    {
        $this->authorizeWrite($material);

        $subjects = Subject::with('schoolClass')
            ->when(auth()->user()->hasRole(Role::GURU), fn ($q) => $q->where('teacher_id', auth()->id()))
            ->orderBy('name')
            ->get();

        return view('materials.edit', compact('material', 'subjects'));
    }

    public function update(UpdateMaterialRequest $request, Material $material): RedirectResponse
    {
        $this->authorizeWrite($material);

        $data = $request->validated();
        $this->assertSubjectOwnershipForGuru((int) $data['subject_id']);
        $material->update($data);

        return redirect()->route('materials.index')->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Material $material): RedirectResponse
    {
        $this->authorizeWrite($material);

        $material->delete();

        return redirect()->route('materials.index')->with('success', 'Materi berhasil dihapus.');
    }

    private function authorizeWrite(Material $material): void
    {
        if (auth()->user()->hasRole(Role::ADMIN)) {
            return;
        }

        abort_if(! auth()->user()->hasRole(Role::GURU), 403, 'Akses ditolak.');
        abort_if($material->created_by !== auth()->id(), 403, 'Hanya bisa mengubah materi milik sendiri.');
    }

    private function authorizeRead(Material $material): void
    {
        if (auth()->user()->hasRole(Role::ADMIN)) {
            return;
        }

        if (auth()->user()->hasRole(Role::GURU)) {
            abort_if($material->created_by !== auth()->id(), 403, 'Hanya bisa melihat materi milik sendiri.');

            return;
        }

        abort_if(! auth()->user()->hasRole(Role::SISWA), 403, 'Akses ditolak.');

        if (auth()->user()->school_class_id !== null) {
            abort_if($material->subject->school_class_id !== auth()->user()->school_class_id, 403, 'Materi tidak tersedia untuk kelas kamu.');
        }

        abort_if($material->published_at === null, 403, 'Materi belum dipublikasikan.');
    }

    private function assertSubjectOwnershipForGuru(int $subjectId): void
    {
        if (! auth()->user()->hasRole(Role::GURU)) {
            return;
        }

        $allowed = Subject::where('id', $subjectId)
            ->where('teacher_id', auth()->id())
            ->exists();

        abort_if(! $allowed, 403, 'Guru hanya bisa membuat materi untuk mata pelajaran yang diampu.');
    }
}
