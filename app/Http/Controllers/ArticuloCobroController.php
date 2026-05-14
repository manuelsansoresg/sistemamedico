<?php

namespace App\Http\Controllers;

use App\Models\ArticuloCobro;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ArticuloCobroController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = ArticuloCobro::with('doctor')->latest();

        if (! $user->hasRole('root')) {
            $query->where('doctor_id', $this->ownerDoctorId($user));
        }

        $articulos = $query->paginate(15);

        return view('admin.articulos_cobro.index', compact('articulos'));
    }

    public function create()
    {
        return view('admin.articulos_cobro.create');
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $doctorId = $this->ownerDoctorId($user);
        $validated = $this->validated($request, $doctorId);

        ArticuloCobro::create($validated + [
            'doctor_id' => $doctorId,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return redirect()->route('articulos-cobro.index')->with('success', __('cobros.messages.article_created'));
    }

    public function edit(ArticuloCobro $articulosCobro)
    {
        $this->authorizeArticleAccess($articulosCobro);

        return view('admin.articulos_cobro.edit', ['articulo' => $articulosCobro]);
    }

    public function update(Request $request, ArticuloCobro $articulosCobro)
    {
        $this->authorizeArticleAccess($articulosCobro);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $articulosCobro->update($this->validated($request, $this->ownerDoctorId($user), $articulosCobro) + ['updated_by' => $user->id]);

        return redirect()->route('articulos-cobro.index')->with('success', __('cobros.messages.article_updated'));
    }

    public function destroy(ArticuloCobro $articulosCobro)
    {
        $this->authorizeArticleAccess($articulosCobro);
        $articulosCobro->delete();

        return redirect()->route('articulos-cobro.index')->with('success', __('cobros.messages.article_deleted'));
    }

    private function validated(Request $request, int $doctorId, ?ArticuloCobro $articulo = null): array
    {
        $request->merge([
            'nombre' => trim((string) $request->input('nombre')),
        ]);

        return $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('articulos_cobro', 'nombre')
                    ->where(fn ($query) => $query->where('doctor_id', $doctorId))
                    ->ignore($articulo?->id),
            ],
            'descripcion' => ['nullable', 'string'],
            'unidad' => ['nullable', 'string', 'max:50'],
            'precio' => ['required', 'numeric', 'min:0'],
            'activo' => ['nullable', 'boolean'],
        ]) + ['activo' => $request->boolean('activo', true)];
    }

    private function ownerDoctorId(User $user): int
    {
        if ($user->hasRole('doctor')) {
            return $user->id;
        }

        return (int) ($user->created_by ?: $user->id);
    }

    private function authorizeArticleAccess(ArticuloCobro $articulo): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('root') || (int) $articulo->doctor_id === $this->ownerDoctorId($user)) {
            return;
        }

        abort(403);
    }
}
