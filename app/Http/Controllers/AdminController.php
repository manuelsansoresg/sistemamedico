<?php

namespace App\Http\Controllers;

use App\Models\DiaSinCita;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user && ! $user->hasRole('root')) {
            return redirect()->route('dashboard');
        }

        $today = now()->timezone('America/Mexico_City')->toDateString();

        $diasBloqueadosHoy = DiaSinCita::whereDate('fecha_inicio', '<=', $today)
            ->whereDate('fecha_fin', '>=', $today)
            ->with('consultorios')
            ->get();

        return view('admin.dashboard', compact('diasBloqueadosHoy'));
    }
}
