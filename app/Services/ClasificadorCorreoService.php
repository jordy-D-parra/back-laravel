<?php

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
    ];

    /**
     * Clasifica un correo como 'soporte' o 'solicitud'.
     * Si no coincide con ninguno, devuelve 'solicitud' por defecto.
     */
    public function clasificar(string $asunto, string $cuerpo = ''): string
    {
        $texto = mb_strtolower($asunto . ' ' . $cuerpo, 'UTF-8');

        // Contar coincidencias
        $scoreSoporte = $this->contarCoincidencias($texto, $this->palabrasSoporte);
        $scoreSolicitud = $this->contarCoincidencias($texto, $this->palabrasSolicitud);

        // Prioridad: si el asunto menciona soporte/técnico, va a soporte
        $asuntoLower = mb_strtolower($asunto, 'UTF-8');
        if ($this->contarCoincidencias($asuntoLower, $this->palabrasSoporte) > 0) {
            return 'soporte';
        }

        if ($this->contarCoincidencias($asuntoLower, $this->palabrasSolicitud) > 0) {
            return 'solicitud';
        }

        // Si el cuerpo tiene más señales de soporte que de solicitud
        if ($scoreSoporte > $scoreSolicitud) {
            return 'soporte';
        }

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
}