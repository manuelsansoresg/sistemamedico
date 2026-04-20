<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('suscripciones:check-expiry')->dailyAt('09:00');

// NUEVA TAREA: Purga de bitácora (el primer día de cada mes a medianoche)
// Mantendremos 180 días de rastro, pero puedes cambiarlo a 365 si prefieres.
Schedule::command('audit:purge --days=180')->monthly();
