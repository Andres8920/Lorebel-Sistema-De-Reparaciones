<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;
use PDOException;

class CreateDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear la base de datos si no existe';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $database = config('database.connections.mysql.database');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        try {
            // Conectar sin especificar base de datos
            $pdo = new PDO(
                "mysql:host={$host};port={$port}",
                $username,
                $password
            );

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Crear la base de datos
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            $this->info("✓ Base de datos '{$database}' creada exitosamente!");

            return Command::SUCCESS;
        } catch (PDOException $e) {
            $this->error("✗ Error al crear la base de datos: " . $e->getMessage());
            $this->warn("Asegúrate de que MySQL esté corriendo y las credenciales sean correctas.");

            return Command::FAILURE;
        }
    }
}
