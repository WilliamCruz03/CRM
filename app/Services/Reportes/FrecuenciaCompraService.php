<?php

declare(strict_types=1);

namespace App\Services\Reportes;

use Illuminate\Database\Eloquent\Builder;

/**
 * Servicio único de cálculo de frecuencia de compra (R2).
 *
 * Dos fórmulas coexisten por decisión de negocio:
 *  - porPeriodo():   dias_periodo / compras            (ITAMAR, nuevo reporte)
 *  - entreCompras(): dias_periodo / (total_compras - 1) (legacy frecuenciaCompra / dashboard)
 */
final class FrecuenciaCompraService
{
    /** R3: criterio de status a excluir en el nuevo reporte. */
    public const STATUS_EXCLUIDOS = ['C', 'D'];

    /**
     * Frecuencia por período: días del período / compras.
     * Única fórmula mostrada en el nuevo reporte "Búsqueda por Artículo".
     */
    public function porPeriodo(int $diasPeriodo, int $compras): float
    {
        if ($compras <= 0) {
            return 0.0;
        }

        return $diasPeriodo / $compras;
    }

    /**
     * Frecuencia entre compras: días del período / (total_compras - 1).
     * Preserva la fórmula legacy exacta. Devuelve 0.0 si no hay al menos 2 compras.
     */
    public function entreCompras(int $diasPeriodo, int $totalCompras): float
    {
        if ($totalCompras <= 1) {
            return 0.0;
        }

        return $diasPeriodo / ($totalCompras - 1);
    }

    /**
     * Aplica el filtro de status "ventas válidas" sobre un Builder.
     * Uso exclusivo del nuevo controlador (R3). No forzar en legacy.
     */
    public function aplicarFiltroStatus(Builder $q, string $col = 'F_STATUS'): Builder
    {
        return $q->where(function (Builder $sub) use ($col): void {
            $sub->whereNull($col)
                ->orWhereNotIn($col, self::STATUS_EXCLUIDOS);
        });
    }
}