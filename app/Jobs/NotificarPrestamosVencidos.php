<?php
// app/Jobs/NotificarPrestamosVencidos.php

namespace App\Jobs;

use App\Models\Prestamo;
use App\Models\Usuario;
use App\Services\NotificacionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotificarPrestamosVencidos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Ejecuta el job: busca préstamos vencidos y notifica al responsable
     * (persona a la que se le prestó el equipo) por su correo electrónico.
     */
    public function handle(NotificacionService $notificacionService): void
    {
        Log::info('🔍 [NotificarPrestamosVencidos] Iniciando revisión de préstamos vencidos...');

        // ============================================================
        // 1. OBTENER PRÉSTAMOS VENCIDOS
        // ============================================================
        // Solo préstamos ENTREGADOS o EXTENDIDOS (aún no devueltos)
        // cuya fecha_devolucion_esperada ya pasó.
        $prestamosVencidos = Prestamo::with([
            'responsableReceptor',   // ← Persona que RECIBIÓ el equipo
            'responsableEmisor',     // ← Persona que ENTREGÓ el equipo (Informática)
            'departamento',
            'institucion',
            'detalles.prestable',
        ])
        ->whereIn('estado', ['entregado', 'extendido'])
        ->whereNull('fecha_devolucion_real')
        ->whereDate('fecha_devolucion_esperada', '<', Carbon::today())
        ->get();

        Log::info("📋 [NotificarPrestamosVencidos] Préstamos vencidos encontrados: {$prestamosVencidos->count()}");

        if ($prestamosVencidos->isEmpty()) {
            return;
        }

        // ============================================================
        // 2. RECORRER CADA PRÉSTAMO Y NOTIFICAR
        // ============================================================
        foreach ($prestamosVencidos as $prestamo) {

            // ------------------------------------------------------------
            // Evitar notificar 2 veces el mismo día para el mismo préstamo
            // ------------------------------------------------------------
            $cacheKey = 'prestamo_vencido_notificado_' . $prestamo->id . '_' . Carbon::today()->format('Y-m-d');

            if (cache()->has($cacheKey)) {
                Log::info("⏭️ [NotificarPrestamosVencidos] Préstamo #{$prestamo->id} ya notificado hoy. Se omite.");
                continue;
            }

            // ------------------------------------------------------------
            // Obtener el RESPONSABLE que recibió el equipo
            // ------------------------------------------------------------
            $responsable = $prestamo->responsableReceptor;

            if (!$responsable) {
                Log::warning("⚠️ [NotificarPrestamosVencidos] Préstamo #{$prestamo->id} no tiene responsable receptor. Se omite.");
                continue;
            }

            if (!$responsable->email) {
                Log::warning("⚠️ [NotificarPrestamosVencidos] Responsable '{$responsable->nombre}' del préstamo #{$prestamo->id} no tiene email. Se omite.");
                continue;
            }

            // ------------------------------------------------------------
            // Calcular datos del préstamo
            // ------------------------------------------------------------
            $diasVencidos = Carbon::today()->diffInDays($prestamo->fecha_devolucion_esperada);
            $destino = $prestamo->destino_nombre ?? 'No especificado';

            // Lista de items prestados
            $itemsLista = '';
            foreach ($prestamo->detalles as $detalle) {
                $nombreItem = $detalle->nombre_item ?? 'Item';
                $cantidad = $detalle->cantidad ?? 1;
                $itemsLista .= "  • {$nombreItem} (Cant: {$cantidad})\n";
            }

            // ------------------------------------------------------------
            // Construir el mensaje
            // ------------------------------------------------------------
            $mensaje =
                "⚠️ PRÉSTAMO VENCIDO\n\n" .
                "Estimado/a {$responsable->nombre},\n\n" .
                "Le recordamos que el préstamo con código {$prestamo->codigo} ha superado la fecha tope de devolución.\n\n" .
                "📌 Detalles del préstamo:\n" .
                "  • Código: {$prestamo->codigo}\n" .
                "  • Destino: {$destino}\n" .
                "  • Fecha de préstamo: " . ($prestamo->fecha_prestamo ? $prestamo->fecha_prestamo->format('d/m/Y') : 'N/A') . "\n" .
                "  • Fecha tope de devolución: " . ($prestamo->fecha_devolucion_esperada ? $prestamo->fecha_devolucion_esperada->format('d/m/Y') : 'N/A') . "\n" .
                "  • Días vencidos: {$diasVencidos}\n\n" .
                "📦 Items prestados:\n" . $itemsLista . "\n" .
                "Por favor, devuelva el equipo lo antes posible al Departamento de Informática de la Gobernación del Estado Yaracuy.\n\n" .
                "Si ya realizó la devolución, ignore este mensaje.\n\n" .
                "Gracias.";

            // ------------------------------------------------------------
            // Enviar notificación al responsable (correo externo)
            // ------------------------------------------------------------
            try {
                $notificacionService->enviarAResponsable(
                    $responsable->email,
                    $responsable->nombre,
                    '⚠️ Préstamo Vencido - Devolver Equipo',
                    $mensaje,
                    'prestamo'
                );

                // ------------------------------------------------------------
                // Si el responsable tiene un usuario en el sistema, también
                // le enviamos notificación interna (campanita)
                // ------------------------------------------------------------
                $usuarioReceptor = Usuario::whereHas('trabajador', function ($q) use ($responsable) {
                    $q->where('email', $responsable->email);
                })->first();

                if ($usuarioReceptor) {
                    $notificacionService->enviarAUsuario(
                        $usuarioReceptor,
                        '⚠️ Préstamo Vencido - Devolver Equipo',
                        $mensaje,
                        'prestamo',
                        route('admin.prestamos.index')
                    );
                }

                // ------------------------------------------------------------
                // Notificar también a los administradores (opcional)
                // ------------------------------------------------------------
                $admins = Usuario::whereHas('rol', function ($q) {
                    $q->where('nombre', 'admin');
                })->where('status', 'activo')->with('trabajador')->get();

                $mensajeAdmin =
                    "⚠️ PRÉSTAMO VENCIDO\n\n" .
                    "El préstamo {$prestamo->codigo} está vencido desde el " .
                    ($prestamo->fecha_devolucion_esperada ? $prestamo->fecha_devolucion_esperada->format('d/m/Y') : 'N/A') .
                    " ({$diasVencidos} días).\n\n" .
                    "📌 Detalles:\n" .
                    "  • Código: {$prestamo->codigo}\n" .
                    "  • Destino: {$destino}\n" .
                    "  • Responsable: {$responsable->nombre} ({$responsable->email})\n" .
                    "  • Items: " . $prestamo->detalles->count() . "\n\n" .
                    "Se ha enviado un recordatorio al responsable.";

                foreach ($admins as $admin) {
                    if ($admin->email) {
                        $notificacionService->enviarAUsuario(
                            $admin,
                            '⚠️ Préstamo Vencido',
                            $mensajeAdmin,
                            'prestamo',
                            route('admin.prestamos.index')
                        );
                    }
                }

                // ------------------------------------------------------------
                // Marcar en caché para no re-notificar hoy
                // ------------------------------------------------------------
                cache()->put($cacheKey, true, now()->addDays(2));

                Log::info("✅ [NotificarPrestamosVencidos] Notificación enviada", [
                    'prestamo_id'   => $prestamo->id,
                    'codigo'        => $prestamo->codigo,
                    'responsable'   => $responsable->nombre,
                    'email'         => $responsable->email,
                    'dias_vencidos' => $diasVencidos,
                ]);

            } catch (\Exception $e) {
                Log::error("❌ [NotificarPrestamosVencidos] Error al notificar préstamo #{$prestamo->id}: " . $e->getMessage());
            }
        }

        Log::info('🏁 [NotificarPrestamosVencidos] Revisión completada.');
    }
}