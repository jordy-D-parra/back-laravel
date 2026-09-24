<?php

namespace App\Jobs;

use App\Models\Solicitud;
use App\Models\Usuario;
use App\Models\Responsable;
use App\Services\NotificacionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EnviarNotificacionSolicitudCreada implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];
    public int $timeout = 120;
    public int $uniqueFor = 3600;

    public string $evento = 'creada';

    public function __construct(
        public int $solicitudId,
        string $evento = 'creada'
    ) {
        $this->evento = $evento;
    }

    public function uniqueId(): string
    {
        return 'solicitud-' . $this->solicitudId . '-' . $this->evento;
    }

    public function handle(NotificacionService $notificacionService): void
    {
        $evento = $this->evento ?? 'creada';
        if (empty($evento)) {
            $evento = 'creada';
        }
        $this->evento = $evento;

        Log::info("🔔 [Job Solicitud] Procesando notificación", [
            'solicitud_id' => $this->solicitudId,
            'evento' => $evento,
            'intento' => $this->attempts(),
        ]);

        $solicitud = Solicitud::with([
            'responsable',
            'usuario.trabajador',
            'departamento',
            'institucion',
            'detalles',
        ])->find($this->solicitudId);

        if (!$solicitud) {
            Log::warning("⚠️ [Job Solicitud] Solicitud #{$this->solicitudId} no encontrada");
            return;
        }

        $this->notificarResponsable($solicitud, $notificacionService);
        $this->notificarSolicitante($solicitud, $notificacionService);
        $this->notificarAdmins($solicitud, $notificacionService);

        Log::info("✅ [Job Solicitud] Notificación completada", [
            'solicitud_id' => $solicitud->id,
            'evento' => $evento,
        ]);
    }

    protected function notificarResponsable(Solicitud $solicitud, NotificacionService $service): void
    {
        $responsable = $this->obtenerResponsableConEmail($solicitud);

        if (!$responsable) {
            Log::warning("⚠️ [Job Solicitud] No se encontró responsable con email", [
                'solicitud_id' => $solicitud->id,
                'responsable_id' => $solicitud->responsable_id,
                'departamento_id' => $solicitud->departamento_id,
                'institucion_id' => $solicitud->institucion_id,
            ]);
            return;
        }

        Log::info("📧 [Job Solicitud] Enviando notificación al responsable", [
            'solicitud_id' => $solicitud->id,
            'responsable' => $responsable->nombre,
            'email' => $responsable->email,
            'evento' => $this->evento,
        ]);

        $entidad = $solicitud->tipo_solicitante === 'interno'
            ? ($solicitud->departamento?->nombre ?? 'Departamento')
            : ($solicitud->institucion?->nombre ?? 'Institución');

        $solicitante = $solicitud->usuario?->trabajador
            ? trim($solicitud->usuario->trabajador->nombre . ' ' . $solicitud->usuario->trabajador->apellido)
            : ($solicitud->usuario?->usuario ?? 'Usuario');

        $fechaRequerida = $solicitud->fecha_requerida
            ? \Carbon\Carbon::parse($solicitud->fecha_requerida)->format('d/m/Y')
            : 'No especificada';

        $fechaFin = $solicitud->fecha_fin_estimada
            ? \Carbon\Carbon::parse($solicitud->fecha_fin_estimada)->format('d/m/Y')
            : 'No especificada';

        $itemsTexto = '';
        foreach ($solicitud->detalles as $det) {
            $itemsTexto .= " • " . ($det->descripcion_personalizada ?? 'Item')
                . " (Cant: {$det->cantidad_solicitada})\n";
        }

        if ($this->evento === 'creada') {
            $titulo = '📋 Nueva Solicitud de Préstamo - En Proceso';
            $mensaje =
                "📋 NUEVA SOLICITUD DE PRÉSTAMO REGISTRADA\n\n" .
                "Estimado/a {$responsable->nombre},\n\n" .
                "Se ha registrado una nueva solicitud de préstamo donde usted figura como responsable.\n\n" .
                "📌 Detalles:\n" .
                " • Código: SOL-" . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) . "\n" .
                " • Entidad: {$entidad}\n" .
                " • Solicitante: {$solicitante}\n" .
                " • Fecha requerida: {$fechaRequerida}\n" .
                " • Fecha fin estimada: {$fechaFin}\n" .
                " • Prioridad: " . strtoupper($solicitud->prioridad ?? 'normal') . "\n\n" .
                "📦 Items solicitados:\n" . ($itemsTexto ?: " • Sin items registrados\n") . "\n" .
                "📝 Justificación:\n {$solicitud->justificacion}\n\n" .
                "⏳ Estado actual: EN PROCESO (pendiente de aprobación)\n\n" .
                "Le notificaremos cuando la solicitud sea APROBADA o RECHAZADA.\n\n" .
                "Gracias.";
        } elseif ($this->evento === 'aprobada') {
            $titulo = '✅ Solicitud de Préstamo APROBADA';
            $mensaje =
                "✅ SOLICITUD DE PRÉSTAMO APROBADA\n\n" .
                "Estimado/a {$responsable->nombre},\n\n" .
                "Le informamos que su solicitud de préstamo ha sido APROBADA.\n\n" .
                "📌 Detalles:\n" .
                " • Código: SOL-" . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) . "\n" .
                " • Entidad: {$entidad}\n" .
                " • Solicitante: {$solicitante}\n" .
                " • Fecha requerida: {$fechaRequerida}\n" .
                " • Fecha fin estimada: {$fechaFin}\n" .
                " • Prioridad: " . strtoupper($solicitud->prioridad ?? 'normal') . "\n\n" .
                "📦 Items:\n" . ($itemsTexto ?: " • Sin items\n") . "\n" .
                "📄 Se ha generado el Acta de Retiro que encontrará adjunta en otro correo.\n\n" .
                "📍 Próximos pasos:\n" .
                " 1. Descargue e imprima el Acta de Retiro\n" .
                " 2. Fírmela\n" .
                " 3. Preséntese en el Departamento de Informática con su cédula\n\n" .
                "Gracias.";
        } elseif ($this->evento === 'rechazada') {
            $titulo = '❌ Solicitud de Préstamo RECHAZADA';
            $mensaje =
                "❌ SOLICITUD DE PRÉSTAMO RECHAZADA\n\n" .
                "Estimado/a {$responsable->nombre},\n\n" .
                "Lamentamos informarle que su solicitud de préstamo ha sido RECHAZADA.\n\n" .
                "📌 Detalles:\n" .
                " • Código: SOL-" . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) . "\n" .
                " • Entidad: {$entidad}\n" .
                " • Solicitante: {$solicitante}\n\n" .
                "📝 Motivo del rechazo:\n" . ($solicitud->observaciones ?? 'No especificado') . "\n\n" .
                "Si tiene dudas, contacte al Departamento de Informática.\n\n" .
                "Gracias.";
        } elseif ($this->evento === 'cancelada') {
            $titulo = '🚫 Solicitud de Préstamo CANCELADA';
            $mensaje =
                "🚫 SOLICITUD DE PRÉSTAMO CANCELADA\n\n" .
                "Estimado/a {$responsable->nombre},\n\n" .
                "La solicitud ha sido CANCELADA.\n\n" .
                "📌 Detalles:\n" .
                " • Código: SOL-" . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) . "\n" .
                " • Entidad: {$entidad}\n" .
                " • Motivo: " . ($solicitud->observaciones ?? 'Cancelada por el solicitante') . "\n\n" .
                "Si no solicitó esta cancelación, contacte al Departamento de Informática.";
        } else {
            $titulo = '📋 Actualización de Solicitud';
            $mensaje = "Actualización de la solicitud #{$solicitud->id}";
        }

        try {
            $service->enviarAResponsable(
                $responsable->email,
                $responsable->nombre,
                $titulo,
                $mensaje,
                'solicitud',
                route('admin.solicitudes.index')
            );

            Log::info("📧 ✅ Correo enviado al responsable", [
                'solicitud_id' => $solicitud->id,
                'email' => $responsable->email,
                'evento' => $this->evento,
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ [Job Solicitud] Error al enviar al responsable", [
                'solicitud_id' => $solicitud->id,
                'email' => $responsable->email,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function notificarSolicitante(Solicitud $solicitud, NotificacionService $service): void
    {
        $usuario = $solicitud->usuario;

        if (!$usuario || !$usuario->email) {
            Log::info("ℹ️ [Job Solicitud] Solicitante sin email, omitiendo", [
                'solicitud_id' => $solicitud->id,
            ]);
            return;
        }

        $titulo = match($this->evento) {
            'creada' => '📋 Solicitud de Préstamo Registrada - En Proceso',
            'aprobada' => '✅ Su Solicitud fue Aprobada',
            'rechazada' => '❌ Su Solicitud fue Rechazada',
            'cancelada' => '🚫 Su Solicitud fue Cancelada',
            default => '📋 Actualización de Solicitud',
        };

        $mensaje = "Estimado/a " . ($usuario->trabajador?->nombre ?? $usuario->usuario) . ",\n\n";

        if ($this->evento === 'creada') {
            $mensaje .= "Su solicitud #SOL-" . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) . " ha sido registrada.\n";
            $mensaje .= "Estado: EN PROCESO\n\n";
            $mensaje .= "Le notificaremos cuando sea aprobada o rechazada.\n\nGracias.";
        } elseif ($this->evento === 'aprobada') {
            $mensaje .= "¡Buenas noticias! Su solicitud #SOL-" . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) . " ha sido APROBADA.\n";
            $mensaje .= "Pronto recibirá el Acta de Retiro.\n\nGracias.";
        } elseif ($this->evento === 'rechazada') {
            $mensaje .= "Su solicitud #SOL-" . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) . " ha sido RECHAZADA.\n";
            $mensaje .= "Motivo: " . ($solicitud->observaciones ?? 'No especificado') . "\n\n";
            $mensaje .= "Puede realizar una nueva solicitud corrigiendo los aspectos mencionados.\n\nGracias.";
        } elseif ($this->evento === 'cancelada') {
            $mensaje .= "Su solicitud #SOL-" . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) . " ha sido CANCELADA.\n\nGracias.";
        }

        try {
            $service->enviarAUsuario(
                $usuario,
                $titulo,
                $mensaje,
                'solicitud',
                route('admin.solicitudes.index')
            );
        } catch (\Throwable $e) {
            Log::error("❌ [Job Solicitud] Error al notificar solicitante: " . $e->getMessage());
        }
    }

    protected function notificarAdmins(Solicitud $solicitud, NotificacionService $service): void
    {
        $admins = Usuario::whereHas('rol', function ($q) {
            $q->whereIn('nombre', ['admin', 'super_admin']);
        })->where('status', 'activo')->with('trabajador')->get();

        $mensaje = "Solicitud #SOL-" . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) . ".\n";
        $mensaje .= "Evento: " . strtoupper($this->evento) . "\n";
        $mensaje .= "Prioridad: " . strtoupper($solicitud->prioridad) . "\n";

        foreach ($admins as $admin) {
            if (!$admin->email) continue;

            try {
                $service->enviarAUsuario(
                    $admin,
                    '📋 ' . ucfirst($this->evento) . ' Solicitud de Préstamo',
                    $mensaje,
                    'solicitud',
                    route('admin.solicitudes.index')
                );
            } catch (\Throwable $e) {
                Log::error("❌ [Job Solicitud] Error al notificar admin: " . $e->getMessage());
            }
        }
    }

    protected function obtenerResponsableConEmail(Solicitud $solicitud): ?Responsable
    {
        $responsable = $solicitud->responsable;

        if ($responsable && !empty($responsable->email)) {
            Log::info("✅ [Job Solicitud] Usando responsable directo", [
                'solicitud_id' => $solicitud->id,
                'responsable_id' => $responsable->id,
                'email' => $responsable->email,
            ]);
            return $responsable;
        }

        if ($solicitud->departamento_id) {
            $respDepto = Responsable::where('departamento_id', $solicitud->departamento_id)
                ->where('activo', true)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->first();

            if ($respDepto) {
                Log::info("✅ [Job Solicitud] Usando responsable del departamento", [
                    'solicitud_id' => $solicitud->id,
                    'departamento_id' => $solicitud->departamento_id,
                    'email' => $respDepto->email,
                ]);
                return $respDepto;
            }
        }

        if ($solicitud->institucion_id) {
            $respInst = Responsable::where('institucion_id', $solicitud->institucion_id)
                ->whereNull('departamento_id')
                ->where('activo', true)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->first();

            if ($respInst) {
                Log::info("✅ [Job Solicitud] Usando responsable institucional", [
                    'solicitud_id' => $solicitud->id,
                    'institucion_id' => $solicitud->institucion_id,
                    'email' => $respInst->email,
                ]);
                return $respInst;
            }

            $respCualquiera = Responsable::where('institucion_id', $solicitud->institucion_id)
                ->where('activo', true)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->first();

            if ($respCualquiera) {
                Log::info("✅ [Job Solicitud] Usando cualquier responsable de la institución", [
                    'solicitud_id' => $solicitud->id,
                    'email' => $respCualquiera->email,
                ]);
                return $respCualquiera;
            }
        }

        return null;
    }
}