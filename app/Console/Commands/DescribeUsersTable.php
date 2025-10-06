<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DescribeUsersTable extends Command
{
    protected $signature = 'db:describe-users {--json : Output as JSON}';
    protected $description = 'Describe la estructura actual de la tabla users (columnas, tipos, null, default, keys)';

    public function handle(): int
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if (!Schema::hasTable('users')) {
            $this->error('La tabla users no existe en la base de datos actual.');
            return self::FAILURE;
        }

        try {
            $rows = [];

            if ($driver === 'mysql') {
                // MySQL: usar DESCRIBE
                $rows = DB::select("DESCRIBE `users`");
                $normalized = collect($rows)->map(function ($r) {
                    return [
                        'column' => $r->Field ?? null,
                        'type' => $r->Type ?? null,
                        'nullable' => (isset($r->Null) && strtoupper($r->Null) === 'YES') ? 'YES' : 'NO',
                        'key' => $r->Key ?? '',
                        'default' => isset($r->Default) ? (is_string($r->Default) ? $r->Default : json_encode($r->Default)) : null,
                        'extra' => $r->Extra ?? '',
                    ];
                })->toArray();
            } elseif ($driver === 'sqlite') {
                // SQLite: PRAGMA table_info
                $rows = DB::select("PRAGMA table_info('users')");
                $normalized = collect($rows)->map(function ($r) {
                    return [
                        'column' => $r->name ?? null,
                        'type' => $r->type ?? null,
                        'nullable' => (isset($r->notnull) && (int)$r->notnull === 0) ? 'YES' : 'NO',
                        'key' => ((isset($r->pk) && (int)$r->pk === 1) ? 'PRI' : ''),
                        'default' => $r->dflt_value ?? null,
                        'extra' => '',
                    ];
                })->toArray();
            } else {
                // Fallback genérico: intentar INFORMATION_SCHEMA (MySQL/PostgreSQL) cuando sea posible
                // Intento para MySQL/MariaDB via information_schema (por si DESCRIBE no está permitido)
                try {
                    $dbName = $connection->getDatabaseName();
                    $rows = DB::select(
                        'SELECT COLUMN_NAME as column_name, COLUMN_TYPE as column_type, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA 
                         FROM information_schema.columns WHERE table_schema = ? AND table_name = ? ORDER BY ORDINAL_POSITION',
                        [$dbName, 'users']
                    );
                    $normalized = collect($rows)->map(function ($r) {
                        return [
                            'column' => $r->column_name ?? null,
                            'type' => $r->column_type ?? null,
                            'nullable' => (isset($r->IS_NULLABLE) && strtoupper($r->IS_NULLABLE) === 'YES') ? 'YES' : 'NO',
                            'key' => $r->COLUMN_KEY ?? '',
                            'default' => isset($r->COLUMN_DEFAULT) ? (is_string($r->COLUMN_DEFAULT) ? $r->COLUMN_DEFAULT : json_encode($r->COLUMN_DEFAULT)) : null,
                            'extra' => $r->EXTRA ?? '',
                        ];
                    })->toArray();
                } catch (\Throwable $e) {
                    $this->warn('No se pudo usar information_schema. Mostrando listado básico de columnas.');
                    $cols = Schema::getColumnListing('users');
                    $normalized = collect($cols)->map(fn ($c) => [
                        'column' => $c,
                        'type' => null,
                        'nullable' => null,
                        'key' => '',
                        'default' => null,
                        'extra' => '',
                    ])->toArray();
                }
            }

            if ($this->option('json')) {
                $this->line(json_encode([
                    'connection' => $connection->getName(),
                    'driver' => $driver,
                    'database' => $connection->getDatabaseName(),
                    'table' => 'users',
                    'columns' => $normalized,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            } else {
                $this->info("Conexion: {$connection->getName()} | Driver: {$driver} | DB: {$connection->getDatabaseName()}");
                $this->table(['Column', 'Type', 'Nullable', 'Key', 'Default', 'Extra'], $normalized);
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error describiendo la tabla users: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
