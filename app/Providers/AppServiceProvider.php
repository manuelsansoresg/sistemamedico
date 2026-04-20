<?php

namespace App\Providers;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $write = function (?int $userId, string $action, ?string $section, ?string $modelType, $modelId, ?array $payload = null): void {
            try {
                $request = null;
                if (! app()->runningInConsole()) {
                    $request = request();
                }

                AuditLog::create([
                    'user_id' => $userId,
                    'action' => $action,
                    'section' => $section,
                    'model_type' => $modelType,
                    'model_id' => $modelId,
                    'payload' => $payload,
                    'ip_address' => $request ? $request->ip() : null,
                    'user_agent' => $request ? $request->userAgent() : null,
                    'created_at' => now(),
                ]);
            } catch (Throwable $e) {
                report($e);
            }
        };

        $this->app['events']->listen(Login::class, function (Login $event) use ($write) {
            $userId = $event->user ? (int) $event->user->getAuthIdentifier() : null;
            $write($userId, 'login', 'seguridad', $event->user ? get_class($event->user) : null, $userId, null);
        });

        $this->app['events']->listen(Failed::class, function (Failed $event) use ($write) {
            $userId = $event->user ? (int) $event->user->getAuthIdentifier() : null;

            $payload = [
                'credentials' => [
                    'email' => $event->credentials['email'] ?? null,
                ],
            ];

            $write($userId, 'login_fallido', 'seguridad', $event->user ? get_class($event->user) : null, $userId, $payload);
        });

        $this->app['events']->listen(Logout::class, function (Logout $event) use ($write) {
            $userId = $event->user ? (int) $event->user->getAuthIdentifier() : null;
            $write($userId, 'logout', 'seguridad', $event->user ? get_class($event->user) : null, $userId, null);
        });

        $this->app['events']->listen(PasswordReset::class, function (PasswordReset $event) use ($write) {
            $userId = $event->user ? (int) $event->user->getAuthIdentifier() : null;
            $write($userId, 'reset_password', 'seguridad', $event->user ? get_class($event->user) : null, $userId, null);
        });
    }
}
