<?php

namespace App\Support;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class ClienteFiltro
{
    public const IDS_EXCLUIDOS = ['0000000007295', '0000000004489'];

    /**
     * Aplica el criterio de cliente activo sobre una query que ya tiene
     * JOIN a catalogo_cliente_maestro.
     *
     * @param  string  $columnaStatus     Columna de status calificada, ej. 'c.status'.
     * @param  string|null  $columnaIdCliente  Columna de ID cliente calificada, ej. 'h.IDCLIENTE'.
     *                                        Si es null, no se aplica la exclusión de IDs.
     */
    public static function aplicarActivos(
        EloquentBuilder|QueryBuilder $query,
        string $columnaStatus,
        ?string $columnaIdCliente = null
    ): EloquentBuilder|QueryBuilder {
        if ($columnaIdCliente !== null) {
            $query->whereNotIn($columnaIdCliente, self::IDS_EXCLUIDOS);
        }

        return $query->whereIn($columnaStatus, Cliente::getActiveStatuses());
    }
}