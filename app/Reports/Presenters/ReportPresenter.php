<?php

namespace App\Reports\Presenters;

use Carbon\Carbon;

class ReportPresenter
{
    public static function format($value, ?string $format): string
    {
        if (is_null($value) || $value === "") {
            return "-";
        }

        return match ($format) {
            "date"  => self::formatDate($value),
            "money" => number_format((float) $value, 2, ",", "."),
            "badge" => self::formatBadge($value),
            default => (string) $value,
        };
    }

    public static function formatDate($value): string
    {
        try {
            if ($value instanceof \DateTimeInterface) {
                return $value->format("d/m/Y");
            }
            return Carbon::parse($value)->format("d/m/Y");
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    public static function badgeClass(string $value): string
    {
        $v = strtolower(trim($value));

        return match (true) {
            in_array($v, ["disponible", "activo", "activa", "entregado", "aprobada", "aprobado", "finalizado", "devuelto"])
                => "badge-estado badge-estado-disponible",
            in_array($v, ["prestado", "pendiente", "extendido", "en proceso", "en_proceso"])
                => "badge-estado badge-estado-prestado",
            in_array($v, ["en reparacion", "en_reparacion", "vencido", "rechazado", "rechazada", "desechado"])
                => "badge-estado badge-estado-en-reparacion",
            in_array($v, ["en bodega", "en_bodega", "inactivo", "inactiva", "cancelado", "cancelada"])
                => "badge-estado badge-estado-en-bodega",
            default => "badge-estado badge-estado-default",
        };
    }

    public static function formatBadge(string $value): string
    {
        return $value;
    }
}
