<?php
// app/Http/Controllers/Ventas/SeguimientoController.php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Seguimientos\Seguimiento;
use App\Models\Seguimientos\SeguimientoMensaje;
use App\Models\Seguimientos\QuejaCliente;
use App\Models\Seguimientos\SugerenciaCliente;
use App\Models\Cotizaciones\Cotizacion;
use App\Models\Pedidos\OrdenPedido;
use App\Models\Cliente;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeguimientoController extends Controller
{
    /**
     * Verificar permiso general para seguimiento
     * Cualquier usuario que pueda ver cotizaciones o pedidos puede hacer seguimiento
     */
    private function tienePermisoSeguimiento(): bool
    {
        return auth()->user()->puede('ventas', 'cotizaciones', 'ver') || 
               auth()->user()->puede('ventas', 'pedidos', 'ver');
    }

    /**
     * Obtener datos de la cotización para el modal
     */
    public function getCotizacionData(int $id): JsonResponse
    {
        if (!$this->tienePermisoSeguimiento()) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        try {
            $cotizacion = Cotizacion::with(['cliente'])
                ->where('activo', 1)
                ->where('es_pedido', '!=', 1)
                ->findOrFail($id);

            // Verificar que esté en proceso
            if ($cotizacion->fase_nombre !== 'En proceso') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se puede dar seguimiento a cotizaciones en estado "En proceso"'
                ], 400);
            }

            // Obtener el teléfono del cliente para WhatsApp
            $telefono = $cotizacion->cliente->telefono1 ?? $cotizacion->cliente->telefono2 ?? null;

            return response()->json([
                'success' => true,
                'data' => [
                    'id_referencia' => $cotizacion->id_cotizacion,
                    'folio' => $cotizacion->folio,
                    'tipo' => 'cotizacion',
                    'estado_actual' => 1,
                    'estado_nombre' => $cotizacion->fase_nombre,
                    'id_cliente_maestro' => $cotizacion->id_cliente,
                    'cliente_nombre' => $cotizacion->nombre_cliente,
                    'cliente_telefono' => $telefono,
                    'fecha_creacion' => $cotizacion->fecha_creacion->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener datos de cotización para seguimiento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los datos de la cotización'
            ], 500);
        }
    }

    /**
     * Obtener datos del pedido para el modal
     */
    public function getPedidoData(int $id): JsonResponse
    {
        if (!$this->tienePermisoSeguimiento()) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        try {
            $pedido = OrdenPedido::with(['cotizacion.cliente'])
                ->findOrFail($id);

            $cliente = $pedido->cotizacion->cliente ?? null;
            
            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el cliente asociado al pedido'
                ], 400);
            }

            $telefono = $cliente->telefono1 ?? $cliente->telefono2 ?? null;
            
            // Determinar si es pedido (status 2) o venta (status 3)
            $esVenta = $pedido->status == 3;
            $estadoNombre = $esVenta ? 'Venta completada' : ($pedido->status == 2 ? 'Pedido en proceso' : 'Estado: ' . $pedido->status);
            
            // Solo permitir seguimiento para pedidos en proceso (status 2) o ventas completadas (status 3)
            if (!in_array($pedido->status, [2, 3])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se puede dar seguimiento a pedidos en proceso o ventas completadas'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id_referencia' => $pedido->id_pedido,
                    'folio' => $pedido->folio_pedido,
                    'tipo' => $esVenta ? 'venta' : 'pedido',
                    'estado_actual' => $esVenta ? 3 : 2,
                    'estado_nombre' => $estadoNombre,
                    'id_cliente_maestro' => $cliente->id_Cliente,
                    'cliente_nombre' => $cliente->nombre_completo,
                    'cliente_telefono' => $telefono,
                    'fecha_creacion' => $pedido->fecha_pedido ? $pedido->fecha_pedido->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener datos de pedido para seguimiento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los datos del pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar seguimiento (unificado para cotizaciones, pedidos y ventas)
     */
    public function store(Request $request): JsonResponse
    {
        if (!$this->tienePermisoSeguimiento()) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        try {
            $validated = $request->validate([
                'tipo' => 'required|in:cotizacion,pedido,venta',
                'folio_referencia' => 'required|string|max:20',
                'id_cliente_maestro' => 'required|integer',
                'hora_fin' => 'required|date_format:Y-m-d\TH:i',
                'motivo_no_finalizacion' => 'nullable|string|max:500',
                'mensaje_cliente' => 'nullable|string|max:500',
                'conversacion' => 'nullable|string',
                'queja' => 'nullable|string|max:254',
                'sugerencia' => 'nullable|string|max:254',
            ], [
                'hora_fin.required' => 'La hora de fin es obligatoria',
                'tipo.required' => 'El tipo de seguimiento es obligatorio',
            ]);

            DB::connection('sqlsrv')->beginTransaction();

            // Determinar estado_actual y qué folio guardar
            $estadoActual = match($validated['tipo']) {
                'cotizacion' => 1,
                'pedido' => 2,
                'venta' => 3,
                default => 1
            };

            $folioCotizacion = $validated['tipo'] === 'cotizacion' ? $validated['folio_referencia'] : null;
            $folioPedido = in_array($validated['tipo'], ['pedido', 'venta']) ? $validated['folio_referencia'] : null;

            // Crear el seguimiento principal
            $seguimiento = Seguimiento::create([
                'id_cliente_maestro' => $validated['id_cliente_maestro'],
                'folio_cotizacion' => $folioCotizacion,
                'folio_pedido' => $folioPedido,
                'estado_actual' => $estadoActual,
                'motivo_no_finalizacion' => $validated['motivo_no_finalizacion'] ?? null,
                'mensaje_cliente' => $validated['mensaje_cliente'] ?? null,
                'hora_inicio' => now(),
                'hora_fin' => $validated['hora_fin'],
                'creado_por' => auth()->user()->id_personal_empresa,
                'editado_por' => null,
            ]);

            // Guardar conversación si existe
            if (!empty($validated['conversacion'])) {
                SeguimientoMensaje::create([
                    'id_seguimiento' => $seguimiento->id_seguimiento,
                    'mensaje' => $validated['conversacion']
                ]);
            }

            // Guardar queja si existe
            if (!empty($validated['queja'])) {
                QuejaCliente::create([
                    'id_cliente_maestro' => $validated['id_cliente_maestro'],
                    'queja' => substr($validated['queja'], 0, 254),
                    'notas' => 'Registrada desde seguimiento de ' . $validated['tipo'] . ': ' . $validated['folio_referencia'],
                    'fecha_creacion' => now(),
                    'id_operador' => auth()->user()->id_personal_empresa,
                ]);
            }

            // Guardar sugerencia si existe
            if (!empty($validated['sugerencia'])) {
                SugerenciaCliente::create([
                    'id_cliente_maestro' => $validated['id_cliente_maestro'],
                    'sugerencia' => substr($validated['sugerencia'], 0, 254),
                    'notas' => 'Registrada desde seguimiento de ' . $validated['tipo'] . ': ' . $validated['folio_referencia'],
                    'fecha_creacion' => now(),
                    'id_operador' => auth()->user()->id_personal_empresa,
                    'status' => 1,
                ]);
            }

            DB::connection('sqlsrv')->commit();

            Log::info('Seguimiento registrado correctamente', [
                'seguimiento_id' => $seguimiento->id_seguimiento,
                'tipo' => $validated['tipo'],
                'folio' => $validated['folio_referencia'],
                'usuario' => auth()->user()->id_personal_empresa
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Seguimiento guardado correctamente',
                'data' => $seguimiento
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            Log::error('Error al guardar seguimiento: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el seguimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener configuración de días de alerta
     */
    public function getConfiguracionAlerta(): JsonResponse
    {
        try {
            // Usar el helper getValor del modelo Configuracion
            $diasCancelacion = Configuracion::getValor('dias_cancelacion_cotizacion', 7);
            $diasResaltado = Configuracion::getValor('dias_resaltado_alerta', 2);
            
            return response()->json([
                'success' => true,
                'dias_cancelacion' => $diasCancelacion,
                'dias_resaltado' => $diasResaltado
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener configuración de alerta: ' . $e->getMessage());
            return response()->json([
                'success' => true,
                'dias_cancelacion' => 7,
                'dias_resaltado' => 2
            ]);
        }
    }
}