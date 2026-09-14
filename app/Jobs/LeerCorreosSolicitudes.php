<?php

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

    public function handle(CorreoImapService $service): void
    {
        $count = $service->leerCorreosNuevos();
        Log::info("LeerCorreosSolicitudes: {$count} correos procesados");
    }
}