<?php

namespace App\Console\Commands;

use App\Models\Suscripcion;
use App\Notifications\SubscriptionExpiringNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckSubscriptionExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suscripciones:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expiring subscriptions and notify users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysToNotify = [7, 3, 1];
        $count = 0;

        foreach ($daysToNotify as $days) {
            $date = Carbon::now()->addDays($days)->format('Y-m-d');

            $this->info("Checking subscriptions expiring on $date ($days days from now)...");

            $suscripciones = Suscripcion::whereDate('fecha_fin', $date)
                ->where('estatus_pago', 'pagado')
                ->with(['user', 'paquete', 'catalogo'])
                ->get();

            foreach ($suscripciones as $suscripcion) {
                if ($suscripcion->user) {
                    try {
                        $suscripcion->user->notify(new SubscriptionExpiringNotification($suscripcion));
                        $this->info("Sent notification to {$suscripcion->user->email} (ID: {$suscripcion->id})");
                        $count++;
                    } catch (\Exception $e) {
                        $this->error("Failed to notify user {$suscripcion->user->id}: ".$e->getMessage());
                    }
                }
            }
        }

        $this->info("Process completed. Sent $count notifications.");
    }
}
