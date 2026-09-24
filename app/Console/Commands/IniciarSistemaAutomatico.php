<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class IniciarSistemaAutomatico extends Command
{
    protected $signature = 'sistema:iniciar';
    protected $description = 'Inicia el worker de colas y el scheduler automáticamente';

    public function handle()
    {
        $this->info('🚀 Iniciando Sistema Automático...');
        $this->info('   ├─ Worker de colas');
        $this->info('   └─ Scheduler de tareas');
        $this->newLine();

        $basePath = base_path();
        $php = PHP_BINARY;

        // Worker de colas
        $worker = new Process(
            [$php, 'artisan', 'queue:work', '--tries=3', '--timeout=180', '--sleep=3', '--max-time=3600'],
            $basePath
        );
        $worker->setTimeout(null);
        $worker->start();

        // Scheduler
        $scheduler = new Process(
            [$php, 'artisan', 'schedule:work'],
            $basePath
        );
        $scheduler->setTimeout(null);
        $scheduler->start();

        $this->info('✅ Sistema corriendo. Presiona Ctrl+C para detener.');
        $this->newLine();

        // Bucle infinito para mantener los procesos vivos
        while (true) {
            if (!$worker->isRunning()) {
                $this->warn('⚠️ Worker detenido. Reiniciando...');
                $worker = new Process(
                    [$php, 'artisan', 'queue:work', '--tries=3', '--timeout=180', '--sleep=3', '--max-time=3600'],
                    $basePath
                );
                $worker->setTimeout(null);
                $worker->start();
            }

            if (!$scheduler->isRunning()) {
                $this->warn('⚠️ Scheduler detenido. Reiniciando...');
                $scheduler = new Process(
                    [$php, 'artisan', 'schedule:work'],
                    $basePath
                );
                $scheduler->setTimeout(null);
                $scheduler->start();
            }

            sleep(5);
        }
    }
}