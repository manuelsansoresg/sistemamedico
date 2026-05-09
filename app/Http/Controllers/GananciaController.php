<?php

namespace App\Http\Controllers;

use App\Models\Ganancia;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GananciaController extends Controller
{
    private function buildQuery(Request $request, &$dateStart, &$dateEnd)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = Ganancia::with(['user', 'catalogo', 'paquete']);

        if ($user->hasRole('doctor')) {
            $query->where('user_id', $user->id);
        }

        $periodo = $request->periodo ?? 'mes_anterior';

        if ($periodo === 'hoy') {
            $dateStart = now()->format('Y-m-d');
            $dateEnd = now()->format('Y-m-d');
        } elseif ($periodo === 'mes_actual') {
            $dateStart = now()->startOfMonth()->format('Y-m-d');
            $dateEnd = now()->format('Y-m-d');
        } elseif ($periodo === 'mes_anterior') {
            $dateStart = now()->subMonth()->startOfMonth()->format('Y-m-d');
            $dateEnd = now()->subMonth()->endOfMonth()->format('Y-m-d');
        } else {
            $dateStart = $request->date_start ?? now()->subMonth()->startOfMonth()->format('Y-m-d');
            $dateEnd = $request->date_end ?? now()->format('Y-m-d');
        }

        $query->whereDate('fecha', '>=', $dateStart);
        $query->whereDate('fecha', '<=', $dateEnd);

        if ($user->hasRole('root') && $request->filled('doctor_id')) {
            $query->where('user_id', $request->doctor_id);
        }

        if ($request->filled('tipo_ingreso')) {
            $query->where('tipo_ingreso', $request->tipo_ingreso);
        }

        if ($request->filled('origen')) {
            $origins = (array) $request->origen;
            $query->where(function ($q) use ($origins) {
                $q->whereHas('catalogo', function ($sq) use ($origins) {
                    $sq->whereIn('nombre', $origins);
                })->orWhereHas('paquete', function ($sq) use ($origins) {
                    $sq->whereIn('nombre', $origins);
                });
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $dateStart = null;
        $dateEnd = null;
        $query = $this->buildQuery($request, $dateStart, $dateEnd);
        $periodo = $request->periodo ?? 'mes_anterior';

        $summaryQuery = clone $query;
        $totalVentas = $summaryQuery->sum('monto_total');

        $chartQuery = clone $summaryQuery;

        if (! $user->hasRole('doctor')) {
            $totalGanancias = $summaryQuery->sum(DB::raw('monto_total - monto_ganancia_doctor'));
            $gananciasPorDia = $chartQuery->selectRaw('DATE(fecha) as dia, SUM(monto_total - monto_ganancia_doctor) as total')
                ->groupBy('dia')->orderBy('dia')->get();
        } else {
            $totalGanancias = $summaryQuery->sum('monto_ganancia_doctor');
            $gananciasPorDia = $chartQuery->selectRaw('DATE(fecha) as dia, SUM(monto_ganancia_doctor) as total')
                ->groupBy('dia')->orderBy('dia')->get();
        }

        $ganancias = $query->latest('fecha')->paginate(15)->withQueryString();

        $doctors = ! $user->hasRole('doctor') ? User::role('doctor')->get() : [];

        $tiposIngreso = [
            'compra' => 'Compra',
            'renovacion' => 'Renovación',
        ];

        $servicios = $this->getServiciosDisponibles();

        return view('ganancias.index', compact(
            'ganancias', 'totalGanancias', 'totalVentas',
            'gananciasPorDia', 'doctors', 'tiposIngreso',
            'servicios', 'periodo', 'dateStart', 'dateEnd'
        ));
    }

    public function exportPdf(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $dateStart = null;
        $dateEnd = null;
        $query = $this->buildQuery($request, $dateStart, $dateEnd);
        $periodo = $request->periodo ?? 'mes_anterior';

        $totalVentas = (clone $query)->sum('monto_total');

        if (! $user->hasRole('doctor')) {
            $totalGanancias = (clone $query)->sum(DB::raw('monto_total - monto_ganancia_doctor'));
        } else {
            $totalGanancias = (clone $query)->sum('monto_ganancia_doctor');
        }

        $ganancias = $query->latest('fecha')->get();

        $nombresMeses = [
            'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
            'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
            'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',
            'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre',
        ];

        $periodoLabel = '';
        if ($periodo === 'hoy') {
            $periodoLabel = 'Hoy';
        } elseif ($periodo === 'mes_anterior') {
            $carbon = now()->subMonth();
            $monthName = $nombresMeses[$carbon->format('F')] ?? $carbon->format('F');
            $periodoLabel = $monthName.' '.$carbon->format('Y');
        } elseif ($periodo === 'mes_actual') {
            $carbon = now();
            $monthName = $nombresMeses[$carbon->format('F')] ?? $carbon->format('F');
            $periodoLabel = $monthName.' '.$carbon->format('Y');
        } else {
            $periodoLabel = 'Del '.\Carbon\Carbon::parse($dateStart)->format('d/m/Y').' al '.\Carbon\Carbon::parse($dateEnd)->format('d/m/Y');
        }

        $pdf = Pdf::loadView('ganancias.pdf', compact(
            'ganancias', 'totalGanancias', 'totalVentas',
            'user', 'periodoLabel', 'dateStart', 'dateEnd'
        ))->setPaper('a4', 'landscape');

        $filename = 'reporte-ganancias-'.now()->format('Y-m-d').'.pdf';

        return $pdf->download($filename);
    }

    private function getServiciosDisponibles()
    {
        return Ganancia::whereNotNull('catalogo_id')
            ->orWhereNotNull('paquete_id')
            ->with('catalogo', 'paquete')
            ->get()
            ->map(function ($g) {
                return optional($g->catalogo)->nombre ?? optional($g->paquete)->nombre ?? null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }
}
