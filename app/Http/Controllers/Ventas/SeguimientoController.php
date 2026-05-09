<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Seguimientos\Seguimiento;
use App\Models\Seguimientos\SeguimientoMensaje;
use App\Models\Cotizaciones\Cotizacion;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Seguimientos\QuejaCliente;
use App\Models\Seguimientos\SugerenciaCliente;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeguimientoController extends Controller
{
    /**
     * Obtener datos de la cotización para el modal
     */
    public function getCotizacionData(int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'ver')) {
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
                    'id_cotizacion' => $cotizacion->id_cotizacion,
                    'folio' => $cotizacion->folio,
                    'id_cliente_maestro' => $cotizacion->id_cliente,
                    'cliente_nombre' => $cotizacion->nombre_cliente,
                    'cliente_telefono' => $telefono,
                    'certeza' => $cotizacion->certeza,
                    'certeza_nombre' => $cotizacion->certeza_nombre,
                    'fecha_creacion' => $cotizacion->fecha_creacion->format('Y-m-d H:i:s'),
                    'dias_transcurridos' => $cotizacion->fecha_creacion->diffInDays(now())
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
     * Guardar seguimiento
     */
    public function store(Request $request): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'ver')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        try {
            $validated = $request->validate([
                'folio_cotizacion' => 'required|string|max:20',
                'id_cliente_maestro' => 'required|integer',
                'hora_fin' => 'required|date_format:Y-m-d\TH:i',
                'motivo_no_finalizacion' => 'nullable|string|max:500',
                'mensaje_cliente' => 'nullable|string|max:500',
                'conversacion' => 'nullable|string',
                'queja' => 'nullable|string|max:254',
                'sugerencia' => 'nullable|string|max:254',
            ]);

            DB::connection('sqlsrv')->beginTransaction();

            // Crear el seguimiento principal
            $seguimiento = Seguimiento::create([
                'id_cliente_maestro' => $validated['id_cliente_maestro'],
                'folio_cotizacion' => $validated['folio_cotizacion'],
                'folio_pedido' => null,
                'estado_actual' => 1, // Cotización
                'motivo_no_finalizacion' => $validated['motivo_no_finalizacion'] ?? null,
                'mensaje_cliente' => $validated['mensaje_cliente'] ?? null,
                'hora_inicio' => now(),
                'hora_fin' => !empty($validated['hora_fin']) ? $validated['hora_fin'] : null,
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

            // Guardar queja si existe (en BD matriz)
            if (!empty($validated['queja'])) {
                QuejaCliente::create([
                    'id_cliente_maestro' => $validated['id_cliente_maestro'],
                    'queja' => substr($validated['queja'], 0, 254),
                    'notas' => 'Registrada desde seguimiento de cotización: ' . $validated['folio_cotizacion'],
                    'fecha_creacion' => now(),
                    'id_operador' => auth()->user()->id_personal_empresa,
                ]);
            }

            // Guardar sugerencia si existe (en BD matriz)
            if (!empty($validated['sugerencia'])) {
                SugerenciaCliente::create([
                    'id_cliente_maestro' => $validated['id_cliente_maestro'],
                    'sugerencia' => substr($validated['sugerencia'], 0, 254),
                    'notas' => 'Registrada desde seguimiento de cotización: ' . $validated['folio_cotizacion'],
                    'fecha_creacion' => now(),
                    'id_operador' => auth()->user()->id_personal_empresa,
                    'status' => 1, // Pendiente
                ]);
            }

            DB::connection('sqlsrv')->commit();

            Log::info('Seguimiento registrado correctamente', [
                'seguimiento_id' => $seguimiento->id_seguimiento,
                'folio_cotizacion' => $validated['folio_cotizacion'],
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
            $diasAlerta = Configuracion::where('nombre', 'dias_sin_contacto_alerta')
                ->where('activo', 1)
                ->value('valor');

            $dias = $diasAlerta ? (int)$diasAlerta : 7;

            return response()->json([
                'success' => true,
                'dias_alerta' => $dias
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'dias_alerta' => 7 // Valor por defecto
            ]);
        }
    }
}