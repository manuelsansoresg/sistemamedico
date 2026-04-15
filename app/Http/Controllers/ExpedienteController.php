<?php

namespace App\Http\Controllers;

use App\Models\Clinica;
use App\Models\Consulta;
use App\Models\Consultorio;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

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

        if ($request->filled('paciente_id')) {
            $query->where('consultas.paciente_id', $request->paciente_id);
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
        $pacientes = collect();
        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            $owner = $user->hasRole('doctor') ? $user : \App\Models\User::find($ownerId);

            $clinicas = $owner
                ? $owner->clinicas()->select('clinicas.*')->where('clinicas.created_by', $ownerId)->distinct()->get()
                : collect();
            $consultorios = $owner
                ? $owner->consultorios()->select('consultorios.*')->where('consultorios.created_by', $ownerId)->distinct()->get()
                : collect();

            if ($owner) {
                $pacientesQuery = User::role('paciente')
                    ->where(function ($q) use ($ownerId) {
                        $q->whereHas('doctors', function ($subQ) use ($ownerId) {
                            $subQ->where('users.id', $ownerId);
                        })
                            ->orWhere('created_by', $ownerId);
                    });

                $pacientes = $pacientesQuery
                    ->orderBy('name')
                    ->orderBy('apellido_paterno')
                    ->get();
            }
            
        } else {
            $clinicas = Clinica::where('activo', true)->get();
            $consultorios = Consultorio::where('activo', true)->get();

            $pacientes = User::role('paciente')
                ->orderBy('name')
                ->orderBy('apellido_paterno')
                ->get();
        }

        return view('admin.expedientes.index', compact('expedientes', 'clinicas', 'consultorios', 'pacientes'));
    }

    /**
     * Expedientes para Paciente autenticado
     */
    public function patientIndex(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (! $user->hasRole('paciente')) {
            abort(403);
        }

        return redirect()->route('dashboard', $request->query());
    }

    public function patientDownloadBulk(Request $request)
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\User) {
            abort(403);
        }
        if (! $user->hasRole('paciente')) {
            abort(403);
        }

        if (! $this->patientHasActiveSubscription($user)) {
            return back()->with('error', 'Tu suscripción de paciente ha vencido. Solicita a tu médico la renovación para descargar tu expediente.');
        }

        $request->validate([
            'selected' => 'required|array',
            'selected.*' => 'integer',
        ]);

        return $this->generateZip($request->selected);
    }

    public function patientDownloadAll(Request $request)
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\User) {
            abort(403);
        }
        if (! $user->hasRole('paciente')) {
            abort(403);
        }

        if (! $this->patientHasActiveSubscription($user)) {
            return back()->with('error', 'Tu suscripción de paciente ha vencido. Solicita a tu médico la renovación para descargar tu expediente.');
        }

        $query = \App\Models\Consulta::join('citas', 'consultas.cita_id', '=', 'citas.id')
            ->select('consultas.id')
            ->where('consultas.paciente_id', $user->id);

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

    /**
     * Historial completo de un paciente (para root/doctor/asistente/secretaria).
     */
    public function patientHistory(User $paciente, Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;

            $isRelated = $paciente->hasRole('paciente') && (
                $paciente->created_by === $ownerId ||
                $paciente->doctors()->where('users.id', $ownerId)->exists()
            );

            if (! $isRelated) {
                abort(403);
            }
        }

        $query = Consulta::with(['cita.clinica', 'cita.consultorio', 'doctor', 'estudios'])
            ->join('citas', 'consultas.cita_id', '=', 'citas.id')
            ->select('consultas.*')
            ->where('consultas.paciente_id', $paciente->id);

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

        $query->orderBy('citas.fecha', 'desc');
        $expedientes = $query->paginate(15)->withQueryString();

        $clinicas = Clinica::whereIn('id', function ($q) use ($paciente) {
            $q->select('clinica_id')
                ->from('citas')
                ->whereIn('id', function ($q2) use ($paciente) {
                    $q2->select('cita_id')->from('consultas')->where('paciente_id', $paciente->id);
                });
        })->get();

        $consultorios = Consultorio::whereIn('id', function ($q) use ($paciente) {
            $q->select('consultorio_id')
                ->from('citas')
                ->whereIn('id', function ($q2) use ($paciente) {
                    $q2->select('cita_id')->from('consultas')->where('paciente_id', $paciente->id);
                });
        })->get();

        return view('admin.expedientes.paciente', compact('paciente', 'expedientes', 'clinicas', 'consultorios'));
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
        if ($request->filled('paciente_id')) {
            $query->where('consultas.paciente_id', $request->paciente_id);
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

            $query->where(function ($q) use ($assignedClinicas, $assignedConsultorios, $ownerId) {
                $q->whereIn('citas.clinica_id', $assignedClinicas)
                    ->orWhereIn('citas.consultorio_id', $assignedConsultorios)
                    ->orWhere('citas.doctor_id', $ownerId);
            });
        }

        return $query;
    }

    private function generateZip($ids)
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\User) {
            abort(403);
        }

        $isPatient = $user->hasRole('paciente');

        if ($isPatient) {
            if (! $this->patientHasActiveSubscription($user)) {
                return back()->with('error', 'Tu suscripción de paciente ha vencido. Solicita a tu médico la renovación para descargar tu expediente.');
            }

            $ownIds = Consulta::whereIn('consultas.id', $ids)
                ->where('paciente_id', $user->id)
                ->pluck('consultas.id')
                ->toArray();

            if (empty($ownIds)) {
                return back()->with('error', 'No hay expedientes para descargar.');
            }

            $ids = $ownIds;

            $canDownloadConsultas = true;
            $canDownloadEstudios = true;
            $canDownloadImages = true;
            $canDownloadAll = true;
        } else {
            $canDownloadConsultas = $user->can('descargar consultas');
            $canDownloadEstudios = $user->can('descargar estudios');
            $canDownloadImages = $user->can('descargar estudios con imagenes');
            $canDownloadAll = $user->can('descargar expedientes');
        }

        if (! $isPatient && ! $canDownloadConsultas && ! $canDownloadEstudios && ! $canDownloadAll) {
            return back()->with('error', 'No tienes permisos para descargar expedientes.');
        }

        $consultas = Consulta::with(['paciente', 'doctor', 'cita', 'estudios.archivos'])
            ->whereIn('consultas.id', $ids)
            ->get();

        $zipFileName = 'expedientes_'.date('Y-m-d_H-i-s').'.zip';
        // Ensure storage directory exists
        if (! Storage::disk('public')->exists('zips')) {
            Storage::disk('public')->makeDirectory('zips');
        }
        $zipPath = storage_path('app/public/zips/'.$zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {

            foreach ($consultas as $consulta) {
                $folderName = Str::slug($consulta->paciente->name.'_'.$consulta->paciente->apellido_paterno).'_'.$consulta->cita->fecha->format('Y-m-d');

                // 1. Generate PDF of Consulta
                if ($canDownloadConsultas || $canDownloadAll) {
                    try {
                        // We need to ensure the view exists and data is sufficient
                        // 'admin.consultas.pdf' expects 'consulta'
                        $pdfContent = Pdf::loadView('admin.consultas.pdf', compact('consulta'))->output();
                        $zip->addFromString($folderName.'/consulta.pdf', $pdfContent);
                    } catch (\Exception $e) {
                        // Log error but continue
                        Log::error("Error generating PDF for consulta {$consulta->id}: ".$e->getMessage());
                    }
                }

                // 2. Add Estudios
                if (($canDownloadEstudios || $canDownloadAll) && $consulta->estudios->isNotEmpty()) {
                    foreach ($consulta->estudios as $estudio) {
                        // 2.a PDF de la orden de estudio
                        try {
                            $estudioPdfContent = Pdf::loadView('admin.consultas.estudio_pdf', ['estudio' => $estudio])->output();
                            $zip->addFromString($folderName.'/estudios/orden-estudio-'.$estudio->id.'.pdf', $estudioPdfContent);
                        } catch (\Exception $e) {
                            Log::error("Error generating PDF for estudio {$estudio->id}: ".$e->getMessage());
                        }

                        // 2.b Archivos adjuntos del estudio
                        if ($estudio->archivos->isNotEmpty()) {
                            foreach ($estudio->archivos as $archivo) {
                                // Check if image
                                $isImage = str_starts_with($archivo->mime_type, 'image/');

                                if ($isImage && ! $canDownloadImages && ! $canDownloadAll) {
                                    continue;
                                }

                                $content = null;

                                if (Storage::disk('public')->exists($archivo->path)) {
                                    $content = Storage::disk('public')->get($archivo->path);
                                } elseif (file_exists(public_path($archivo->path))) {
                                    $content = file_get_contents(public_path($archivo->path));
                                }

                                if ($content !== null) {
                                    $zip->addFromString($folderName.'/estudios/'.$archivo->nombre_original, $content);
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

    private function patientHasActiveSubscription(User $patient): bool
    {
        return DB::table('doctor_patient as dp')
            ->join('suscripciones as s', 's.id', '=', 'dp.suscripcion_id')
            ->where('dp.patient_id', $patient->id)
            ->where('s.estatus_pago', 'pagado')
            ->where(function ($q) {
                $q->whereNull('s.fecha_fin')->orWhere('s.fecha_fin', '>=', now());
            })
            ->exists();
    }
}
