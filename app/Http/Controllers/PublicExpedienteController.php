<?php

namespace App\Http\Controllers;

use App\Models\Clinica;
use App\Models\Consulta;
use App\Models\Consultorio;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicExpedienteController extends Controller
{
    public function show(string $token, Request $request): View
    {
        $paciente = $this->findPatientByToken($token);

        $query = Consulta::with(['cita.clinica', 'cita.consultorio', 'doctor', 'plantilla', 'estudios'])
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

        $expedientes = $query->orderBy('citas.fecha', 'desc')->paginate(15)->withQueryString();
        $clinicas = $this->clinicasForPatient($paciente);
        $consultorios = $this->consultoriosForPatient($paciente);

        return view('public.expediente', compact('paciente', 'expedientes', 'clinicas', 'consultorios', 'token'));
    }

    public function consulta(string $token, Consulta $consulta): View
    {
        $paciente = $this->findPatientByToken($token);

        abort_unless($consulta->paciente_id === $paciente->id, 404);

        $consulta->load([
            'paciente',
            'doctor',
            'cita.clinica',
            'cita.consultorio',
            'plantilla',
            'valores.campo',
            'estudios.archivos',
        ]);

        return view('public.consulta', compact('paciente', 'consulta', 'token'));
    }

    private function findPatientByToken(string $token): User
    {
        return User::role('paciente')
            ->where('patient_public_token', $token)
            ->firstOrFail();
    }

    private function clinicasForPatient(User $paciente): Collection
    {
        return Clinica::whereIn('id', function ($q) use ($paciente) {
            $q->select('clinica_id')
                ->from('citas')
                ->whereIn('id', function ($q2) use ($paciente) {
                    $q2->select('cita_id')->from('consultas')->where('paciente_id', $paciente->id);
                });
        })->get();
    }

    private function consultoriosForPatient(User $paciente): Collection
    {
        return Consultorio::whereIn('id', function ($q) use ($paciente) {
            $q->select('consultorio_id')
                ->from('citas')
                ->whereIn('id', function ($q2) use ($paciente) {
                    $q2->select('cita_id')->from('consultas')->where('paciente_id', $paciente->id);
                });
        })->get();
    }
}
