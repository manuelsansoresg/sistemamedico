<?php

namespace App\Http\Controllers;

use App\Models\Consulta;
use App\Models\Clinica;
use App\Models\Consultorio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ExpedienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $query = $this->buildQuery($user);

        // Apply filters
        if ($request->filled('clinica_id')) {
            $query->where('citas.clinica_id', $request->clinica_id);
        }

        if ($request->filled('consultorio_id')) {
            $query->where('citas.consultorio_id', $request->consultorio_id);
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('citas.fecha', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('citas.fecha', '<=', $request->fecha_fin);
        }
        
        // Order by date desc
        $query->orderBy('citas.fecha', 'desc');

        $expedientes = $query->paginate(15);
        
        // Load filter options
        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            $owner = $user->hasRole('doctor') ? $user : \App\Models\User::find($ownerId);
            
            $clinicas = $owner->clinicas;
            $consultorios = $owner->consultorios;
        } else {
            $clinicas = Clinica::where('activo', true)->get();
            $consultorios = Consultorio::where('activo', true)->get();
        }

        return view('admin.expedientes.index', compact('expedientes', 'clinicas', 'consultorios'));
    }

    public function downloadBulk(Request $request)
    {
        $request->validate([
            'selected' => 'required|array',
            'selected.*' => 'exists:consultas,id',
        ]);

        return $this->generateZip($request->selected);
    }

    public function downloadAll(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = $this->buildQuery($user);

        if ($request->filled('clinica_id')) {
            $query->where('citas.clinica_id', $request->clinica_id);
        }
        if ($request->filled('consultorio_id')) {
            $query->where('citas.consultorio_id', $request->consultorio_id);
        }
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('citas.fecha', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('citas.fecha', '<=', $request->fecha_fin);
        }

        $ids = $query->pluck('consultas.id')->toArray();
        
        if (empty($ids)) {
            return back()->with('error', 'No hay expedientes para descargar con los filtros seleccionados.');
        }

        return $this->generateZip($ids);
    }

    private function buildQuery($user)
    {
        $query = Consulta::with(['cita.clinica', 'cita.consultorio', 'paciente', 'doctor', 'estudios'])
            ->join('citas', 'consultas.cita_id', '=', 'citas.id')
            ->select('consultas.*');

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            $owner = $user->hasRole('doctor') ? $user : \App\Models\User::find($ownerId);
            
            $assignedClinicas = $owner->clinicas->pluck('id');
            $assignedConsultorios = $owner->consultorios->pluck('id');
            
            $query->where(function($q) use ($assignedClinicas, $assignedConsultorios, $ownerId) {
                $q->whereIn('citas.clinica_id', $assignedClinicas)
                  ->orWhereIn('citas.consultorio_id', $assignedConsultorios)
                  ->orWhere('citas.doctor_id', $ownerId);
            });
        }
        
        return $query;
    }

    private function generateZip($ids)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Permission checks
        $canDownloadConsultas = $user->can('descargar consultas');
        $canDownloadEstudios = $user->can('descargar estudios');
        $canDownloadImages = $user->can('descargar estudios con imagenes');
        $canDownloadAll = $user->can('descargar expedientes'); // or 'descargar todo'?

        if (!$canDownloadConsultas && !$canDownloadEstudios && !$canDownloadAll) {
             return back()->with('error', 'No tienes permisos para descargar expedientes.');
        }

        $consultas = Consulta::with(['paciente', 'doctor', 'cita', 'estudios.archivos'])
            ->whereIn('consultas.id', $ids)
            ->get();

        $zipFileName = 'expedientes_' . date('Y-m-d_H-i-s') . '.zip';
        // Ensure storage directory exists
        if (!Storage::disk('public')->exists('zips')) {
            Storage::disk('public')->makeDirectory('zips');
        }
        $zipPath = storage_path('app/public/zips/' . $zipFileName);
        
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            
            foreach ($consultas as $consulta) {
                $folderName = Str::slug($consulta->paciente->name . '_' . $consulta->paciente->apellido_paterno) . '_' . $consulta->cita->fecha->format('Y-m-d');
                
                // 1. Generate PDF of Consulta
                if ($canDownloadConsultas || $canDownloadAll) {
                    try {
                        // We need to ensure the view exists and data is sufficient
                        // 'admin.consultas.pdf' expects 'consulta'
                        $pdfContent = Pdf::loadView('admin.consultas.pdf', compact('consulta'))->output();
                        $zip->addFromString($folderName . '/consulta.pdf', $pdfContent);
                    } catch (\Exception $e) {
                        // Log error but continue
                        Log::error("Error generating PDF for consulta {$consulta->id}: " . $e->getMessage());
                    }
                }
                
                // 2. Add Estudios
                if (($canDownloadEstudios || $canDownloadAll) && $consulta->estudios->isNotEmpty()) {
                    foreach ($consulta->estudios as $estudio) {
                         if ($estudio->archivos->isNotEmpty()) {
                             foreach ($estudio->archivos as $archivo) {
                                 // Check if image
                                 $isImage = str_starts_with($archivo->mime_type, 'image/');
                                 
                                 if ($isImage && !$canDownloadImages && !$canDownloadAll) {
                                     continue;
                                 }
                                 
                                 if (Storage::disk('public')->exists($archivo->path)) {
                                     $content = Storage::disk('public')->get($archivo->path);
                                     $zip->addFromString($folderName . '/estudios/' . $archivo->nombre_original, $content);
                                 }
                             }
                         }
                    }
                }
            }
            
            $zip->close();
        } else {
            return back()->with('error', 'No se pudo crear el archivo ZIP.');
        }
        
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
