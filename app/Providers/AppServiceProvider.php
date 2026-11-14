<?php

namespace App\Providers;

use App\Models\RegistroSesion;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;

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
        Paginator::useBootstrapFive();

        // -----------------------------------------------------------------
        // Anexo Auditoría: registro de login, logout e intentos fallidos.
        // -----------------------------------------------------------------
        Event::listen(Login::class, fn (Login $e) => $this->registrarSesion('login', $e->user?->id, $e->user?->email));
        Event::listen(Logout::class, fn (Logout $e) => $this->registrarSesion('logout', $e->user?->id, $e->user?->email));
        Event::listen(Failed::class, fn (Failed $e) => $this->registrarSesion(
            'login_fallido',
            $e->user?->id,
            $e->credentials['email'] ?? 'desconocido'
        ));
    }

    private function registrarSesion(string $evento, ?int $userId, ?string $email): void
    {
        RegistroSesion::create([
            'user_id'    => $userId,
            'email'      => $email ?? 'desconocido',
            'evento'     => $evento,
            'ip'         => Request::ip(),
            'user_agent' => substr((string) Request::userAgent(), 0, 255),
        ]);
    }
}
