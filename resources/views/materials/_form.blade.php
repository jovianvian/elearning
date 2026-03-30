@csrf
<div class="mb-3">
    <label class="form-label">Subject</label>
    <select class="form-select" name="subject_id" required>
        <option value="">-- Pilih Subject --</option>
        @foreach($subjects as $subject)
            <option value="{{ $subject->id }}" @selected(old('subject_id', $material->subject_id ?? '') == $subject->id)>
                {{ $subject->name }} - {{ $subject->schoolClass?->name }}
            </option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Judul</label>
    <input type="text" name="title" class="form-control" value="{{ old('title', $material->title ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Konten</label>
    <textarea name="content" rows="6" class="form-control">{{ old('content', $material->content ?? '') }}</textarea>
</div>
<div class="mb-3">
    <label class="form-label">Tanggal Publikasi (opsional)</label>
    <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', isset($material->published_at) && $material->published_at ? $material->published_at->format('Y-m-d\TH:i') : '') }}">
</div>
