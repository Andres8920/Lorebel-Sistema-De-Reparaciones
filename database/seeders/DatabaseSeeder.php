<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * (Sin WithoutModelEvents: los eventos generan el código de pago y la trazabilidad inicial.)
     */
    public function run(): void
    {
        // Usuarios por rol (contraseña inicial: Lorebel2026!)
        User::create(['name' => 'Administrador Lorebel', 'email' => 'admin@lorebel.com', 'password' => 'Lorebel2026!', 'rol' => 'administrador']);
        User::create(['name' => 'Recepción / Caja', 'email' => 'caja@lorebel.com', 'password' => 'Lorebel2026!', 'rol' => 'recepcion']);
        User::create(['name' => 'Técnico de Taller', 'email' => 'tecnico@lorebel.com', 'password' => 'Lorebel2026!', 'rol' => 'tecnico']);

        $this->call([
            TecnicoSeeder::class,
            ClienteSeeder::class,
            DemoCobrosSeeder::class,
        ]);
    }
}
