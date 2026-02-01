<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Consulta;
use App\Models\ConsultaValor;
use App\Models\Estudio;
use App\Models\Plantilla;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultaController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create($cita_id)
    {
        $cita = Cita::with(['paciente', 'doctor', 'consultorio'])->findOrFail($cita_id);
        
        // Security check: only doctor of the appointment or root can start it
        if (auth()->user()->hasRole('doctor') && $cita->doctor_id !== auth()->id()) {
            abort(403, 'No tiene permiso para iniciar esta consulta.');
        }

        // Get Plantillas (Templates)
        // Doctor can see own plantillas or global ones (if any logic for shared templates exists)
        // For now, let's show doctor's own plantillas.
        $plantillas = Plantilla::where('user_id', auth()->id())->get();
        
        // If no templates, maybe show a warning or a default one?
        // Assuming there is at least one or the user can create one.
        
        $paciente = $cita->paciente;
        
        // Previous consultations history for this patient
        $historialConsultas = Consulta::where('paciente_id', $paciente->id)
            ->with(['doctor', 'plantilla'])
            ->latest()
            ->get();
            
        // Studies history
        $historialEstudios = Estudio::whereHas('consulta', function($q) use ($paciente) {
            $q->where('paciente_id', $paciente->id);
        })->with(['consulta.doctor'])->latest()->get();

        return view('admin.consultas.create', compact('cita', 'paciente', 'plantillas', 'historialConsultas', 'historialEstudios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cita_id' => 'required|exists:citas,id',
            'plantilla_id' => 'required|exists:plantillas,id',
            'peso' => 'nullable|numeric',
            'estatura' => 'nullable|numeric',
            'alergias' => 'nullable|string',
            'valores' => 'nullable|array',
        ]);

        $cita = Cita::findOrFail($request->cita_id);
        $paciente = User::findOrFail($cita->paciente_id);

        DB::beginTransaction();

        try {
            // 1. Create Consulta
            $consulta = Consulta::create([
                'cita_id' => $cita->id,
                'doctor_id' => auth()->id(), // Assuming logged in user is the doctor
                'paciente_id' => $paciente->id,
                'plantilla_id' => $request->plantilla_id,
                'peso' => $request->peso,
                'estatura' => $request->estatura,
                'alergias' => $request->alergias,
            ]);

            // 2. Save Dynamic Values
            if ($request->has('valores')) {
                foreach ($request->valores as $campoId => $valor) {
                    ConsultaValor::create([
                        'consulta_id' => $consulta->id,
                        'plantilla_campo_id' => $campoId,
                        'valor' => $valor,
                    ]);
                }
            }

            // 3. Update Patient Profile (Current Health Data)
            $paciente->update([
                'peso' => $request->peso,
                'estatura' => $request->estatura,
                'alergias' => $request->alergias,
            ]);

            // 4. Update Appointment Status
            $cita->update(['estado' => 'completada']); // Or 'en_progreso' if needed, but usually 'completed' after saving. 
            // Actually, maybe keep it open until they leave? But saving usually means done or progress.
            // Let's assume saving = saved record, status update optional or handled elsewhere? 
            // User didn't specify, but "Iniciar" implies flow. Let's mark as completed or create a separate status.
            // For now, let's keep it simple.

            DB::commit();

            return redirect()->route('consultas.create', ['cita_id' => $cita->id])
                ->with('success', 'Consulta guardada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al guardar la consulta: ' . $e->getMessage());
        }
    }

    public function storeEstudio(Request $request, Consulta $consulta)
    {
        $request->validate([
            'orden' => 'required|string',
            'observacion' => 'nullable|string',
        ]);

        Estudio::create([
            'consulta_id' => $consulta->id,
            'orden' => $request->orden,
            'observacion' => $request->observacion,
        ]);

        return back()->with('success', 'Orden de estudio guardada correctamente.');
    }
    
    // Implement PDF methods
    public function print(Consulta $consulta)
    {
        // Load relationships
        $consulta->load(['doctor', 'paciente', 'plantilla', 'valores.campo']);
        
        // Security Check
        if (auth()->user()->hasRole('doctor') && $consulta->doctor_id !== auth()->id()) {
            abort(403);
        }

        $pdf = Pdf::loadView('admin.consultas.pdf', compact('consulta'));
        return $pdf->stream('receta-consulta-' . $consulta->id . '.pdf');
    }
    
    public function printEstudio(Estudio $estudio)
    {
        // Load relationships
        $estudio->load(['consulta.doctor', 'consulta.paciente']);
        
        // Security Check
        if (auth()->user()->hasRole('doctor') && $estudio->consulta->doctor_id !== auth()->id()) {
            abort(403);
        }

        $pdf = Pdf::loadView('admin.consultas.estudio_pdf', compact('estudio'));
        return $pdf->stream('orden-estudio-' . $estudio->id . '.pdf');
    }

    public function destroy(Consulta $consulta)
    {
        // Security Check
        if (auth()->user()->hasRole('doctor') && $consulta->doctor_id !== auth()->id()) {
            abort(403);
        }
        
        $citaId = $consulta->cita_id;
        
        try {
            DB::beginTransaction();
            $consulta->delete();
            DB::commit();
            return redirect()->route('consultas.create', ['cita_id' => $citaId])->with('success', 'Consulta eliminada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar la consulta.');
        }
    }
}
