<?php

namespace App\Reports;

use App\Reports\Contracts\ReportInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

abstract class BaseReport implements ReportInterface
{
    protected array $params = [];

    public function withParams(array $params): static
    {
        $this->params = $params;
        return $this;
    }

    public function params(): array
    {
        return $this->params;
    }

    /**
     * Permiso adicional requerido.
     * Si retorna null, basta con "ver-reportes".
     */
    public function requiredPermission(): ?string
    {
        return null;
    }

    /**
     * Texto legible de los filtros aplicados.
     */
    public function filtrosAplicados(): string
    {
        $aplicados = [];

        foreach ($this->filters() as $name => $f) {
            $valor = $this->params[$name] ?? null;

            if (is_array($valor)) {
                $from = $valor["from"] ?? null;
                $to   = $valor["to"]   ?? null;
                if ($from && $to) {
                    $aplicados[] = $f["label"] . ": " .
                        Carbon::parse($from)->format("d/m/Y") . " -> " .
                        Carbon::parse($to)->format("d/m/Y");
                }
                continue;
            }

            if (!empty($valor)) {
                $label = $f["options"][$valor] ?? $valor;
                $aplicados[] = $f["label"] . ": " . $label;
            }
        }

        return $aplicados ? implode(" | ", $aplicados) : "Sin filtros aplicados";
    }

    /**
     * Codigo unico del documento.
     */
    public function codigoDocumento(): string
    {
        $prefijo = "REP-" . strtoupper($this->key());
        $fecha   = now()->format("Ymd");
        $rand    = strtoupper(Str::random(6));

        return "{$prefijo}-{$fecha}-{$rand}";
    }

    /**
     * Hash corto para verificacion.
     */
    public function hashDocumento(): string
    {
        $payload = $this->key() . "|" . json_encode($this->params) . "|" . now()->timestamp;
        return strtoupper(substr(sha1($payload), 0, 10));
    }

    /**
     * Nombre de archivo sugerido.
     */
    public function filename(): string
    {
        return Str::slug($this->title()) . "-" . now()->format("Ymd_His");
    }

    /**
     * Paginado (sin cache, es liviano).
     */
    public function paginate(int $perPage = 15)
    {
        return $this->query($this->params)->paginate($perPage);
    }

       /**
     * Todos los registros (sin cache — para exportaciones).
     *
     * Se desactivó el cache porque cachear una Collection Eloquent
     * requiere 'serializable_classes' => true en config/cache.php,
     * lo cual es un riesgo de seguridad. La exportación es una acción
     * puntual y no necesita cache.
     */
    public function all()
    {
        return $this->query($this->params)->get();
    }

    /**
     * Llave de cache unica por reporte + params + usuario.
     */
    protected function cacheKey(): string
    {
        return "report:" . $this->key() . ":" . md5(json_encode($this->params) . "|" . auth()->id());
    }

    /**
     * Invalida el cache de este reporte.
     */
    public function forgetCache(): void
    {
        Cache::forget($this->cacheKey());
    }

    /**
     * Nombre del usuario que genera.
     */
    public function usuarioGenera(): string
    {
        $u = auth()->user();
        if (!$u) return "Sistema";
        return $u->nombre_completo ?? $u->usuario ?? "Sistema";
    }
}