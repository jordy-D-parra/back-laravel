<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LimpiarColaAtascada extends Command
{
    protected $signature = 'cola:limpiar {--minutos=10}';
    protected $description = 'Libera jobs atascados por más de X minutos';

    public function handle()
    {
        $minutos = (int) $this->option('minutos');
        $limite = now()->subMinutes($minutos)->timestamp;

        $liberados = DB::table('jobs')
            ->where('reserved_at', '<', $limite)
            ->update(['reserved_at' => null]);

        $this->info("✅ {$liberados} jobs liberados (atascados > {$minutos} min)");
    }
}