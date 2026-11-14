<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'activo',
    ];

    public const ROLES = [
        'administrador' => 'Administrador',
        'recepcion'     => 'Recepción / Caja',
        'tecnico'       => 'Técnico',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function esAdministrador(): bool
    {
        return $this->rol === 'administrador';
    }

    /** ¿El usuario tiene alguno de los roles indicados? */
    public function tieneRol(string ...$roles): bool
    {
        return in_array($this->rol, $roles, true);
    }

    public function getRolTextoAttribute(): string
    {
        return self::ROLES[$this->rol] ?? $this->rol;
    }
}
