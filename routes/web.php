<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\ComprobantePagoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('public.home');
});

// Ruta pública para subir comprobantes
Route::get('/subir-comprobante/{token}', [ComprobantePagoController::class, 'show'])->name('suscripciones.subir_comprobante');
Route::post('/subir-comprobante/{token}', [ComprobantePagoController::class, 'store'])->name('suscripciones.guardar_comprobante');
Route::get('/subir-comprobante/{token}/enviado', [ComprobantePagoController::class, 'enviado'])->name('suscripciones.comprobante_enviado');

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'check.doctor.status'])
    ->name('dashboard');

Route::get('/doctor/verification-notice', function () {
    return view('doctor.verification_notice');
})->middleware(['auth', 'role:doctor'])->name('doctor.verification.notice');

Route::middleware(['auth', 'role:doctor', 'check.doctor.status'])->group(function () {
    Route::get('/doctor/wizard', [\App\Http\Controllers\Doctor\WizardController::class, 'index'])->name('doctor.wizard.index');
    Route::post('/doctor/wizard/clinica', [\App\Http\Controllers\Doctor\WizardController::class, 'storeClinica'])->name('doctor.wizard.store_clinica');
    Route::post('/doctor/wizard/consultorio', [\App\Http\Controllers\Doctor\WizardController::class, 'storeConsultorio'])->name('doctor.wizard.store_consultorio');
});

Route::middleware(['auth', 'role:root'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::resource('admin/catalogos', \App\Http\Controllers\CatalogoController::class);
    Route::resource('admin/paquetes', \App\Http\Controllers\PaqueteController::class);
    Route::resource('admin/configuraciones', \App\Http\Controllers\ConfiguracionController::class);
    Route::resource('admin/especialidades', \App\Http\Controllers\EspecialidadController::class);

    // Gestión de Suscripciones y Validaciones (Root)
    Route::get('admin/suscripciones', [\App\Http\Controllers\Admin\SuscripcionController::class, 'index'])->name('admin.suscripciones.index');
    Route::get('admin/suscripciones/create', [\App\Http\Controllers\Admin\SuscripcionController::class, 'create'])->name('admin.suscripciones.create');
    Route::post('admin/suscripciones', [\App\Http\Controllers\Admin\SuscripcionController::class, 'store'])->name('admin.suscripciones.store');
    Route::get('admin/suscripciones/{suscripcion}', [\App\Http\Controllers\Admin\SuscripcionController::class, 'show'])->name('admin.suscripciones.show');
    Route::put('admin/suscripciones/{suscripcion}', [\App\Http\Controllers\Admin\SuscripcionController::class, 'update'])->name('admin.suscripciones.update');
    Route::post('admin/users/{user}/validar-cedula', [\App\Http\Controllers\Admin\SuscripcionController::class, 'validarCedula'])->name('admin.users.validar_cedula');
    Route::get('admin/suscripciones/{suscripcion}/download-comprobante', [\App\Http\Controllers\Admin\SuscripcionController::class, 'downloadComprobante'])->name('admin.suscripciones.download_comprobante');

    // Horarios Routes (Moved to shared group)
});

Route::middleware(['auth', 'role:root|doctor|asistente|secretaria', 'check.doctor.status'])->group(function () {
    Route::resource('admin/clinicas', \App\Http\Controllers\ClinicaController::class);
    Route::resource('admin/consultorios', \App\Http\Controllers\ConsultorioController::class);
    Route::resource('admin/users', \App\Http\Controllers\UserController::class);

    // Pacientes Routes
    Route::get('admin/pacientes/compartidos', [\App\Http\Controllers\PacienteController::class, 'sharedIndex'])->name('pacientes.shared.index');
    Route::post('admin/pacientes/{paciente}/compartir', [\App\Http\Controllers\PacienteController::class, 'share'])->name('pacientes.share');
    Route::post('admin/pacientes/{paciente}/dejar-compartir', [\App\Http\Controllers\PacienteController::class, 'unshare'])->name('pacientes.unshare');
    Route::post('admin/pacientes/{paciente}/toggle-compartir', [\App\Http\Controllers\PacienteController::class, 'toggleShare'])->name('pacientes.toggle_share');
    Route::resource('admin/pacientes', \App\Http\Controllers\PacienteController::class);

    // Citas Routes
    Route::resource('admin/citas', \App\Http\Controllers\Admin\CitaController::class);
    Route::get('admin/api/doctors/search', [\App\Http\Controllers\Admin\CitaController::class, 'searchDoctors'])->name('api.doctors.search');
    Route::get('admin/api/doctors/{id}/data', [\App\Http\Controllers\Admin\CitaController::class, 'getDoctorData'])->name('api.doctors.data');
    Route::get('admin/api/patients/search', [\App\Http\Controllers\Admin\CitaController::class, 'searchPatients'])->name('api.patients.search');
    Route::get('admin/api/slots', [\App\Http\Controllers\Admin\CitaController::class, 'getSlots'])->name('api.slots');

    // Expedientes Routes
    Route::get('admin/expedientes', [\App\Http\Controllers\ExpedienteController::class, 'index'])->name('expedientes.index');
    Route::get('admin/expedientes/paciente/{paciente}', [\App\Http\Controllers\ExpedienteController::class, 'patientHistory'])->name('expedientes.paciente');
    Route::post('admin/expedientes/download', [\App\Http\Controllers\ExpedienteController::class, 'downloadBulk'])->name('expedientes.download.bulk');
    Route::get('admin/expedientes/download-all', [\App\Http\Controllers\ExpedienteController::class, 'downloadAll'])->name('expedientes.download.all');

    // Plantillas Routes
    Route::resource('admin/plantillas', \App\Http\Controllers\PlantillaController::class);
    Route::get('admin/plantillas/{plantilla}/campos', [\App\Http\Controllers\PlantillaController::class, 'getCampos'])->name('plantillas.campos');

    // Horarios Routes
    Route::get('admin/horarios', [\App\Http\Controllers\HorarioController::class, 'index'])->name('horarios.index');
    Route::get('admin/horarios/manage', [\App\Http\Controllers\HorarioController::class, 'manage'])->name('horarios.manage');
    Route::post('admin/horarios', [\App\Http\Controllers\HorarioController::class, 'store'])->name('horarios.store');

    // Consultas Routes
    Route::get('consultas/create/{cita_id}', [\App\Http\Controllers\ConsultaController::class, 'create'])->name('consultas.create');
    Route::post('consultas', [\App\Http\Controllers\ConsultaController::class, 'store'])->name('consultas.store');
    Route::resource('consultas', \App\Http\Controllers\ConsultaController::class)->except(['create', 'store', 'index']);
    Route::post('consultas/{consulta}/estudios', [\App\Http\Controllers\ConsultaController::class, 'storeEstudio'])->name('consultas.estudios.store');
    Route::get('consultas/{consulta}/print', [\App\Http\Controllers\ConsultaController::class, 'print'])->name('consultas.print');
    Route::get('estudios/{estudio}/print', [\App\Http\Controllers\ConsultaController::class, 'printEstudio'])->name('consultas.estudios.print');
    Route::post('estudios/{estudio}/upload', [\App\Http\Controllers\ConsultaController::class, 'uploadEstudioFile'])->name('consultas.estudios.upload');
    Route::get('estudios/{estudio}/edit', [\App\Http\Controllers\ConsultaController::class, 'editEstudio'])->name('consultas.estudios.edit');
    Route::put('estudios/{estudio}', [\App\Http\Controllers\ConsultaController::class, 'updateEstudio'])->name('consultas.estudios.update');
    Route::post('estudios/{estudio}/actualizar', [\App\Http\Controllers\ConsultaController::class, 'updateEstudio'])->name('consultas.estudios.update.post');
    Route::delete('estudios/{estudio}', [\App\Http\Controllers\ConsultaController::class, 'destroyEstudio'])->name('consultas.estudios.destroy');
    Route::get('estudios-archivos/{archivo}/delete', [\App\Http\Controllers\ConsultaController::class, 'destroyEstudioArchivo'])->name('consultas.estudios.archivos.delete');

    // Ganancias Routes (Restricted to Root and Doctor)
    Route::get('ganancias', [\App\Http\Controllers\GananciaController::class, 'index'])
        ->middleware('role:root|doctor')
        ->name('ganancias.index');

    // Días Sin Citas
    Route::resource('dias-sin-citas', \App\Http\Controllers\DiaSinCitaController::class);

    // Notifications
    Route::get('admin/notifications/{id}/read', [\App\Http\Controllers\DashboardController::class, 'markNotificationRead'])->name('admin.notifications.read');

    // Recursos compartidos
    Route::get('admin/recursos', [\App\Http\Controllers\RecursoController::class, 'index'])->name('recursos.index');
    Route::post('admin/recursos', [\App\Http\Controllers\RecursoController::class, 'store'])->name('recursos.store');
    Route::put('admin/recursos/{recurso}', [\App\Http\Controllers\RecursoController::class, 'update'])->name('recursos.update');
    Route::delete('admin/recursos/{recurso}', [\App\Http\Controllers\RecursoController::class, 'destroy'])->name('recursos.destroy');

    Route::get('admin/recursos/permisos', [\App\Http\Controllers\RecursoController::class, 'permisos'])->name('recursos.permisos');
    Route::post('admin/recursos/permisos', [\App\Http\Controllers\RecursoController::class, 'actualizarPermisos'])->name('recursos.permisos.actualizar');

    Route::get('admin/recursos/agenda', [\App\Http\Controllers\RecursoReservaController::class, 'calendario'])->name('recursos.agenda');
    Route::get('admin/recursos/eventos', [\App\Http\Controllers\RecursoReservaController::class, 'eventos'])->name('recursos.eventos');
    Route::post('admin/recursos/eventos', [\App\Http\Controllers\RecursoReservaController::class, 'store'])->name('recursos.eventos.store');
    Route::put('admin/recursos/eventos/{reserva}', [\App\Http\Controllers\RecursoReservaController::class, 'update'])->name('recursos.eventos.update');
    Route::delete('admin/recursos/eventos/{reserva}', [\App\Http\Controllers\RecursoReservaController::class, 'destroy'])->name('recursos.eventos.destroy');
});

Route::middleware(['auth', 'role:doctor'])->group(function () {
    // Compras / Catálogo (Doctor only)
    Route::get('compras', [\App\Http\Controllers\CompraController::class, 'index'])->name('compras.index');
    Route::post('compras', [\App\Http\Controllers\CompraController::class, 'store'])->name('compras.store');
    Route::post('compras/renovar-paquete', [\App\Http\Controllers\CompraController::class, 'renewPackage'])->name('compras.renovar_paquete');
    Route::post('compras/{suscripcion}/comprobante', [\App\Http\Controllers\CompraController::class, 'uploadComprobante'])->name('compras.upload_comprobante');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Pendientes Routes (Doctor/User specific)
    Route::resource('pendientes', \App\Http\Controllers\PendienteController::class);
});

// Portal Paciente
Route::middleware(['auth', 'role:paciente'])->group(function () {
    Route::get('/mis-expedientes', [\App\Http\Controllers\ExpedienteController::class, 'patientIndex'])->name('paciente.expedientes.index');
    Route::post('/mis-expedientes/descargar', [\App\Http\Controllers\ExpedienteController::class, 'patientDownloadBulk'])->name('paciente.expedientes.download.bulk');
    Route::get('/mis-expedientes/descargar-todo', [\App\Http\Controllers\ExpedienteController::class, 'patientDownloadAll'])->name('paciente.expedientes.download.all');
});

require __DIR__.'/auth.php';
