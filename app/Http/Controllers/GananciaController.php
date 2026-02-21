<?php

namespace App\Http\Controllers;

use App\Models\Ganancia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GananciaController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = Ganancia::with(['user', 'catalogo']);

        // Filtrar por rol
        if ($user->hasRole('doctor')) {
            // El doctor ve sus propias ganancias
            $query->where('user_id', $user->id);
        }
        // Si es Root, ve todo (sin filtro adicional por defecto)

        // Filtros de búsqueda
        if ($request->filled('date_start')) {
            $query->whereDate('fecha', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->whereDate('fecha', '<=', $request->date_end);
        }

        // Filtro por doctor (solo para root)
        if ($user->hasRole('root') && $request->filled('doctor_id')) {
            $query->where('user_id', $request->doctor_id);
        }

        // Obtener datos para gráficos / resumen
        // Clonamos la query para los totales antes de paginar
        $summaryQuery = clone $query;

        // Calcular total ventas (común para ambos, basado en la query filtrada)
        // Lo calculamos ANTES de cualquier modificación de group/select en summaryQuery
        $totalVentas = $summaryQuery->sum('monto_total');

        // Clonamos de nuevo para el gráfico para evitar ensuciar la query original o reusar una modificada
        $chartQuery = clone $summaryQuery;

        // Calcular totales según el rol
        if ($user->hasRole('root')) {
            // Para Root: Ganancia Total = Suma(Total - GananciaDoctor)
            $totalGanancias = $summaryQuery->sum(DB::raw('monto_total - monto_ganancia_doctor'));

            $gananciasPorDia = $chartQuery->selectRaw('DATE(fecha) as dia, SUM(monto_total - monto_ganancia_doctor) as total')
                ->groupBy('dia')
                ->orderBy('dia')
                ->get();
        } else {
            // Para Doctor: Ganancia = monto_ganancia_doctor
            $totalGanancias = $summaryQuery->sum('monto_ganancia_doctor');

            $gananciasPorDia = $chartQuery->selectRaw('DATE(fecha) as dia, SUM(monto_ganancia_doctor) as total')
                ->groupBy('dia')
                ->orderBy('dia')
                ->get();
        }

        $ganancias = $query->latest('fecha')->paginate(15)->withQueryString();

        // Para el filtro de doctores (solo root)
        $doctors = $user->hasRole('root') ? \App\Models\User::role('doctor')->get() : [];

        return view('ganancias.index', compact('ganancias', 'totalGanancias', 'totalVentas', 'gananciasPorDia', 'doctors'));
    }
}
