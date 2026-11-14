<?php

namespace App\Services\Ventas;

use App\Models\Pedidos\OrdenPedido;
use App\Models\PersonalEmpresa;
use App\Models\TmpCatalogo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrarPedidoDirecto
{
    public const UMBRAL_PROMOCION_PUBLICO_CENTAVOS = 30000;

    public function guardar(array $datos, PersonalEmpresa $operador): OrdenPedido
    {
        $sucursal = (int) $operador->sucursal_asignada;
        if ($sucursal === 0) {
            $sucursal = (int) ($datos['id_sucursal_origen'] ?? 0);
        }
        if ($sucursal <= 0) {
            throw ValidationException::withMessages([
                'sucursal' => 'Tu cuenta necesita una sucursal asignada para guardar el pedido.',
            ]);
        }

        $totalCentavos = collect($datos['articulos'])->sum(
            static fn (array $articulo): int => (int) round($articulo['precio_unitario'] * 100) * $articulo['cantidad']
        );
        if ($totalCentavos > 999999999999) {
            throw ValidationException::withMessages(['articulos' => 'El total excede el importe permitido.']);
        }

        $minimoCentavos = intdiv($totalCentavos + 1, 2);
        if ((int) round($datos['anticipo_cliente'] * 100) < $minimoCentavos) {
            throw ValidationException::withMessages([
                'anticipo_cliente' => 'El anticipo mínimo es $' . number_format($minimoCentavos / 100, 2) . '.',
            ]);
        }

        $aplicaPromocion = $datos['tipo_venta'] !== 'publico'
            || $totalCentavos >= self::UMBRAL_PROMOCION_PUBLICO_CENTAVOS;

        return DB::transaction(function () use ($datos, $operador, $sucursal, $aplicaPromocion): OrdenPedido {
            // Mantiene reservada la secuencia T hasta guardar los temporales y sus detalles.
            DB::select('SELECT TOP (1) id_tmp FROM dbo.tmp_catalogo WITH (TABLOCKX, HOLDLOCK)');
            $fecha = now();
            $pedido = OrdenPedido::create([
                'folio_pedido' => GenerarFolioPedido::siguiente($fecha),
                'sobre_pedido' => true,
                'id_cliente' => $datos['id_cliente'],
                'id_sucursal_origen' => $sucursal,
                'anticipo_cliente' => $datos['anticipo_cliente'],
                'status' => 2,
                'fecha_pedido' => $fecha,
                'fecha_encargo' => $datos['fecha_encargo'],
                'creado_por' => $operador->getKey(),
                'activo' => true,
            ]);

            foreach ($datos['articulos'] as $articulo) {
                $categoria = $articulo['categoria_promocion'];
                $promocion = (config('pedidos_directos.categorias') ?? (require config_path('pedidos_directos.php'))['categorias'])[$datos['tipo_venta']][$categoria];
                $temporal = TmpCatalogo::create([
                    'ean' => TmpCatalogo::generarEan(),
                    'descripcion' => $articulo['descripcion'],
                    'precio' => $articulo['precio_unitario'],
                    'creado_por' => $operador->getKey(),
                    'fecha_creacion' => $fecha,
                    'activo' => true,
                ]);
                $centavos = (int) round($articulo['precio_unitario'] * 100) * $articulo['cantidad'];
                $pedido->detalles()->create([
                    'ean' => $temporal->ean,
                    'ean_consultado' => isset($articulo['ean_consultado'])
                        ? str_pad($articulo['ean_consultado'], 13, '0', STR_PAD_LEFT) : null,
                    'descripcion_sobre_pedido' => $articulo['descripcion'],
                    'id_convenio' => $datos['id_convenio'] ?? null,
                    'categoria_promocion' => $categoria,
                    'porcentaje_promocion' => $aplicaPromocion ? $promocion['porcentaje'] : null,
                    'cantidad' => $articulo['cantidad'],
                    'precio_unitario' => $articulo['precio_unitario'],
                    'importe' => $centavos / 100,
                    'created_at' => $fecha,
                ]);
            }

            return $pedido;
        }, 3);
    }
}
