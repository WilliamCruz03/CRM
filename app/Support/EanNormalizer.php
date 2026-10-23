<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Normalizador de códigos de barras (EAN) cortos.
 *
 * TODO (R7): Reemplazar por el helper/servicio de padding existente del CRM
 * cuando se localice. La interfaz pública (normalizar / largoEan) está diseñada
 * para que el reemplazo sea de una sola línea en el cuerpo de cada método.
 */
final class EanNormalizer
{
    public const CACHE_KEY_LARGO = 'reportes.largo_ean';
    public const CACHE_TTL_SEGUNDOS = 3600;
    public const LARGO_FALLBACK = 13;

    /**
     * Normaliza un EAN corto rellenando con ceros a la izquierda hasta el largo
     * real del catálogo. Si no es numérico, lo devuelve tal cual (trim).
     *
     * Si el EAN ya tiene el largo esperado o es más largo, se devuelve tal cual
     * (nunca truncamos).
     */
    public static function normalizar(string $ean): string
    {
        $ean = trim($ean);

        if ($ean === '' || ! ctype_digit($ean)) {
            return $ean;
        }

        // Si ya tiene largo "de EAN completo" (>= 8), no tocamos nada.
        // Los EANs cortos que el usuario tipea son de 1-6 dígitos.
        if (strlen($ean) >= 8) {
            return $ean;
        }

        $largo = self::largoEan();

        return str_pad($ean, $largo, '0', STR_PAD_LEFT);
    }

    /**
     * Largo del EAN según el catálogo maestro.
     * Usa el largo MÁS FRECUENTE (moda) de los EANs del catálogo, no el máximo,
     * para no romperse si hay outliers (EANs especiales, datos sucios).
     * Cacheado 1h. Conexión: sqlsrvM (fp_central_matriz).
     */
    public static function largoEan(): int
    {
        return (int) Cache::remember(
            self::CACHE_KEY_LARGO,
            self::CACHE_TTL_SEGUNDOS,
            static function (): int {
                $row = DB::connection('sqlsrvM')->selectOne(
                    'SELECT TOP 1 LEN(ean) AS l, COUNT(*) AS n
                     FROM catalogo_general
                     WHERE ean IS NOT NULL AND LEN(ean) > 0
                     GROUP BY LEN(ean)
                     ORDER BY n DESC'
                );

                return (int) ($row->l ?? self::LARGO_FALLBACK);
            }
        );
    }
}