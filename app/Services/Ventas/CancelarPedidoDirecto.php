<?php

namespace App\Services\Ventas;

use App\Models\Pedidos\OrdenPedido;
use App\Models\Pedidos\PedidoCancelado;
use App\Models\PersonalEmpresa;

class CancelarPedidoDirecto
{
    public function cancelar(int $id, PersonalEmpresa $usuario, string $motivo): void
    {
        $consulta = OrdenPedido::pedidosDirectosVisibles($usuario);
        $conexion = $consulta->getConnection();

        $conexion->transaction(function () use ($consulta, $conexion, $id, $usuario, $motivo): void {
            $pedido = $consulta->lockForUpdate()->findOrFail($id);
            abort_if($pedido->status === 3, 409, 'No se puede cancelar un pedido entregado.');

            PedidoCancelado::on($conexion->getName())->create([
                'id_pedido' => $pedido->getKey(),
                'motivo' => trim($motivo),
                'cancelado_por' => $usuario->getKey(),
                'fecha_cancelacion' => now(),
            ]);
            $pedido->forceFill(['status' => 1, 'activo' => false])->save();
        });
    }
}
