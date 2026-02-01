<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'role:root'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::resource('admin/catalogos', \App\Http\Controllers\CatalogoController::class);
    Route::resource('admin/paquetes', \App\Http\Controllers\PaqueteController::class);
    Route::resource('admin/configuraciones', \App\Http\Controllers\ConfiguracionController::class);
    Route::resource('admin/especialidades', \App\Http\Controllers\EspecialidadController::class);
    
    // Horarios Routes (Moved to shared group)
});

Route::middleware(['auth', 'role:root|doctor'])->group(function () {
    Route::resource('admin/clinicas', \App\Http\Controllers\ClinicaController::class);
    Route::resource('admin/consultorios', \App\Http\Controllers\ConsultorioController::class);
    Route::resource('admin/users', \App\Http\Controllers\UserController::class);
    
    // Pacientes Routes
    Route::resource('admin/pacientes', \App\Http\Controllers\PacienteController::class);

    // Citas Routes
    Route::resource('admin/citas', \App\Http\Controllers\Admin\CitaController::class);
    Route::get('admin/api/doctors/search', [\App\Http\Controllers\Admin\CitaController::class, 'searchDoctors'])->name('api.doctors.search');
    Route::get('admin/api/doctors/{id}/data', [\App\Http\Controllers\Admin\CitaController::class, 'getDoctorData'])->name('api.doctors.data');
    Route::get('admin/api/patients/search', [\App\Http\Controllers\Admin\CitaController::class, 'searchPatients'])->name('api.patients.search');
    Route::get('admin/api/slots', [\App\Http\Controllers\Admin\CitaController::class, 'getSlots'])->name('api.slots');

    // Plantillas Routes
    Route::resource('admin/plantillas', \App\Http\Controllers\PlantillaController::class);

    // Horarios Routes
    Route::get('admin/horarios', [\App\Http\Controllers\HorarioController::class, 'index'])->name('horarios.index');
    Route::get('admin/horarios/manage', [\App\Http\Controllers\HorarioController::class, 'manage'])->name('horarios.manage');
    Route::post('admin/horarios', [\App\Http\Controllers\HorarioController::class, 'store'])->name('horarios.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Pendientes Routes (Doctor/User specific)
    Route::resource('pendientes', \App\Http\Controllers\PendienteController::class);
});

require __DIR__.'/auth.php';
