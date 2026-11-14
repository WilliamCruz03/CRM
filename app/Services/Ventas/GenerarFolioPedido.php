<?php

namespace App\Services\Ventas;

use App\Models\Pedidos\OrdenPedido;
use Carbon\CarbonInterface;
use LogicException;

class GenerarFolioPedido
{
    public static function siguiente(CarbonInterface $fecha): string
    {
        $query = OrdenPedido::query();
        if ($query->getConnection()->transactionLevel() === 0) {
            throw new LogicException('El folio debe generarse en la transacción que guarda el pedido.');
        }

        $prefijo = 'OP-' . $fecha->format('Ymd') . '-';
        $ultimo = $query->lock('WITH (TABLOCKX, HOLDLOCK)')
            ->where('folio_pedido', 'like', $prefijo . '%')
            ->selectRaw('MAX(TRY_CONVERT(int, SUBSTRING(folio_pedido, ?, 20))) AS consecutivo', [strlen($prefijo) + 1])
            ->first()->consecutivo;

        return $prefijo . str_pad((string) ((int) $ultimo + 1), 4, '0', STR_PAD_LEFT);
    }
}
