<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:root']);
    }

    public function index(Request $request)
    {
        $category = $request->input('category');

        if ($category && $modelClass = $this->modelForCategory($category)) {
            $query = $modelClass::with('user');

            $this->applyFilters($query, $request);

            $logs = $query->latest('created_at')
                ->paginate(20)
                ->withQueryString();

            $sections = $modelClass::query()
                ->whereNotNull('section')
                ->distinct()
                ->orderBy('section')
                ->pluck('section');
        } else {
            $logs = $this->buildUnionQuery($request);
            $sections = AuditLog::allDistinctSections();
        }

        $categories = AuditLog::categories();

        return view('admin.audit.index', compact('logs', 'sections', 'categories'));
    }

    protected function applyFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('section')) {
            $query->where('section', $request->input('section'));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }
    }

    /**
     * Build a paginated UNION query across all audit category tables.
     */
    protected function buildUnionQuery(Request $request)
    {
        $tables = [];
        foreach (AuditLog::categories() as $cat) {
            $tables[] = $cat['table'];
        }

        $allQueries = [];

        foreach ($tables as $table) {
            $q = DB::table($table)
                ->select(
                    "{$table}.id",
                    "{$table}.user_id",
                    "{$table}.action",
                    "{$table}.section",
                    "{$table}.model_type",
                    "{$table}.model_id",
                    "{$table}.payload",
                    "{$table}.ip_address",
                    "{$table}.user_agent",
                    "{$table}.created_at",
                    DB::raw("'{$table}' as audit_table"),
                )
                ->leftJoin('users', 'users.id', '=', "{$table}.user_id")
                ->addSelect('users.name as user_name', 'users.email as user_email');

            if ($request->filled('search')) {
                $search = $request->input('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('users.name', 'like', "%{$search}%")
                        ->orWhere('users.email', 'like', "%{$search}%");
                });
            }

            if ($request->filled('section')) {
                $q->where("{$table}.section", $request->input('section'));
            }

            if ($request->filled('date')) {
                $q->whereDate("{$table}.created_at", $request->input('date'));
            }

            $allQueries[] = $q;
        }

        $first = array_shift($allQueries);

        foreach ($allQueries as $q) {
            $first->unionAll($q);
        }

        $sql = $first->toSql();

        // Collect bindings from ALL queries (unionAll does not merge them)
        $allBindings = $first->getBindings();
        foreach ($allQueries as $q) {
            $allBindings = array_merge($allBindings, $q->getBindings());
        }

        $wrapper = DB::table(DB::raw("({$sql}) as audit_union"));

        foreach ($allBindings as $binding) {
            $wrapper->addBinding($binding, 'from');
        }

        return $wrapper
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * Resolve the Eloquent model class for a given category key.
     */
    protected function modelForCategory(string $category): ?string
    {
        $categories = AuditLog::categories();

        return $categories[$category]['model'] ?? null;
    }
}
