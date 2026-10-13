<?php

// app/Jobs/EnviarNotificacionFichaSoporte.php

namespace App\Jobs;

use App\Models\FichaSoporte;
use App\Models\Usuario;
use App\Models\Responsable;
use App\Models\Activo;
use App\Models\Notificacion;
use App\Services\NotificacionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EnviarNotificacionFichaSoporte implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300];
    public int $timeout = 120;

    public string $evento = 'creada';
    public ?int $correoId = null;

    public function __construct(
        public int $fichaId,
        string $evento = 'creada',
        ?int $correoId = null
    ) {
        $this->evento = $evento;
        $this->correoId = $correoId;
    }

    public function handle(NotificacionService $notificacionService): void
    {
        $evento = $this->evento ?? 'creada';
        if (empty($evento)) {
            $evento = 'creada';
        }
        $this->evento = $evento;

        Log::info("🔔 [Job Soporte] Iniciando notificación", [
            'ficha_id' => $this->fichaId,
            'evento' => $evento,
            'correo_id' => $this->correoId,
            'intento' => $this->attempts(),
        ]);

        $ficha = FichaSoporte::with([
            'activo.modelo.marca',
            'activo.modelo.categoria',
            'activo.institucion',
            'activo.departamento',
            'activo.responsable',
            'tecnico.trabajador',
            'detalles.componente',
            'correo',
        ])->find($this->fichaId);

        if (!$ficha) {
            Log::warning("⚠️ [Job Soporte] Ficha #{$this->fichaId} no encontrada");
            return;
        }

        // 1. Notificar al responsable (CRÍTICO)
        $this->notificarResponsable($ficha, $notificacionService);

        // 2. Notificar al técnico
        $this->notificarTecnico($ficha, $notificacionService);

        // ❌ 3. Notificar a los admins → ELIMINADO

        Log::info("✅ [Job Soporte] Notificación completada", [
            'ficha_id' => $ficha->id,
            'evento' => $evento,
        ]);
    }

    protected function notificarResponsable(FichaSoporte $ficha, NotificacionService $service): void
    {
        $responsable = $this->obtenerResponsableConEmail($ficha->activo);

        if (!$responsable) {
            Log::warning("⚠️ [Job Soporte] No hay responsable con email", [
                'ficha_id' => $ficha->id,
                'activo_id' => $ficha->activo_id,
                'departamento_id' => $ficha->activo?->departamento_id,
                'institucion_id' => $ficha->activo?->institucion_id,
            ]);
            return;
        }

        $titulo = $this->tituloResponsable();
        $mensaje = $this->mensajeResponsable($ficha, $responsable);

        Log::info("📧 [Job Soporte] Enviando notificación al responsable", [
            'ficha_id' => $ficha->id,
            'responsable' => $responsable->nombre,
            'email' => $responsable->email,
            'evento' => $this->evento,
        ]);

        try {
            Notificacion::create([
                'usuario_id' => null,
                'tipo' => 'soporte',
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'url' => null,
                'fecha_envio' => now(),
                'leida' => false,
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ [Job Soporte] Error al registrar notificación externa", [
                'ficha_id' => $ficha->id,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $service->enviarAResponsable(
                $responsable->email,
                $responsable->nombre,
                $titulo,
                $mensaje,
                'soporte',
                route('admin.soporte.index')
            );

            Log::info("📧 ✅ Correo enviado al responsable", [
                'ficha_id' => $ficha->id,
                'evento' => $this->evento,
                'email' => $responsable->email,
            ]);

        } catch (\Throwable $e) {
            Log::error("❌ [Job Soporte] Error al enviar al responsable", [
                'ficha_id' => $ficha->id,
                'email' => $responsable->email,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function notificarTecnico(FichaSoporte $ficha, NotificacionService $service): void
    {
        if (!$ficha->tecnico_id) return;

        $tecnico = Usuario::with('trabajador')->find($ficha->tecnico_id);

        if (!$tecnico) return;

        Log::info("📧 [Job Soporte] Enviando notificación al técnico", [
            'ficha_id' => $ficha->id,
            'tecnico_id' => $tecnico->id,
            'evento' => $this->evento,
        ]);

        try {
            $service->enviarAUsuario(
                $tecnico,
                $this->tituloTecnico(),
                $this->mensajeTecnico($ficha),
                'soporte',
                route('admin.soporte.index')
            );
        } catch (\Throwable $e) {
            Log::error("❌ [Job Soporte] Error al notificar técnico: " . $e->getMessage());
        }
    }

    // ❌ MÉTODO ELIMINADO: notificarAdmins()
    // protected function notificarAdmins(FichaSoporte $ficha, NotificacionService $service): void { ... }

    protected function obtenerResponsableConEmail(?Activo $activo): ?Responsable
    {
        if (!$activo) {
            Log::warning("⚠️ [Job Soporte] Activo es null, no se puede buscar responsable");
            return null;
        }

        if ($activo->responsable && !empty($activo->responsable->email)) {
            Log::info("✅ [Job Soporte] Usando responsable directo del activo", [
                'activo_id' => $activo->id,
                'responsable_id' => $activo->responsable->id,
                'email' => $activo->responsable->email,
            ]);
            return $activo->responsable;
        }

        if ($activo->departamento_id) {
            $respDepto = Responsable::where('departamento_id', $activo->departamento_id)
                ->where('activo', true)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->first();

            if ($respDepto) {
                Log::info("✅ [Job Soporte] Usando responsable del departamento del activo", [
                    'activo_id' => $activo->id,
                    'departamento_id' => $activo->departamento_id,
                    'email' => $respDepto->email,
                ]);
                return $respDepto;
            }

            Log::warning("⚠️ [Job Soporte] Departamento sin responsable con email. NO se hará fallback a institución.", [
                'activo_id' => $activo->id,
                'departamento_id' => $activo->departamento_id,
            ]);
            return null;
        }

        if ($activo->institucion_id) {
            $respInst = Responsable::where('institucion_id', $activo->institucion_id)
                ->whereNull('departamento_id')
                ->where('activo', true)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->first();

            if ($respInst) {
                Log::info("✅ [Job Soporte] Usando responsable institucional", [
                    'activo_id' => $activo->id,
                    'institucion_id' => $activo->institucion_id,
                    'email' => $respInst->email,
                ]);
                return $respInst;
            }
        }

        Log::warning("❌ [Job Soporte] No se encontró responsable con email", [
            'activo_id' => $activo->id,
            'departamento_id' => $activo->departamento_id,
            'institucion_id' => $activo->institucion_id,
        ]);

        return null;
    }

    protected function nombreEquipo(FichaSoporte $ficha): string
    {
        $marca = $ficha->activo?->modelo?->marca?->nombre ?? 'N/A';
        $modelo = $ficha->activo?->modelo?->nombre ?? 'N/A';
        $serial = $ficha->activo?->serial ?? 'N/A';
        return "{$marca} {$modelo} (Serial: {$serial})";
    }

    protected function tituloResponsable(): string
    {
        return match ($this->evento) {
            'creada' => '🔄 Solicitud de Reparación en Proceso',
            'aceptada' => '✅ Solicitud de Reparación ACEPTADA',
            'rechazada' => '❌ Solicitud de Reparación RECHAZADA',
            'finalizada' => '✅ Reparación Finalizada - RETIRAR EQUIPO',
            'eliminada' => '⚠️ Solicitud de Reparación Cancelada',
            default => 'Actualización de Solicitud de Reparación',
        };
    }

    protected function tituloTecnico(): string
    {
        return match ($this->evento) {
            'creada' => '🔧 Nueva Ficha de Soporte Asignada',
            'aceptada' => '✅ Ficha de Soporte ACEPTADA',
            'finalizada' => '✅ Ficha de Soporte Finalizada',
            default => 'Actualización de Ficha de Soporte',
        };
    }

    // ❌ MÉTODO ELIMINADO: tituloAdmin()
    // protected function tituloAdmin(): string { ... }

    protected function mensajeResponsable(FichaSoporte $ficha, Responsable $responsable): string
    {
        $base = "Estimado/a {$responsable->nombre},\n\n";
        $equipo = $this->nombreEquipo($ficha);
        $institucion = $ficha->activo?->institucion?->nombre ?? 'No especificada';
        $detalle = "📌 Ficha #{$ficha->id}\n🔧 Equipo: {$equipo}\n🏢 Institución: {$institucion}\n\n";

        return match ($this->evento) {
            'creada' =>
                $base . "🔄 SU SOLICITUD DE REPARACIÓN ESTÁ EN PROCESO\n\n" . $detalle .
                "📝 Diagnóstico:\n" . ($ficha->diagnostico ?? 'N/A') . "\n\n" .
                "📅 Fecha requerida: " . ($ficha->fecha_requerida_entrega ? $ficha->fecha_requerida_entrega->format('d/m/Y') : 'No especificada') . "\n\n" .
                "📍 Lugar de reparación:\nGobernación del Estado Yaracuy\nDepartamento de Informática\nSan Felipe, Edo. Yaracuy\n\n" .
                "📌 Instrucciones:\n1. Lleve el equipo.\n2. Presente este correo.\n3. Le notificaremos al ser ACEPTADA o RECHAZADA.\n\nGracias.",

            'aceptada' =>
                $base . "✅ SU SOLICITUD HA SIDO ACEPTADA\n\n" . $detalle .
                "📅 Aceptada: " . ($ficha->fecha_aceptacion ? $ficha->fecha_aceptacion->format('d/m/Y H:i') : now()->format('d/m/Y H:i')) . "\n" .
                "👨‍🔧 Técnico: " . ($ficha->tecnico_nombre ?? 'Pendiente') . "\n\n" .
                "Procederemos con la reparación.\n\nGracias.",

            'rechazada' =>
                $base . "❌ SU SOLICITUD HA SIDO RECHAZADA\n\n" . $detalle .
                "📝 Motivo:\n" . ($ficha->motivo_rechazo ?? 'No especificado') . "\n\n" .
                "📍 Puede retirar su equipo en:\nGobernación del Estado Yaracuy\nDepartamento de Informática\nSan Felipe, Edo. Yaracuy\n\nGracias.",

            'finalizada' =>
                $base . "✅ REPARACIÓN FINALIZADA\n\n" . $detalle .
                "📝 Trabajo:\n" . ($ficha->trabajo_realizado ?? 'No especificado') . "\n\n" .
                "📍 Puede retirar el equipo en:\nGobernación del Estado Yaracuy\nDepartamento de Informática\nSan Felipe, Edo. Yaracuy\n\n" .
                "📌 Instrucciones:\n1. Presente este correo.\n2. Horario: L-V 8AM-4PM.\n\nGracias.",

            'eliminada' =>
                $base . "⚠️ SOLICITUD CANCELADA\n\n" . $detalle .
                "📍 Si ya llevó el equipo, puede retirarlo en:\nGobernación del Estado Yaracuy\nDepartamento de Informática\n\nGracias.",

            default => $base . $detalle,
        };
    }

    protected function mensajeTecnico(FichaSoporte $ficha): string
    {
        $equipo = $this->nombreEquipo($ficha);
        $institucion = $ficha->activo?->institucion?->nombre ?? 'No especificada';

        return match ($this->evento) {
            'creada' => "Se te asignó la ficha #{$ficha->id}.\n\n🔧 Equipo: {$equipo}\n🏢 Institución: {$institucion}\n📝 Diagnóstico: " . substr($ficha->diagnostico ?? 'N/A', 0, 200) . "\n📅 Fecha requerida: " . ($ficha->fecha_requerida_entrega ? $ficha->fecha_requerida_entrega->format('d/m/Y') : 'N/A') . "\n\nProcede con la reparación.",
            'aceptada' => "Ficha #{$ficha->id} ACEPTADA. Procede con la reparación.",
            'finalizada' => "Ficha #{$ficha->id} FINALIZADA.\n\n🔧 Equipo: {$equipo}\n📝 Trabajo: " . substr($ficha->trabajo_realizado ?? 'N/A', 0, 200),
            default => "Actualización de la ficha #{$ficha->id}.",
        };
    }

    // ❌ MÉTODO ELIMINADO: mensajeAdmin()
    // protected function mensajeAdmin(FichaSoporte $ficha): string { ... }
}