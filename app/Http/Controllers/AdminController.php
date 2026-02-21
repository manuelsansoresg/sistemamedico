<?php

namespace App\Http\Controllers;

use App\Models\DiaSinCita;

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
