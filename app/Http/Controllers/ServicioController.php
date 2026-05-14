<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ServicioController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = Servicio::query();

        if (! $user->hasRole('root')) {
            $query->where('created_by', $this->ownerDoctorId($user));
        }

        $servicios = $query->paginate(15);

        return view('admin.servicios.index', compact('servicios'));
    }

    public function create()
    {
        return view('admin.servicios.create');
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $doctorId = $this->ownerDoctorId($user);
        $validated = $this->validated($request, $doctorId);

        Servicio::create($validated + ['created_by' => $doctorId]);

        return redirect()->route('servicios.index')->with('success', __('servicios.messages.created_success'));
    }

    public function edit(Servicio $servicio)
    {
        $this->authorizeServicioAccess($servicio);

        return view('admin.servicios.edit', compact('servicio'));
    }

    public function update(Request $request, Servicio $servicio)
    {
        $this->authorizeServicioAccess($servicio);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $validated = $this->validated($request, $this->ownerDoctorId($user), $servicio);

        $servicio->update($validated);

        return redirect()->route('servicios.index')->with('success', __('servicios.messages.updated_success'));
    }

    public function destroy(Servicio $servicio)
    {
        $this->authorizeServicioAccess($servicio);

        $servicio->delete();

        return redirect()->route('servicios.index')->with('success', __('servicios.messages.deleted_success'));
    }

    private function authorizeServicioAccess(Servicio $servicio): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('root') || (int) $servicio->created_by === $this->ownerDoctorId($user)) {
            return;
        }

        abort(403);
    }

    private function validated(Request $request, int $doctorId, ?Servicio $servicio = null): array
    {
        $request->merge([
            'nombre' => trim((string) $request->input('nombre')),
        ]);

        return $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('servicios', 'nombre')
                    ->where(fn ($query) => $query->where('created_by', $doctorId))
                    ->ignore($servicio?->id),
            ],
            'duracion' => ['required', 'integer', 'min:1'],
            'costo' => ['required', 'numeric', 'min:0'],
        ]);
    }

    private function ownerDoctorId(User $user): int
    {
        if ($user->hasRole('doctor')) {
            return $user->id;
        }

        return (int) ($user->created_by ?: $user->id);
    }
}
