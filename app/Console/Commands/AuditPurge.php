<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class AuditPurge extends Command
{
    protected $signature = 'audit:purge {--days=365}';

    protected $description = 'Eliminar registros antiguos de todas las tablas de auditoría';

    public function handle()
    {
        $days = (int) $this->option('days');

        if ($days <= 0) {
            $this->error('El valor de --days debe ser un entero mayor a 0.');

            return self::FAILURE;
        }

        $threshold = now()->subDays($days);
        $totalDeleted = 0;

        foreach (AuditLog::categories() as $cat) {
            $modelClass = $cat['model'];
            $deleted = $modelClass::query()
                ->where('created_at', '<', $threshold)
                ->delete();

            $this->line("  {$cat['table']}: {$deleted} registros eliminados.");
            $totalDeleted += $deleted;
        }

        $this->info("Total de registros eliminados: {$totalDeleted}. Umbral: {$threshold->toDateTimeString()} (>{$days} días).");

        return self::SUCCESS;
    }
}
