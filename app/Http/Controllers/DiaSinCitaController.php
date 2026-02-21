<?php

namespace App\Http\Controllers;

use App\Models\Consultorio;
use App\Models\DiaSinCita;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DiaSinCitaController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = DiaSinCita::with(['consultorios', 'user'])
            ->whereNull('dias_sin_citas.deleted_at');

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            $owner = $user->hasRole('doctor') ? $user : User::find($ownerId);

            $assignedConsultorios = $owner ? $owner->consultorios()->pluck('consultorios.id')->toArray() : [];
            $createdConsultorios = Consultorio::where('created_by', $ownerId)->pluck('id')->toArray();
            $consultorioIds = array_unique(array_merge($assignedConsultorios, $createdConsultorios));

            $query->whereHas('consultorios', function ($q) use ($consultorioIds) {
                $q->whereIn('consultorios.id', $consultorioIds);
            });
        }

        // Si venimos de una eliminación, evita mostrar el último ID eliminado en la recarga
        if (request()->filled('skip_id')) {
            $query->where('dias_sin_citas.id', '!=', request()->input('skip_id'));
        }

        $diasSinCitas = $query->latest()->paginate(10);

        return view('dias_sin_citas.index', compact('diasSinCitas'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            $owner = $user->hasRole('doctor') ? $user : User::find($ownerId);

            $assignedConsultorios = $owner ? $owner->consultorios()->where('activo', true)->get() : collect();
            $createdConsultorios = Consultorio::where('created_by', $ownerId)->where('activo', true)->get();

            $consultorios = $assignedConsultorios->merge($createdConsultorios)->unique('id')->values();
        } else {
            $consultorios = Consultorio::where('activo', true)->get();
        }

        return view('dias_sin_citas.create', compact('consultorios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'consultorios' => 'required|array|min:1',
            'consultorios.*' => 'exists:consultorios,id',
            'motivo' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'todo_el_dia' => 'boolean',
            'hora_inicio' => 'nullable|required_if:todo_el_dia,false|date_format:H:i',
            'hora_fin' => 'nullable|required_if:todo_el_dia,false|date_format:H:i|after:hora_inicio',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Security check: ensure consultorios belong to the user's scope
        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            $owner = $user->hasRole('doctor') ? $user : User::find($ownerId);

            $assignedConsultorios = $owner ? $owner->consultorios()->pluck('consultorios.id')->toArray() : [];
            $createdConsultorios = Consultorio::where('created_by', $ownerId)->pluck('id')->toArray();
            $allowedConsultorios = array_unique(array_merge($assignedConsultorios, $createdConsultorios));

            $validConsultorios = Consultorio::whereIn('id', $request->consultorios)
                ->whereIn('id', $allowedConsultorios)
                ->count();

            if ($validConsultorios !== count($request->consultorios)) {
                abort(403, 'No tiene permiso para asignar estos consultorios.');
            }
        }

        $diaSinCita = DiaSinCita::create([
            'user_id' => Auth::id(),
            'motivo' => $request->motivo,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'hora_inicio' => $request->boolean('todo_el_dia') ? null : $request->hora_inicio,
            'hora_fin' => $request->boolean('todo_el_dia') ? null : $request->hora_fin,
            'todo_el_dia' => $request->boolean('todo_el_dia'),
        ]);

        $diaSinCita->consultorios()->sync($request->consultorios);

        return redirect()->route('dias-sin-citas.index')->with('success', 'Día sin citas registrado exitosamente.');
    }

    public function destroy(DiaSinCita $diaSinCita)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $param = request()->route('dias_sin_cita');
            $routeId = is_object($param) && method_exists($param, 'getKey') ? $param->getKey() : (is_numeric($param) ? (int) $param : null);
            $id = $diaSinCita->getKey() ?? $routeId;
            $conn = $diaSinCita->getConnectionName();
            Log::warning('DiaSinCita DELETE attempt', [
                'user_id' => $user->id,
                'roles' => $user->getRoleNames()->toArray(),
                'model_id' => $diaSinCita->getKey(),
                'route_id' => $routeId,
                'final_id' => $id,
                'conn' => $conn,
            ]);

            if ($user->hasRole('doctor')) {
                // Borrado definitivo para evitar residuos que sigan mostrando el registro
                \Illuminate\Support\Facades\DB::table('consultorio_dia_sin_cita')
                    ->where('dia_sin_cita_id', $id)->delete();

                // 1) Intento por Eloquent en la misma conexión del modelo
                $deleted = $id ? \App\Models\DiaSinCita::on($conn)->where('id', $id)->delete() : 0;
                // 2) Fallback a DB directa si el borrado por Eloquent no afectó filas
                if ($deleted < 1) {
                    $deleted = $id ? \Illuminate\Support\Facades\DB::connection($conn)
                        ->table('dias_sin_citas')->where('id', $id)->delete() : 0;
                }
                if ($deleted < 1) {
                    $exists = $id ? \Illuminate\Support\Facades\DB::connection($conn)
                        ->table('dias_sin_citas')->where('id', $id)->exists() : null;

                    return redirect()->route('dias-sin-citas.index')
                        ->with('error', 'No se pudo eliminar el registro en la base de datos (ID: '.$id.', existe='.$exists.').');
                }
                // Verificación y limpieza forzada en caso de que el borrado del modelo no haya surtido efecto
                if ($id && \App\Models\DiaSinCita::on($conn)->withTrashed()->find($id)) {
                    \Illuminate\Support\Facades\DB::table('consultorio_dia_sin_cita')
                        ->where('dia_sin_cita_id', $id)->delete();
                    \Illuminate\Support\Facades\DB::table('dias_sin_citas')
                        ->where('id', $id)->delete();
                }

                return redirect()->route('dias-sin-citas.index', ['skip_id' => $id, 'r' => microtime(true)])
                    ->with('success', 'Registro eliminado exitosamente.')
                    ->with('last_deleted_id', $id);
            }

            if ($user->hasRole(['asistente', 'secretaria'])) {
                $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
                $owner = $user->hasRole('doctor') ? $user : User::find($ownerId);

                // Permitir borrar si lo creó el propio doctor o alguien de su equipo (created_by = doctor)
                if ($diaSinCita->user_id == $ownerId || optional($diaSinCita->user)->created_by == $ownerId) {
                    \Illuminate\Support\Facades\DB::table('consultorio_dia_sin_cita')
                        ->where('dia_sin_cita_id', $id)->delete();
                    $deleted = $id ? \App\Models\DiaSinCita::on($conn)->where('id', $id)->delete() : 0;
                    if ($deleted < 1) {
                        $deleted = $id ? \Illuminate\Support\Facades\DB::connection($conn)
                            ->table('dias_sin_citas')->where('id', $id)->delete() : 0;
                    }
                    if ($deleted < 1) {
                        $exists = $id ? \Illuminate\Support\Facades\DB::connection($conn)
                            ->table('dias_sin_citas')->where('id', $id)->exists() : null;

                        return redirect()->route('dias-sin-citas.index')
                            ->with('error', 'No se pudo eliminar el registro en la base de datos (ID: '.$id.', existe='.$exists.').');
                    }
                    if ($id && \App\Models\DiaSinCita::on($conn)->withTrashed()->find($id)) {
                        \Illuminate\Support\Facades\DB::table('consultorio_dia_sin_cita')
                            ->where('dia_sin_cita_id', $id)->delete();
                        \Illuminate\Support\Facades\DB::table('dias_sin_citas')
                            ->where('id', $id)->delete();
                    }

                    return redirect()->route('dias-sin-citas.index', ['skip_id' => $id, 'r' => microtime(true)])
                        ->with('success', 'Registro eliminado exitosamente.')
                        ->with('last_deleted_id', $id);
                }

                $assignedConsultorios = $owner ? $owner->consultorios()->pluck('consultorios.id')->toArray() : [];
                $createdConsultorios = Consultorio::where('created_by', $ownerId)->pluck('id')->toArray();
                $consultorioIds = array_unique(array_merge($assignedConsultorios, $createdConsultorios));

                $affectsDoctorConsultorios = $diaSinCita->consultorios()
                    ->whereIn('consultorios.id', $consultorioIds)
                    ->exists();

                if (! $affectsDoctorConsultorios) {
                    abort(403, 'No tienes permiso para eliminar este registro.');
                }
            }

            \Illuminate\Support\Facades\DB::table('consultorio_dia_sin_cita')
                ->where('dia_sin_cita_id', $id)->delete();
            $deleted = $id ? \App\Models\DiaSinCita::on($conn)->where('id', $id)->delete() : 0;
            if ($deleted < 1) {
                $deleted = $id ? \Illuminate\Support\Facades\DB::connection($conn)
                    ->table('dias_sin_citas')->where('id', $id)->delete() : 0;
            }
            if ($deleted < 1) {
                $exists = $id ? \Illuminate\Support\Facades\DB::connection($conn)
                    ->table('dias_sin_citas')->where('id', $id)->exists() : null;

                return redirect()->route('dias-sin-citas.index')
                    ->with('error', 'No se pudo eliminar el registro en la base de datos (ID: '.$id.', existe='.$exists.').');
            }
            if ($id && \App\Models\DiaSinCita::on($conn)->withTrashed()->find($id)) {
                \Illuminate\Support\Facades\DB::table('consultorio_dia_sin_cita')
                    ->where('dia_sin_cita_id', $id)->delete();
                \Illuminate\Support\Facades\DB::table('dias_sin_citas')
                    ->where('id', $id)->delete();
            }

            return redirect()->route('dias-sin-citas.index', ['skip_id' => $id, 'r' => microtime(true)])
                ->with('success', 'Registro eliminado exitosamente.')
                ->with('last_deleted_id', $id);
        } catch (\Throwable $e) {
            return redirect()->route('dias-sin-citas.index')->with('error', 'Error al eliminar el registro.');
        }
    }
}
