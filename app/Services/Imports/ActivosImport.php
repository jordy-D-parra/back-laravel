<?php

declare(strict_types=1);

namespace App\Services\Importacion;

/**
 * Servicio de inferencia de categorías.
 *
 * Dado el nombre de un modelo (y opcionalmente su marca), intenta adivinar
 * a qué categoría pertenece usando un diccionario de palabras clave.
 *
 * Ejemplo:
 *   "Latitude 5540"   → "Laptop"
 *   "ThinkCentre M90" → "Desktop"
 *   "EcoTank L3250"   → "Impresora"
 *
 * Si no puede inferir nada, devuelve null.
 */
class CategoriaInferida
{
    /**
     * Normalización de categorías: clave interna => nombre canónico en BD.
     * El nombre canónico es el que se va a buscar/crear en la tabla `categorias`.
     */
    private const CATEGORIAS_CANONICAS = [
        'laptop'    => 'Laptop',
        'desktop'   => 'Computadora de Escritorio',
        'monitor'   => 'Monitor',
        'impresora' => 'Impresora',
        'proyector' => 'Proyector',
        'servidor'  => 'Servidor',
        'tablet'    => 'Tablet',
        'red'       => 'Router / Switch',
        'ups'       => 'UPS / Regulador',
    ];

    /**
     * Diccionario de palabras clave por categoría interna.
     *
     * Orden IMPORTANTE: las claves más específicas van primero para evitar
     * falsos positivos. Por ejemplo, "thinkpad" (laptop) debe evaluarse
     * antes que "thinkcentre" (desktop) si comparten prefijo, aunque aquí
     * son mutuamente excluyentes por el nombre completo.
     *
     * Los términos se comparan en minúsculas y sin acentos.
     */
    private const DICCIONARIO = [
        'laptop' => [
            'latitude', 'thinkpad', 'macbook', 'laptop', 'portatil',
            'notebook', 'elitebook', 'probook', 'ideapad', 'pavilion',
            'inspiron', 'vostro', 'xps', 'zenbook', 'vivobook',
            'aspire', 'nitro', 'legion', 'yoga', 'surface',
        ],
        'desktop' => [
            'optiplex', 'thinkcentre', 'desktop', 'pc', 'torre',
            'elitedesk', 'prodesk', 'precision', 'workstation',
            'imac', 'all-in-one', 'aio', 'mini pc', 'minipc',
        ],
        'monitor' => [
            'monitor', 'pantalla', 'display', 'screen',
            'ultrawide', 'ultrasharp',
        ],
        'impresora' => [
            'impresora', 'printer', 'laserjet', 'ecotank', 'deskjet',
            'pixma', 'workforce', 'multifuncional', 'plotter',
            'inkjet', 'laser',
        ],
        'proyector' => [
            'proyector', 'projector', 'videobeam', 'beam',
        ],
        'servidor' => [
            'servidor', 'server', 'poweredge', 'proliant',
            'rack', 'blade', 'tower server',
        ],
        'tablet' => [
            'tablet', 'ipad', 'tab', 'galaxy tab',
        ],
        'red' => [
            'switch', 'router', 'catalyst', 'access point',
            'firewall', 'hub', 'patch panel',
        ],
        'ups' => [
            'ups', 'regulador', 'back-ups', 'backups',
            'supresor', 'protector',
        ],
    ];

    /**
     * Punto de entrada principal.
     *
     * @param string|null $nombreModelo   Nombre del modelo (ej: "Latitude 5540")
     * @param string|null $nombreMarca    Nombre de la marca (ej: "Dell"), opcional
     * @return string|null                Nombre canónico de la categoría, o null si no se puede inferir
     */
    public function inferir(?string $nombreModelo, ?string $nombreMarca = null): ?string
    {
        // Si no hay nombre de modelo, no se puede inferir
        if (empty($nombreModelo)) {
            return null;
        }

        // Normalizar: minúsculas + sin acentos
        $texto = $this->normalizar($nombreModelo);

        // Si viene marca, la concatenamos para ampliar la búsqueda
        // (algunos fabricantes incluyen la categoría en la marca)
        if (!empty($nombreMarca)) {
            $texto .= ' ' . $this->normalizar($nombreMarca);
        }

        // Recorrer el diccionario buscando coincidencias
        foreach (self::DICCIONARIO as $categoriaInterna => $palabrasClave) {
            foreach ($palabrasClave as $palabra) {
                if ($this->contienePalabra($texto, $palabra)) {
                    return self::CATEGORIAS_CANONICAS[$categoriaInterna] ?? null;
                }
            }
        }

        return null;
    }

    /**
     * Devuelve el diccionario completo (útil para depuración o tests).
     *
     * @return array<string, array<int, string>>
     */
    public function getDiccionario(): array
    {
        return self::DICCIONARIO;
    }

    /**
     * Devuelve el mapeo de categorías internas => nombres canónicos.
     *
     * @return array<string, string>
     */
    public function getCategoriasCanonicas(): array
    {
        return self::CATEGORIAS_CANONICAS;
    }

    // ============================================================
    // HELPERS PRIVADOS
    // ============================================================

    /**
     * Normaliza un texto: minúsculas, sin acentos, sin espacios extra.
     */
    private function normalizar(string $texto): string
    {
        // Pasar a minúsculas
        $texto = mb_strtolower(trim($texto), 'UTF-8');

        // Quitar acentos (para que "portátil" coincida con "portatil")
        $texto = $this->quitarAcentos($texto);

        // Reemplazar múltiples espacios por uno
        $texto = preg_replace('/\s+/', ' ', $texto) ?? $texto;

        return $texto;
    }

    /**
     * Quita los acentos de un string UTF-8.
     */
    private function quitarAcentos(string $texto): string
    {
        $reemplazos = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
            'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u',
            'ã' => 'a', 'õ' => 'o',
            'ñ' => 'n', 'ç' => 'c',
        ];

        return strtr($texto, $reemplazos);
    }

    /**
     * Verifica si $texto contiene $palabra como palabra completa o
     * como parte de una palabra compuesta.
     *
     * Estrategia:
     *   - Para palabras cortas (<= 2 caracteres) exigimos coincidencia EXACTA
     *     como palabra completa (ej: "pc" no debe matchear "spectrum").
     *   - Para palabras largas (>= 3 caracteres) permitimos substring
     *     (ej: "latitude" matchea "latitude5540").
     */
    private function contienePalabra(string $texto, string $palabra): bool
    {
        $palabra = $this->normalizar($palabra);

        if (mb_strlen($palabra) <= 2) {
            // Coincidencia exacta como palabra completa
            return (bool) preg_match(
                '/(?<![\p{L}\p{N}])' . preg_quote($palabra, '/') . '(?![\p{L}\p{N}])/u',
                $texto
            );
        }

        // Coincidencia por substring
        return str_contains($texto, $palabra);
    }
}