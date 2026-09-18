<?php
// app/Jobs/LeerCorreosSolicitudes.php

namespace App\Jobs;

use App\Services\CorreoImapService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LeerCorreosSolicitudes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutos máximo
    public $tries = 2;      // 2 intentos máximo

    public function handle(CorreoImapService $service): void
    {
        Log::info('🔄 [LeerCorreosSolicitudes] Iniciando lectura de correos...');

        try {
            $count = $service->leerCorreosNuevos();
            Log::info("✅ [LeerCorreosSolicitudes] {$count} correos procesados");
        } catch (\Throwable $e) {
            Log::error('❌ [LeerCorreosSolicitudes] Error: ' . $e->getMessage());
            throw $e;
        }
    }
}