<?php

namespace Database\Seeders;

use App\Models\Tecnico;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TecnicoSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Ejecuta el seeder de técnicos
     */
    public function run(): void
    {
        $tecnicos = [
            [
                'nombre' => 'Carlos',
                'apellido' => 'Méndez',
                'email' => 'carlos.mendez@lorebel.com',
                'telefono' => '011-5555-1001',
                'especialidad' => 'Smartphones y Tablets',
                'activo' => true,
            ],
            [
                'nombre' => 'Laura',
                'apellido' => 'Fernández',
                'email' => 'laura.fernandez@lorebel.com',
                'telefono' => '011-5555-1002',
                'especialidad' => 'Laptops y Computadoras',
                'activo' => true,
            ],
            [
                'nombre' => 'Miguel',
                'apellido' => 'Torres',
                'email' => 'miguel.torres@lorebel.com',
                'telefono' => '011-5555-1003',
                'especialidad' => 'Tablets y Consolas',
                'activo' => true,
            ],
            [
                'nombre' => 'Ana',
                'apellido' => 'Ramírez',
                'email' => 'ana.ramirez@lorebel.com',
                'telefono' => '011-5555-1004',
                'especialidad' => 'General',
                'activo' => true,
            ],
        ];

        foreach ($tecnicos as $tecnico) {
            Tecnico::create($tecnico);
        }
    }
}
