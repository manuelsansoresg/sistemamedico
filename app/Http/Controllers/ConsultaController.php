<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Consulta;
use App\Models\ConsultaValor;
use App\Models\Estudio;
use App\Models\EstudioArchivo;
use App\Models\Plantilla;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        $historialEstudios = Estudio::whereHas('consulta', function ($q) use ($paciente) {
            $q->where('paciente_id', $paciente->id);
        })->with(['consulta.doctor'])->latest()->get();

        return view('admin.consultas.create', compact('cita', 'paciente', 'plantillas', 'historialConsultas', 'historialEstudios'));
    }

    /**
     * Display the specified resource (read-only).
     */
    public function show(Consulta $consulta)
    {
        // Assistants/secretaries can view, doctors can view own, root can view all
        $consulta->load([
            'paciente',
            'doctor',
            'cita.consultorio',
            'plantilla',
            'valores.campo',
            'estudios.archivos',
        ]);

        if (auth()->user()->hasRole('doctor') && $consulta->doctor_id !== auth()->id()) {
            abort(403);
        }

        return view('admin.consultas.show', compact('consulta'));
    }

    public function edit(Consulta $consulta)
    {
        // Security Check: assistants/secretaries cannot edit
        if (auth()->user()->hasRole(['asistente', 'secretaria'])) {
            abort(403);
        }
        if (auth()->user()->hasRole('doctor') && $consulta->doctor_id !== auth()->id()) {
            abort(403);
        }

        $consulta->load(['paciente', 'doctor', 'cita.consultorio', 'valores', 'plantilla']);
        $plantillas = Plantilla::where('user_id', auth()->id())->get();

        return view('admin.consultas.edit', compact('consulta', 'plantillas'));
    }

    public function update(Request $request, Consulta $consulta)
    {
        // Security Check
        if (auth()->user()->hasRole(['asistente', 'secretaria'])) {
            abort(403);
        }
        if (auth()->user()->hasRole('doctor') && $consulta->doctor_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'plantilla_id' => 'required|exists:plantillas,id',
            'peso' => 'nullable|numeric',
            'estatura' => 'nullable|numeric',
            'alergias' => 'nullable|string',
            'valores' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            $consulta->update([
                'plantilla_id' => $request->plantilla_id,
                'peso' => $request->peso,
                'estatura' => $request->estatura,
                'alergias' => $request->alergias,
            ]);

            // Update Dynamic Values
            // First, remove old values or update them?
            // Simpler to delete and recreate, or update if exists.
            // Let's delete and recreate for the current template to avoid orphans if template changed or fields removed.
            // But if we want to keep history of other template values?
            // Usually we just replace for the current consultation context.

            ConsultaValor::where('consulta_id', $consulta->id)->delete();

            if ($request->has('valores')) {
                foreach ($request->valores as $campoId => $valor) {
                    ConsultaValor::create([
                        'consulta_id' => $consulta->id,
                        'plantilla_campo_id' => $campoId,
                        'valor' => $valor,
                    ]);
                }
            }

            // Update Patient Profile (Optional, maybe user wants to correct the record but not current profile?
            // Usually editing a recent consultation should update profile if it's the latest data.
            // Let's assume yes.)
            $consulta->paciente->update([
                'peso' => $request->peso,
                'estatura' => $request->estatura,
                'alergias' => $request->alergias,
            ]);

            DB::commit();

            return redirect()->route('consultas.create', ['cita_id' => $consulta->cita_id])
                ->with('success', 'Consulta actualizada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error al actualizar la consulta: '.$e->getMessage());
        }
    }

    public function editEstudio(Estudio $estudio)
    {
        // Security Check
        if (auth()->user()->hasRole('doctor') && $estudio->consulta->doctor_id !== auth()->id()) {
            abort(403);
        }

        $estudio->load('archivos');

        return view('admin.consultas.edit_estudio', compact('estudio'));
    }

    public function updateEstudio(Request $request, Estudio $estudio)
    {
        // Security Check
        if (auth()->user()->hasRole('doctor') && $estudio->consulta->doctor_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'orden' => 'required|string',
            'observacion' => 'nullable|string',
            'archivos.*' => 'nullable|file|max:5120',
            'delete_files' => 'nullable|array',
        ]);

        $estudio->update([
            'orden' => $request->orden,
            'observacion' => $request->observacion,
        ]);

        // Handle File Deletion
        if ($request->has('delete_files')) {
            foreach ($request->delete_files as $archivoId) {
                $archivo = EstudioArchivo::find($archivoId);
                if ($archivo && $archivo->estudio_id == $estudio->id) {
                    $this->deleteEstudioFileFromPublic($archivo->path);
                    $archivo->delete();
                }
            }
        }

        // Handle New Files (guardar en /public)
        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $archivo) {
                $originalName = $archivo->getClientOriginalName();
                $mimeType = $archivo->getMimeType();
                $size = $archivo->getSize();

                $relativePath = $this->storeEstudioFileToPublic($archivo, $estudio);

                EstudioArchivo::create([
                    'estudio_id' => $estudio->id,
                    'path' => $relativePath,
                    'nombre_original' => $originalName,
                    'mime_type' => $mimeType,
                    'size' => $size,
                ]);
            }
        }

        return back()->with('success', 'Orden de estudio actualizada correctamente.');
    }

    public function destroyEstudio(Estudio $estudio)
    {
        // Security Check
        if (auth()->user()->hasRole('doctor') && $estudio->consulta->doctor_id !== auth()->id()) {
            abort(403);
        }

        $citaId = $estudio->consulta->cita_id;

        try {
            // Delete files first
            foreach ($estudio->archivos as $archivo) {
                $this->deleteEstudioFileFromPublic($archivo->path);
                $archivo->delete();
            }

            $estudio->delete();

            return redirect()->route('consultas.create', ['cita_id' => $citaId])->with('success', 'Orden de estudio eliminada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar la orden de estudio.');
        }
    }

    public function uploadEstudioFile(Request $request, Estudio $estudio)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:10240', // 10MB max
        ]);

        $uploadedCount = 0;

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $originalName = $file->getClientOriginalName();
                $mimeType = $file->getMimeType();
                $size = $file->getSize();

                $relativePath = $this->storeEstudioFileToPublic($file, $estudio);

                EstudioArchivo::create([
                    'estudio_id' => $estudio->id,
                    'path' => $relativePath,
                    'nombre_original' => $originalName,
                    'mime_type' => $mimeType,
                    'size' => $size,
                ]);

                $uploadedCount++;
            }
        }

        return back()->with('success', "$uploadedCount archivos subidos correctamente.");
    }

    public function destroyEstudioArchivo(EstudioArchivo $archivo)
    {
        $estudio = $archivo->estudio;

        if (auth()->user()->hasRole('doctor') && $estudio->consulta->doctor_id !== auth()->id()) {
            abort(403);
        }

        try {
            $this->deleteEstudioFileFromPublic($archivo->path);
            $archivo->delete();

            return back()->with('success', 'Archivo de estudio eliminado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el archivo de estudio.');
        }
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

            return back()->with('error', 'Error al guardar la consulta: '.$e->getMessage());
        }
    }

    public function storeEstudio(Request $request, Consulta $consulta)
    {
        $request->validate([
            'orden' => 'required|string',
            'observacion' => 'nullable|string',
            'archivos.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $estudio = Estudio::create([
            'consulta_id' => $consulta->id,
            'orden' => $request->orden,
            'observacion' => $request->observacion,
        ]);

        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $archivo) {
                $originalName = $archivo->getClientOriginalName();
                $mimeType = $archivo->getMimeType();
                $size = $archivo->getSize();

                $relativePath = $this->storeEstudioFileToPublic($archivo, $estudio);

                EstudioArchivo::create([
                    'estudio_id' => $estudio->id,
                    'path' => $relativePath,
                    'nombre_original' => $originalName,
                    'mime_type' => $mimeType,
                    'size' => $size,
                ]);
            }
        }

        return back()->with('success', 'Orden de estudio guardada correctamente.');
    }

    private function storeEstudioFileToPublic($file, Estudio $estudio)
    {
        $directory = 'estudios/'.$estudio->id;
        $fullDir = public_path($directory);

        if (! is_dir($fullDir)) {
            mkdir($fullDir, 0755, true);
        }

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $filename = Str::slug($originalName).'-'.uniqid().'.'.$extension;

        $file->move($fullDir, $filename);

        return $directory.'/'.$filename;
    }

    private function deleteEstudioFileFromPublic($relativePath)
    {
        $fullPath = public_path($relativePath);

        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
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

        return $pdf->stream('receta-consulta-'.$consulta->id.'.pdf');
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

        return $pdf->stream('orden-estudio-'.$estudio->id.'.pdf');
    }

    public function destroy(Consulta $consulta)
    {
        // Security Check
        if (auth()->user()->hasRole(['asistente', 'secretaria'])) {
            abort(403);
        }
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
