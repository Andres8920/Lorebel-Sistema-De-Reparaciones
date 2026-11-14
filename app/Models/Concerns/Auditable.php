<?php

namespace App\Models\Concerns;

use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Trait de trazabilidad (Anexo Auditoría).
 *
 * Al aplicarse a un modelo, registra automáticamente en la tabla `auditorias`
 * cada alta, modificación, baja (lógica) y restauración, guardando:
 * usuario, fecha y hora, IP, valores originales y valores nuevos.
 * Así puede reconstruirse el estado original del elemento y todas sus transformaciones.
 */
trait Auditable
{
    /** Campos que no aportan a la trazabilidad y se excluyen del registro. */
    protected static array $camposNoAuditables = ['created_at', 'updated_at', 'remember_token', 'password'];

    public static function bootAuditable(): void
    {
        static::created(function ($modelo) {
            $modelo->registrarAuditoria('alta', null, $modelo->valoresAuditables($modelo->getAttributes()));
        });

        static::updated(function ($modelo) {
            $cambios = $modelo->valoresAuditables($modelo->getChanges());
            if (empty($cambios)) {
                return;
            }
            $anteriores = [];
            foreach (array_keys($cambios) as $campo) {
                $anteriores[$campo] = $modelo->getRawOriginal($campo);
                // Se descartan asignaciones que no alteran el valor (ej.: null → null)
                if ((string) $anteriores[$campo] === (string) $cambios[$campo] && ($anteriores[$campo] === null) === ($cambios[$campo] === null)) {
                    unset($anteriores[$campo], $cambios[$campo]);
                }
            }
            if (empty($cambios)) {
                return;
            }
            // Una baja lógica (SoftDeletes) también dispara "updated" sobre deleted_at: se registra como baja.
            if (array_key_exists('deleted_at', $cambios) && count($cambios) === 1) {
                return;
            }
            $modelo->registrarAuditoria('modificacion', $anteriores, $cambios);
        });

        static::deleted(function ($modelo) {
            $modelo->registrarAuditoria('baja', $modelo->valoresAuditables($modelo->getRawOriginal()), null);
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($modelo) {
                $modelo->registrarAuditoria('restauracion', null, ['deleted_at' => null]);
            });
        }
    }

    protected function valoresAuditables(array $valores): array
    {
        return collect($valores)
            ->except(array_merge(static::$camposNoAuditables, $this->camposExcluidosAuditoria ?? []))
            ->all();
    }

    protected function registrarAuditoria(string $accion, ?array $anteriores, ?array $nuevos): void
    {
        $usuario = Auth::user();

        Auditoria::create([
            'user_id'            => $usuario?->id,
            'usuario_nombre'     => $usuario?->name ?? 'Sistema',
            'accion'             => $accion,
            'entidad'            => class_basename($this),
            'entidad_id'         => $this->getKey(),
            'valores_anteriores' => $anteriores,
            'valores_nuevos'     => $nuevos,
            'ip'                 => Request::ip(),
            'user_agent'         => substr((string) Request::userAgent(), 0, 255),
        ]);
    }

    public function auditorias()
    {
        return Auditoria::where('entidad', class_basename($this))
            ->where('entidad_id', $this->getKey())
            ->orderBy('created_at')
            ->orderBy('id');
    }
}
