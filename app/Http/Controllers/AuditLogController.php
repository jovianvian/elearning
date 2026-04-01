<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->hasRole(Role::SUPER_ADMIN), 403);

        $query = ActivityLog::query()->with('user');

        if ($q = trim((string) $request->string('q'))) {
            $query->where(function ($w) use ($q): void {
                $w->where('action', 'like', "%{$q}%")
                    ->orWhere('entity_type', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('full_name', 'like', "%{$q}%"));
            });
        }

        if ($entityType = trim((string) $request->string('entity_type'))) {
            $query->where('entity_type', $entityType);
        }

        $logs = $query->latest()->paginate(25)->withQueryString();
        $entityTypes = ActivityLog::query()
            ->select('entity_type')
            ->whereNotNull('entity_type')
            ->distinct()
            ->orderBy('entity_type')
            ->pluck('entity_type');

        return view('monitoring.audit-logs', compact('logs', 'entityTypes'));
    }
}
