<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class AuditPurge extends Command
{
    protected $signature = 'audit:purge {--days=365}';

    protected $description = 'Eliminar registros antiguos de audit_logs';

    public function handle()
    {
        $days = (int) $this->option('days');

        if ($days <= 0) {
            $this->error('El valor de --days debe ser un entero mayor a 0.');

            return self::FAILURE;
        }

        $threshold = now()->subDays($days);

        $deleted = AuditLog::query()
            ->where('created_at', '<', $threshold)
            ->delete();

        $this->info("Registros eliminados: {$deleted}. Umbral: {$threshold->toDateTimeString()} (>{$days} días).");

        return self::SUCCESS;
    }
}
