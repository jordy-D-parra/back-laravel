<?php
// app/Services/ClasificadorCorreoService.php

namespace App\Services;

class ClasificadorCorreoService
{
    /**
     * Palabras clave que identifican un correo de SOPORTE TÉCNICO
     */
    protected array $palabrasSoporte = [
        'problema tecnico',
        'problema técnico',
        'reparacion',
        'reparación',
        'soporte tecnico',
        'soporte técnico',
        'averia',
        'avería',
        'mantenimiento',
        'falla tecnica',
        'falla técnica',
        'no enciende',
        'no funciona',
        'dañado',
        'danado',
        'revisar equipo',
        'arreglar',
        'tecnico',
        'técnico',
        'ficha de soporte',
        'orden de reparacion',
        'orden de reparación',
        'requiere reparacion',
        'requiere reparación',
        'necesita reparacion',
        'necesita reparación',
        'equipo dañado',
        'equipo danado',
        'falla en',
        'presenta falla',
        'presenta problema',
        'no prende',
        'no arranca',
        'esta dañado',
        'está dañado',
        'se daño',
        'se dañó',
        'reparar equipo',
        'equipo de computo dañado',
        'computadora dañada',
        'laptop dañada',
        'impresora dañada',
        'no carga',
        'pantalla rota',
        'teclado dañado',
        'mouse dañado',
        'disco duro dañado',
        'memoria ram dañada',
        'bateria dañada',
        'batería dañada',
        'sobrecalentamiento',
        'se apaga solo',
        'se reinicia solo',
        'pantalla azul',
        'virus',
        'malware',
        'no da imagen',
        'no da video',
        'no da sonido',
    ];

    /**
     * Palabras clave que identifican un correo de SOLICITUD DE PRÉSTAMO
     */
    protected array $palabrasSolicitud = [
        'solicitud de prestamo',
        'solicitud de préstamo',
        'prestamo',
        'préstamo',
        'solicito equipos',
        'solicito prestado',
        'requiero equipos',
        'necesito equipos',
        'solicitud de equipos',
        'prestar equipo',
        'solicito prestamo',
        'solicito préstamo',
        'quiero solicitar',
        'deseo solicitar',
        'solicitar prestamo',
        'solicitar préstamo',
        'solicito computadora',
        'necesito computadora',
        'requiero computadora',
        'solicito laptop',
        'necesito laptop',
        'requiero laptop',
        'solicito proyector',
        'necesito proyector',
        'requiero proyector',
        'solicito monitor',
        'necesito monitor',
        'requiero monitor',
        'solicito impresora',
        'necesito impresora',
        'requiero impresora',
        'solicito tablet',
        'necesito tablet',
        'requiero tablet',
        'solicito mouse',
        'necesito mouse',
        'requiero mouse',
        'solicito teclado',
        'necesito teclado',
        'requiero teclado',
        'solicito cargador',
        'necesito cargador',
        'requiero cargador',
        'solicito cable',
        'necesito cable',
        'requiero cable',
        'solicito adaptador',
        'necesito adaptador',
        'requiero adaptador',
        'equipo para',
        'equipos para',
        'computadora para',
        'laptop para',
        'proyector para',
        'monitor para',
        'impresora para',
        'tablet para',
        'prestamo de equipo',
        'préstamo de equipo',
        'prestamo de equipos',
        'préstamo de equipos',
        'solicitud de prestamo de equipos',
        'solicitud de préstamo de equipos',
        'necesito para el',
        'necesito para la',
        'requiero para el',
        'requiero para la',
        'solicito para el',
        'solicito para la',
        'solicito con urgencia',
        'necesito con urgencia',
        'requiero con urgencia',
        'urge prestamo',
        'urge préstamo',
        'urge equipo',
        'urge computadora',
        'urge laptop',
        'solicitud urgente',
        'prestamo urgente',
        'préstamo urgente',
    ];

    /**
     * Clasifica un correo como 'soporte' o 'solicitud'.
     *
     * ✅ REGLA PRINCIPAL: El ASUNTO tiene prioridad absoluta.
     * Si el asunto menciona soporte/técnico → soporte.
     * Si el asunto menciona solicitud/préstamo → solicitud.
     * Si el asunto no es concluyente, se analiza el cuerpo.
     */
    public function clasificar(string $asunto, string $cuerpo = ''): string
    {
        $asuntoLower = mb_strtolower($asunto, 'UTF-8');
        $cuerpoLower = mb_strtolower($cuerpo, 'UTF-8');
        $textoCompleto = $asuntoLower . ' ' . $cuerpoLower;

        // ✅ 1. PRIORIDAD MÁXIMA: Analizar el ASUNTO primero
        $scoreAsuntoSoporte = $this->contarCoincidencias($asuntoLower, $this->palabrasSoporte);
        $scoreAsuntoSolicitud = $this->contarCoincidencias($asuntoLower, $this->palabrasSolicitud);

        // Si el asunto tiene más palabras de soporte que de solicitud
        if ($scoreAsuntoSoporte > 0 && $scoreAsuntoSoporte > $scoreAsuntoSolicitud) {
            return 'soporte';
        }

        // Si el asunto tiene más palabras de solicitud que de soporte
        if ($scoreAsuntoSolicitud > 0 && $scoreAsuntoSolicitud > $scoreAsuntoSoporte) {
            return 'solicitud';
        }

        // Si hay empate en el asunto (ambos > 0), analizar el cuerpo
        if ($scoreAsuntoSoporte > 0 && $scoreAsuntoSolicitud > 0 && $scoreAsuntoSoporte === $scoreAsuntoSolicitud) {
            // Continuar con el análisis del cuerpo
        }

        // ✅ 2. Si el asunto no es concluyente, analizar el cuerpo completo
        $scoreSoporte = $this->contarCoincidencias($textoCompleto, $this->palabrasSoporte);
        $scoreSolicitud = $this->contarCoincidencias($textoCompleto, $this->palabrasSolicitud);

        if ($scoreSoporte > $scoreSolicitud) {
            return 'soporte';
        }

        if ($scoreSolicitud > $scoreSoporte) {
            return 'solicitud';
        }

        // ✅ 3. Si hay empate o no hay coincidencias, analizar patrones adicionales
        // Si el asunto contiene "solicitud" o "préstamo" sin palabras de soporte
        if (str_contains($asuntoLower, 'solicitud') || str_contains($asuntoLower, 'prestamo') || str_contains($asuntoLower, 'préstamo')) {
            return 'solicitud';
        }

        // Si el asunto contiene "soporte" o "reparación" sin palabras de solicitud
        if (str_contains($asuntoLower, 'soporte') || str_contains($asuntoLower, 'reparacion') || str_contains($asuntoLower, 'reparación')) {
            return 'soporte';
        }

        // ✅ 4. Por defecto: solicitud (comportamiento actual)
        return 'solicitud';
    }

    /**
     * Extrae la fecha requerida de entrega del cuerpo del correo.
     */
    public function extraerFechaRequerida(string $cuerpo): ?string
    {
        $patrones = [
            '/(?:fecha\s+requerida|entregar?\s+(?:el|para)|necesito\s+(?:para|el)|para\s+el|entrega\s+(?:el|para)|debe\s+estar\s+(?:el|para))\s*[:\-]?\s*(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})/i',
            '/(?:fecha\s+requerida|fecha\s+de\s+entrega|para\s+entregar)\s*[:\-]?\s*(\d{4}-\d{2}-\d{2})/i',
        ];

        foreach ($patrones as $patron) {
            if (preg_match($patron, $cuerpo, $m)) {
                return $this->normalizarFecha($m[1]);
            }
        }

        return null;
    }

    /**
     * Extrae datos generales del cuerpo (equipo, problema, etc.)
     */
    public function extraerDatosSoporte(string $cuerpo): array
    {
        $datos = [
            'equipo' => null,
            'marca' => null,
            'modelo' => null,
            'serial' => null,
            'problema' => null,
            'fecha_requerida' => null,
        ];

        // Equipo
        if (preg_match('/(?:equipo|dispositivo|computadora|laptop|pc)\s*[:\-]\s*(.+?)(?:\n|$)/i', $cuerpo, $m)) {
            $datos['equipo'] = trim($m[1]);
        }

        // Marca
        if (preg_match('/(?:marca)\s*[:\-]\s*(.+?)(?:\n|$)/i', $cuerpo, $m)) {
            $datos['marca'] = trim($m[1]);
        }

        // Modelo
        if (preg_match('/(?:modelo)\s*[:\-]\s*(.+?)(?:\n|$)/i', $cuerpo, $m)) {
            $datos['modelo'] = trim($m[1]);
        }

        // Serial
        if (preg_match('/(?:serial|n[°º]?\s*de\s*serie|s\/n)\s*[:\-]\s*(.+?)(?:\n|$)/i', $cuerpo, $m)) {
            $datos['serial'] = trim($m[1]);
        }

        // Problema
        if (preg_match('/(?:problema|falla|aver[ií]a|diagn[oó]stico)\s*[:\-]\s*(.+?)(?:\n\n|$)/is', $cuerpo, $m)) {
            $datos['problema'] = trim($m[1]);
        }

        // Fecha requerida
        $datos['fecha_requerida'] = $this->extraerFechaRequerida($cuerpo);

        return $datos;
    }

    /**
     * Extrae datos de una solicitud de préstamo
     */
    public function extraerDatosSolicitud(string $cuerpo): array
    {
        $datos = [
            'prioridad' => 'normal',
            'fecha_requerida' => null,
            'fecha_fin_estimada' => null,
            'justificacion' => null,
            'items' => [],
            'entidad' => null,
        ];

        // Prioridad
        if (preg_match('/prioridad[:\s]+(baja|normal|alta|urgente)/i', $cuerpo, $m)) {
            $datos['prioridad'] = strtolower($m[1]);
        } elseif (preg_match('/(urgente|urge)/i', $cuerpo)) {
            $datos['prioridad'] = 'urgente';
        }

        // Fecha requerida
        if (preg_match('/(?:fecha\s+requerida|necesito\s+para|para\s+el)[:\s]+(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})/i', $cuerpo, $m)) {
            $datos['fecha_requerida'] = $this->normalizarFecha($m[1]);
        }

        // Justificación
        if (preg_match('/(?:justificaci[oó]n|motivo|raz[oó]n)[:\s]+(.+?)(?:\n\n|$)/is', $cuerpo, $m)) {
            $datos['justificacion'] = trim($m[1]);
        } else {
            $datos['justificacion'] = substr(trim($cuerpo), 0, 500);
        }

        // Items
        if (preg_match_all('/(\d+)\s+(?:x\s+)?([a-záéíóúñ\s]+?)(?:\n|,|\.|;|$)/iu', $cuerpo, $matches)) {
            foreach ($matches[1] as $i => $cantidad) {
                $descripcion = trim($matches[2][$i]);
                if (strlen($descripcion) > 3 && strlen($descripcion) < 100) {
                    $datos['items'][] = [
                        'cantidad' => (int) $cantidad,
                        'descripcion' => $descripcion,
                        'tipo_item' => $this->detectarTipoItem($descripcion),
                    ];
                }
            }
        }

        return $datos;
    }

    protected function contarCoincidencias(string $texto, array $palabras): int
    {
        $contador = 0;
        foreach ($palabras as $palabra) {
            if (str_contains($texto, $palabra)) {
                $contador++;
            }
        }
        return $contador;
    }

    protected function normalizarFecha(string $fecha): ?string
    {
        try {
            $fecha = str_replace('/', '-', $fecha);
            $partes = explode('-', $fecha);
            if (count($partes) === 3) {
                if (strlen($partes[0]) === 4) {
                    return sprintf('%04d-%02d-%02d', $partes[0], $partes[1], $partes[2]);
                }
                return sprintf('%04d-%02d-%02d', $partes[2], $partes[1], $partes[0]);
            }
        } catch (\Exception $e) {
            return null;
        }
        return null;
    }

    protected function detectarTipoItem(string $descripcion): string
    {
        $descripcion = strtolower($descripcion);
        $activos = ['laptop', 'computadora', 'pc', 'desktop', 'monitor', 'proyector', 'impresora', 'tablet', 'servidor', 'router', 'switch'];
        $componentes = ['mouse', 'teclado', 'cable', 'cargador', 'ram', 'disco', 'batería', 'bateria', 'adaptador', 'usb', 'audífonos', 'audifonos'];

        foreach ($activos as $a) {
            if (strpos($descripcion, $a) !== false) return 'activo';
        }
        foreach ($componentes as $c) {
            if (strpos($descripcion, $c) !== false) return 'componente';
        }
        return 'activo';
    }
}