<?php

namespace App\Reports\Definitions;

use App\Models\Prestamo;
use App\Reports\BaseReport;
use Illuminate\Database\Eloquent\Builder;

class PrestamosVencidosReport extends BaseReport
{
    public function key(): string            { return "prestamos-vencidos"; }
    public function title(): string          { return "Prestamos Vencidos y Por Vencer"; }
    public function description(): string    { return "Prestamos con fecha de devolucion vencida o que vencen en los proximos 7 dias."; }
    public function category(): string       { return "prestamos"; }
    public function icon(): string           { return "alert"; }
    public function pdfOrientation(): string { return "landscape"; }

    public function filters(): array
    {
        return [
            "modo" => [
                "type"    => "select",
                "label"   => "Modo",
                "options" => [
                    "vencidos"   => "Solo Vencidos",
                    "por_vencer" => "Por Vencer (prox. 7 dias)",
                    "ambos"      => "Ambos",
                ],
                "default" => "ambos",
            ],
            "departamento_id" => [
                "type"   => "select",
                "label"  => "Departamento",
                "source" => "departamentos",
            ],
            "institucion_id" => [
                "type"   => "select",
                "label"  => "Institucion",
                "source" => "instituciones",
            ],
        ];
    }

    public function query(array $params): Builder
    {
        $hoy    = now()->startOfDay();
        $limite = $hoy->copy()->addDays(7);

        $modo = $params["modo"] ?? "ambos";

        $q = Prestamo::query()
            ->with([
                "departamento:id,nombre",
                "institucion:id,nombre",
                "responsableReceptor:id,nombre,telefono,email",
            ])
            ->withCount("detalles")
            ->whereIn("estado", ["entregado", "extendido"])
            ->whereNull("fecha_devolucion_real");

        // Filtrar segun modo
        if ($modo === "vencidos") {
            $q->whereDate("fecha_devolucion_esperada", "<", $hoy->toDateString());
        } elseif ($modo === "por_vencer") {
            $q->whereDate("fecha_devolucion_esperada", ">=", $hoy->toDateString())
              ->whereDate("fecha_devolucion_esperada", "<=", $limite->toDateString());
        } else {
            $q->whereDate("fecha_devolucion_esperada", "<=", $limite->toDateString());
        }

        $q->when($params["departamento_id"] ?? null, fn($q, $v) => $q->where("departamento_id", $v))
          ->when($params["institucion_id"] ?? null,  fn($q, $v) => $q->where("institucion_id", $v))
          ->orderBy("fecha_devolucion_esperada", "asc");

        return $q;
    }

    public function columns(): array
    {
        return [
            ["key" => "codigo",                       "label" => "Codigo",      "width" => "10%"],
            ["key" => "destino_nombre",               "label" => "Destino",     "width" => "20%"],
            ["key" => "responsableReceptor.nombre",   "label" => "Responsable", "width" => "16%"],
            ["key" => "responsableReceptor.telefono", "label" => "Telefono",    "width" => "12%"],
            ["key" => "fecha_devolucion_esperada",    "label" => "F. Tope",     "width" => "10%", "format" => "date"],
            ["key" => "dias_restantes",               "label" => "Dias",        "width" => "8%",  "align" => "center"],
            ["key" => "detalles_count",               "label" => "Items",       "width" => "6%",  "align" => "center"],
            ["key" => "estado",                       "label" => "Estado",      "width" => "10%", "format" => "badge"],
            ["key" => "responsableReceptor.email",    "label" => "Email",       "width" => "18%"],
        ];
    }

    public function summary(array $params): array
    {
        $hoy    = now()->startOfDay();
        $limite = $hoy->copy()->addDays(7);

        $q = $this->query($params);

        $vencidos = (clone $q)
            ->whereDate("fecha_devolucion_esperada", "<", $hoy->toDateString())
            ->count();

        $porVencer = (clone $q)
            ->whereDate("fecha_devolucion_esperada", ">=", $hoy->toDateString())
            ->whereDate("fecha_devolucion_esperada", "<=", $limite->toDateString())
            ->count();

        return [
            "Total en alerta" => (clone $q)->count(),
            "Vencidos"        => $vencidos,
            "Por vencer"      => $porVencer,
        ];
    }

    public function signatures(): array
    {
        return [
            ["role" => "Elabora", "nombre" => "Departamento de Informatica"],
            ["role" => "Revisa",  "nombre" => "Supervisor"],
        ];
    }
}
