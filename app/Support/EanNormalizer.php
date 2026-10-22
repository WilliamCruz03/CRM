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
     */
    public static function normalizar(string $ean): string
    {
        // TODO (R7): reemplazar por helper existente → return HelperExistente::pad($ean);
        $ean = trim($ean);

        if ($ean === '' || ! ctype_digit($ean)) {
            return $ean;
        }

        return str_pad($ean, self::largoEan(), '0', STR_PAD_LEFT);
    }

    /**
     * Largo del EAN según el catálogo maestro. Cacheado 1h.
     * Conexión: sqlsrvM (fp_central_matriz).
     */
    public static function largoEan(): int
    {
        // TODO (R7): reemplazar por el largo del helper existente.
        return (int) Cache::remember(
            self::CACHE_KEY_LARGO,
            self::CACHE_TTL_SEGUNDOS,
            static function (): int {
                $row = DB::connection('sqlsrvM')
                    ->selectOne('SELECT MAX(LEN(ean)) AS l FROM catalogo_general');

                return (int) ($row->l ?? self::LARGO_FALLBACK);
            }
        );
    }
}