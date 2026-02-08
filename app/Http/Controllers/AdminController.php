<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\DiaSinCita;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        $today = now()->timezone('America/Mexico_City')->toDateString();

        $diasBloqueadosHoy = DiaSinCita::whereDate('fecha_inicio', '<=', $today)
            ->whereDate('fecha_fin', '>=', $today)
            ->with('consultorios')
            ->get();

        return view('admin.dashboard', compact('diasBloqueadosHoy'));
    }
}
