<?php

namespace App\Http\Controllers;

use App\Models\Cotizaciones\Cotizacion;
use App\Models\Configuracion;
use App\Models\Seguimientos\Seguimiento;
use App\Models\Pedidos\OrdenPedido;
use App\Models\Notificacion;
use App\Models\AgendaContacto\AgendaContacto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request as HttpRequest;
use App\Events\NotificacionLeida;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class NotificacionController extends Controller
{
    public function getNotificaciones(): JsonResponse
    {
        try {
            $user = Auth::user();

            // Si no hay usuario autenticado, devolver JSON vacío
            if (!$user) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'total' => 0,
                    'mensaje_general' => 'No hay notificaciones disponibles',
                    'tipo' => 'sin_notificaciones'
                ]);
            }

            $this->persistirAlertasCotizaciones($user);
            $this->persistirAlertasContactos($user);
            $this->softResolveAlertas($user);

            $notificaciones = $this->leerNotificacionesVisibles($user);

            usort($notificaciones, function ($a, $b) {
                $fechaA = $a['created_at'] ?? '';
                $fechaB = $b['created_at'] ?? '';
                return strtotime($fechaB) - strtotime($fechaA);
            });

            return response()->json([
                'success' => true,
                'data' => array_values($notificaciones),
                'total' => count($notificaciones),
                'mensaje_general' => count($notificaciones) === 0 ? 'No hay notificaciones pendientes' : null,
                'tipo' => 'general'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en getNotificaciones: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'data' => [],
                'total' => 0,
                'mensaje_general' => 'Error al cargar notificaciones',
                'tipo' => 'error'
            ]);
        }
    }
    
    // TODO: reemplazado por persistirAlertasCotizaciones() / persistirAlertasContactos(). Mantener por si se necesita el cálculo al vuelo en otro contexto.
    private function getNotificacionesAgendaContactos($user): array
    {
        try {
            if (!$user->puede('ventas', 'agenda_contactos', 'ver')) {
                return [];
            }
            
            // Solo contactos de los últimos 7 días para evitar lentitud
            $contactos = AgendaContacto::where('estado', 1)
                ->where('activo', 1)
                ->where('fecha', '>=', now()->subDays(7))
                ->get();
            
            $notificaciones = [];
            $ahora = now();
            
            foreach ($contactos as $contacto) {
                // Extraer solo la fecha (Y-m-d) y concatenar con la hora
                $soloFecha = substr($contacto->fecha, 0, 10);
                $fechaHora = \Carbon\Carbon::parse($soloFecha . ' ' . $contacto->hora);
                $recordatorioMinutos = $contacto->recordatorio_minutos ?? 60;
                
                $inicioNotificacion = $fechaHora->copy()->subMinutes($recordatorioMinutos);
                $finNotificacion = $fechaHora->copy()->addMinutes(60);
                
                if ($ahora >= $inicioNotificacion && $ahora <= $finNotificacion) {
                    $minutosDiferencia = $ahora->diffInMinutes($fechaHora, false);
                    $cliente = $contacto->cliente;
                    
                    // Construir nombre del cliente manualmente
                    if ($cliente) {
                        $partes = [];
                        if (!empty($cliente->Nombre)) $partes[] = $cliente->Nombre;
                        if (!empty($cliente->apPaterno)) $partes[] = $cliente->apPaterno;
                        if (!empty($cliente->apMaterno)) $partes[] = $cliente->apMaterno;
                        $nombreCliente = implode(' ', $partes);
                        if (empty($nombreCliente)) $nombreCliente = 'Cliente';
                    } else {
                        $nombreCliente = 'Cliente';
                    }
                    
                    $fecha = $contacto->fecha;
                    $hora = \Carbon\Carbon::parse($contacto->hora)->format('g:i A');
                    
                    $color = ($minutosDiferencia >= 0) ? 'warning' : 'danger';  // warning para próximo (amarillo)
                    $icono = ($minutosDiferencia >= 0) ? 'bi-exclamation-triangle' : 'bi-exclamation-triangle';  // mismo icono para ambos
                    $tiempoTexto = $this->formatearTiempo(abs($minutosDiferencia));

                    $mensaje = ($minutosDiferencia >= 0) 
                        ? "Próximo contacto en {$tiempoTexto}"
                        : "Atrasado por {$tiempoTexto}";

                    $notificaciones[] = [
                        'id' => $contacto->id_agenda_contacto,
                        'cliente' => $nombreCliente,
                        'asunto' => $contacto->asunto,
                        'fecha' => $contacto->fecha,
                        'hora' => $hora,
                        'color' => $color,
                        'icono' => $icono,
                        'mensaje' => $mensaje,
                        'url' => route('ventas.agenda_contactos.index') . '?destacar=' . $contacto->id_agenda_contacto . '&destacar_tipo=contacto',
                        'tipo' => 'contacto'
                    ];
                }
            }
            
            return $notificaciones;
            
        } catch (\Exception $e) {
            \Log::error('Error en getNotificacionesAgendaContactos: ' . $e->getMessage());
            return [];
        }
    }

    private function formatearTiempo(int $minutos): string
    {
        if ($minutos < 60) {
            return "{$minutos} minuto(s)";
        }
        $horas = floor($minutos / 60);
        $resto = $minutos % 60;
        if ($resto > 0) {
            return "{$horas} hora(s) y {$resto} minuto(s)";
        }
        return "{$horas} hora(s)";
    }
    
    /**
     * Devuelve notificaciones de cotizaciones como ARRAY
     */
    // TODO: reemplazado por persistirAlertasCotizaciones() / persistirAlertasContactos(). Mantener por si se necesita el cálculo al vuelo en otro contexto.
    private function getNotificacionesCotizacionesArray($user): array
    {
        try {
            if (!$user->puede('ventas', 'cotizaciones', 'ver')) {
                return [];
            }
            
            $diasAlerta = Configuracion::getValor('dias_sin_contacto_alerta', 7);
            
            $faseEnProceso = \App\Models\Cotizaciones\CatFase::where('fase', 'En proceso')->first();
            
            if (!$faseEnProceso) {
                return [];
            }
            
            $cotizaciones = Cotizacion::with(['cliente', 'seguimientos'])
                ->where('activo', 1)
                ->where('es_pedido', '!=', 1)
                ->where('id_fase', $faseEnProceso->id_fase)
                ->get();
            
            $notificaciones = [];
            
            foreach ($cotizaciones as $cotizacion) {
                $diasSinContacto = $cotizacion->fecha_creacion ? ceil($cotizacion->fecha_creacion->diffInDays(now())) : 0;
                
                // VERIFICAR SEGUIMIENTOS CON TRY-CATCH
                $tieneSeguimientoReciente = false;
                try {
                    $tieneSeguimientoReciente = $cotizacion->seguimientos()
                        ->where('hora_inicio', '>=', now()->subDays($diasAlerta))
                        ->exists();
                } catch (\Exception $e) {
                    \Log::warning('Error al verificar seguimientos para cotización ' . $cotizacion->id_cotizacion . ': ' . $e->getMessage());
                    // Si hay error, asumir que no tiene seguimiento reciente
                    $tieneSeguimientoReciente = false;
                }
                
                if ($diasSinContacto >= $diasAlerta && !$tieneSeguimientoReciente) {
                    $notificaciones[] = [
                        'id' => $cotizacion->id_cotizacion,
                        'folio' => $cotizacion->folio,
                        'cliente' => $cotizacion->nombre_cliente,
                        'dias' => $diasSinContacto,
                        'mensaje' => "¡Requiere seguimiento! No se ha contactado al cliente recientemente. ({$diasSinContacto} días)",
                        'url' => route('ventas.cotizaciones.index') . '?destacar=' . $cotizacion->id_cotizacion . '&destacar_tipo=cotizacion',
                        'tipo' => 'cotizacion'
                    ];
                }
            }
            
            return $notificaciones;
            
        } catch (\Exception $e) {
            \Log::error('Error en getNotificacionesCotizacionesArray: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Devuelve notificaciones de pedidos como ARRAY
     */
    // TODO: reemplazado por persistirAlertasCotizaciones() / persistirAlertasContactos(). Mantener por si se necesita el cálculo al vuelo en otro contexto.
    private function getNotificacionesPedidosArray($user): array
    {
        try {
            if (!$user->puede('ventas', 'pedidos', 'ver')) {
                return [];
            }
            
            // Usar configuración específica para pedidos
            $diasAlerta = Configuracion::getValor('dias_alerta_pedidos', 7);
            
            $pedidos = OrdenPedido::with(['cotizacion.cliente', 'seguimientos'])
                ->where('status', 2)
                ->where('activo', 1)
                ->get();
            
            $notificaciones = [];
            
            foreach ($pedidos as $pedido) {
                $fechaCreacion = $pedido->fecha_pedido ?? $pedido->created_at;
                $diasTranscurridos = $fechaCreacion ? ceil($fechaCreacion->diffInDays(now())) : 0;
                
                // Verificar si tiene seguimiento reciente
                $tieneSeguimientoReciente = $pedido->seguimientos()
                    ->where('hora_inicio', '>=', now()->subDays($diasAlerta))
                    ->exists();
                
                if ($diasTranscurridos >= $diasAlerta && !$tieneSeguimientoReciente) {
                    $cliente = $pedido->cotizacion->cliente ?? null;
                    $nombreCliente = $cliente->nombre_completo ?? 'Cliente';
                    
                    $notificaciones[] = [
                        'id' => $pedido->id_pedido,
                        'folio' => $pedido->folio_pedido,
                        'cliente' => $nombreCliente,
                        'dias' => $diasTranscurridos,
                        'mensaje' => "¡Requiere seguimiento! Pedido sin contacto reciente. ({$diasTranscurridos} días)",
                        'url' => route('ventas.pedidos.index') . '?destacar=' . $pedido->id_pedido . '&destacar_tipo=pedido',
                        'tipo' => 'pedido'
                    ];
                }
            }
            
            return $notificaciones;
            
        } catch (\Exception $e) {
            \Log::error('Error en getNotificacionesPedidosArray: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener notificaciones guardadas en la tabla notificaciones
     */
    private function getNotificacionesTabla($user): array
    {
        try {
            // Verificar si el usuario es CRM
            $esCRM = DB::connection('sqlsrv')
                ->table('permisos_granulares')
                ->where('id_personal_empresa', $user->id_personal_empresa)
                ->where('es_crm', 1)
                ->exists();
            
            // Mostrar notificaciones personales + compartidas (solo si es CRM)
            $notificaciones = Notificacion::where('leida', 0)
                ->where(function($query) use ($user, $esCRM) {
                    $query->where('id_usuario', $user->id_personal_empresa);
                    
                    if ($esCRM) {
                        $query->orWhere('id_usuario', 0);
                    }
                })
                ->orderBy('created_at', 'DESC')
                ->limit(20)
                ->get();
            
            $resultado = [];
            
            foreach ($notificaciones as $notif) {
                $datosExtra = is_string($notif->datos_extra) ? json_decode($notif->datos_extra, true) : $notif->datos_extra;
                
                // Determinar icono según tipo
                $icono = 'bi-bell';
                $color = 'text-info';
                
                if ($notif->tipo === 'pedido_listo') {
                    $icono = 'bi-box-seam';
                    $color = 'text-success';
                } elseif ($notif->tipo === 'pedido_asignado') {
                    $icono = 'bi-truck';
                    $color = 'text-primary';
                }
                
                // Construir URL para redirigir
                $url = '#';
                if (isset($datosExtra['pedido_id'])) {
                    $url = route('ventas.pedidos.index') . '?destacar=' . $datosExtra['pedido_id'];
                }
                
                $resultado[] = [
                    'id' => $notif->id_notificacion,
                    'titulo' => $notif->titulo,
                    'mensaje' => $notif->mensaje,
                    'tipo' => $notif->tipo,
                    'icono' => $icono,
                    'color' => $color,
                    'leida' => $notif->leida,
                    'url' => $url,
                    'created_at' => $notif->created_at ? $notif->created_at->format('d/m/Y H:i') : '',
                    'datos_extra' => $datosExtra,
                    'es_persistente' => true, // Flag para identificar que viene de la BD
                    'es_compartida' => is_null($notif->id_usuario),  // Flag para identificar notificaciones compartidas
                ];
            }
            
            return $resultado;
            
        } catch (\Exception $e) {
            \Log::error('Error en getNotificacionesTabla: ' . $e->getMessage());
            return [];
        }
    }

    private function persistirAlertasCotizaciones($user): void
    {
        if (!$user->puede('ventas', 'cotizaciones', 'ver')) {
            return;
        }

        $diasAlerta = Configuracion::getValor('dias_sin_contacto_alerta', 7);
        $faseEnProceso = \App\Models\Cotizaciones\CatFase::where('fase', 'En proceso')->first();

        if (!$faseEnProceso) {
            return;
        }

        $cotizaciones = Cotizacion::with(['cliente', 'seguimientos'])
            ->where('activo', 1)
            ->where('es_pedido', '!=', 1)
            ->where('id_fase', $faseEnProceso->id_fase)
            ->get();

        $existentes = Notificacion::where('tipo', 'cotizacion_alerta')
            ->where('leida', 0)
            ->get()
            ->keyBy(fn ($n) => $this->extraerIdOrigen($n->datos_extra));

        foreach ($cotizaciones as $cotizacion) {
            $diasSinContacto = $cotizacion->fecha_creacion
                ? ceil($cotizacion->fecha_creacion->diffInDays(now()))
                : 0;

            $tieneSeguimientoReciente = $cotizacion->seguimientos()
                ->where('hora_inicio', '>=', now()->subDays($diasAlerta))
                ->exists();

            if ($diasSinContacto < $diasAlerta || $tieneSeguimientoReciente) {
                continue;
            }

            $idOrigen = 'cotizacion:' . $cotizacion->id_cotizacion;

            if ($existentes->has($idOrigen)) {
                continue;
            }

            Notificacion::create([
                'id_usuario' => 0,
                'tipo' => 'cotizacion_alerta',
                'titulo' => 'Seguimiento cotización ' . $cotizacion->folio,
                'mensaje' => "¡Requiere seguimiento! No se ha contactado al cliente recientemente. ({$diasSinContacto} días)",
                'datos_extra' => json_encode([
                    'id_origen' => $idOrigen,
                    'id_cotizacion' => $cotizacion->id_cotizacion,
                    'folio' => $cotizacion->folio,
                    'cliente' => $cotizacion->nombre_cliente,
                    'dias' => $diasSinContacto,
                ]),
                'leida' => 0,
                'creado_por' => 0,
            ]);
        }
    }

    private function persistirAlertasContactos($user): void
    {
        if (!$user->puede('ventas', 'agenda_contactos', 'ver')) {
            return;
        }

        $contactos = AgendaContacto::where('estado', 1)
            ->where('activo', 1)
            ->where('fecha', '>=', now()->subDays(7))
            ->get();

        $existentes = Notificacion::where('tipo', 'contacto_alerta')
            ->where('leida', 0)
            ->get()
            ->keyBy(fn ($n) => $this->extraerIdOrigen($n->datos_extra));

        $ahora = now();

        foreach ($contactos as $contacto) {
            $soloFecha = substr($contacto->fecha, 0, 10);
            $fechaHora = Carbon::parse($soloFecha . ' ' . $contacto->hora);
            $recordatorioMinutos = $contacto->recordatorio_minutos ?? 60;

            $inicioNotificacion = $fechaHora->copy()->subMinutes($recordatorioMinutos);
            $finNotificacion = $fechaHora->copy()->addMinutes(60);

            if ($ahora < $inicioNotificacion || $ahora > $finNotificacion) {
                continue;
            }

            $idOrigen = 'contacto:' . $contacto->id_agenda_contacto;

            if ($existentes->has($idOrigen)) {
                continue;
            }

            $sucursalCreador = $this->obtenerSucursalUsuario($contacto->creado_por ?? $contacto->id_usuario_creador ?? null);

            $minutosDiferencia = $ahora->diffInMinutes($fechaHora, false);

            Notificacion::create([
                'id_usuario' => 0,
                'tipo' => 'contacto_alerta',
                'titulo' => 'Recordatorio: ' . ($contacto->asunto ?? 'Contacto agendado'),
                'mensaje' => $minutosDiferencia >= 0
                    ? 'Próximo contacto en ' . $this->formatearTiempo(abs($minutosDiferencia))
                    : 'Atrasado por ' . $this->formatearTiempo(abs($minutosDiferencia)),
                'datos_extra' => json_encode([
                    'id_origen' => $idOrigen,
                    'id_contacto' => $contacto->id_agenda_contacto,
                    'id_sucursal_creador' => $sucursalCreador,
                    'fecha' => $contacto->fecha,
                    'hora' => $contacto->hora,
                    'asunto' => $contacto->asunto,
                ]),
                'leida' => 0,
                'creado_por' => $contacto->creado_por ?? 0,
            ]);
        }
    }

    private function softResolveAlertas($user): void
    {
        $diasAlerta = Configuracion::getValor('dias_sin_contacto_alerta', 7);
        $faseEnProceso = \App\Models\Cotizaciones\CatFase::where('fase', 'En proceso')->first();

        $pendientesCot = Notificacion::where('tipo', 'cotizacion_alerta')
            ->where('leida', 0)
            ->get();

        foreach ($pendientesCot as $notif) {
            $datos = $this->parsearDatosExtra($notif->datos_extra);
            $idCot = $datos['id_cotizacion'] ?? null;
            if (!$idCot) {
                continue;
            }

            $cot = Cotizacion::find($idCot);
            $sigueVigente = $cot
                && $cot->activo == 1
                && $faseEnProceso
                && $cot->id_fase == $faseEnProceso->id_fase
                && !$cot->seguimientos()->where('hora_inicio', '>=', now()->subDays($diasAlerta))->exists();

            if (!$sigueVigente) {
                $notif->leida = 1;
                $notif->save();
            }
        }

        $pendientesCont = Notificacion::where('tipo', 'contacto_alerta')
            ->where('leida', 0)
            ->get();

        $ahora = now();

        foreach ($pendientesCont as $notif) {
            $datos = $this->parsearDatosExtra($notif->datos_extra);
            $idContacto = $datos['id_contacto'] ?? null;
            if (!$idContacto) {
                continue;
            }

            $contacto = AgendaContacto::find($idContacto);
            $sigueVigente = false;

            if ($contacto && $contacto->estado == 1 && $contacto->activo == 1) {
                $soloFecha = substr($contacto->fecha, 0, 10);
                $fechaHora = Carbon::parse($soloFecha . ' ' . $contacto->hora);
                $recordatorioMinutos = $contacto->recordatorio_minutos ?? 60;
                $inicio = $fechaHora->copy()->subMinutes($recordatorioMinutos);
                $fin = $fechaHora->copy()->addMinutes(60);
                $sigueVigente = $ahora >= $inicio && $ahora <= $fin;
            }

            if (!$sigueVigente) {
                $notif->leida = 1;
                $notif->save();
            }
        }
    }

    private function leerNotificacionesVisibles($user): array
    {
        $sucursalUsuario = $this->obtenerSucursalUsuario($user->id_personal_empresa);
        $esCRM = $this->esUsuarioCRM($user);

        $query = Notificacion::where('leida', 0);

        $query->where(function ($q) use ($user, $esCRM, $sucursalUsuario) {
            if ($user->puede('ventas', 'cotizaciones', 'ver')) {
                $q->orWhere(function ($qq) {
                    $qq->where('tipo', 'cotizacion_alerta')->where('id_usuario', 0);
                });
            }

            if ($user->puede('ventas', 'agenda_contactos', 'ver') && $sucursalUsuario) {
                $q->orWhere(function ($qq) use ($sucursalUsuario) {
                    $qq->where('tipo', 'contacto_alerta')->where('id_usuario', 0);
                });
            }

            if ($esCRM && $user->puede('ventas', 'pedidos', 'ver')) {
                $q->orWhere(function ($qq) {
                    $qq->where('tipo', 'pedido_listo')->where('id_usuario', 0);
                });
            }

            if ($user->puede('ventas', 'pedidos', 'ver')) {
                $q->orWhere(function ($qq) use ($user) {
                    $qq->where('tipo', 'pedido_asignado')->where('id_usuario', $user->id_personal_empresa);
                });
            }
        });

        $resultado = [];

        foreach ($query->orderBy('created_at', 'DESC')->limit(30)->get() as $notif) {
            $datos = $this->parsearDatosExtra($notif->datos_extra);

            if ($notif->tipo === 'contacto_alerta') {
                $sucursalCreador = $datos['id_sucursal_creador'] ?? null;
                if (!$sucursalUsuario || $sucursalCreador != $sucursalUsuario) {
                    continue;
                }
            }

            $icono = 'bi-bell';
            $color = 'text-info';

            if ($notif->tipo === 'cotizacion_alerta') { $icono = 'bi-file-earmark-text'; $color = 'text-danger'; }
            elseif ($notif->tipo === 'contacto_alerta') { $icono = 'bi-exclamation-triangle'; $color = 'text-warning'; }
            elseif ($notif->tipo === 'pedido_listo') { $icono = 'bi-box-seam'; $color = 'text-success'; }
            elseif ($notif->tipo === 'pedido_asignado') { $icono = 'bi-truck'; $color = 'text-primary'; }

            $url = '#';
            if ($notif->tipo === 'cotizacion_alerta' && isset($datos['id_cotizacion'])) {
                $url = route('ventas.cotizaciones.index') . '?destacar=' . $datos['id_cotizacion'] . '&destacar_tipo=cotizacion';
            } elseif ($notif->tipo === 'contacto_alerta' && isset($datos['id_contacto'])) {
                $url = route('ventas.agenda_contactos.index') . '?destacar=' . $datos['id_contacto'] . '&destacar_tipo=contacto';
            } elseif (in_array($notif->tipo, ['pedido_listo', 'pedido_asignado']) && isset($datos['pedido_id'])) {
                $url = route('ventas.pedidos.index') . '?destacar=' . $datos['pedido_id'] . '&destacar_tipo=pedido';
            }

            $resultado[] = [
                'id' => $notif->id_notificacion,
                'tipo' => $notif->tipo,
                'titulo' => $notif->titulo,
                'mensaje' => $notif->mensaje,
                'icono' => $icono,
                'color' => $color,
                'url' => $url,
                'datos_extra' => $datos,
                'created_at' => $notif->created_at ? $notif->created_at->format('d/m/Y H:i') : '',
                'es_persistente' => true,
            ];
        }

        return $resultado;
    }

    private function extraerIdOrigen(?string $datosExtra): ?string
    {
        if (!$datosExtra) return null;
        $datos = json_decode($datosExtra, true);
        return $datos['id_origen'] ?? null;
    }

    private function parsearDatosExtra($datosExtra): array
    {
        if (is_array($datosExtra)) return $datosExtra;
        if (is_string($datosExtra)) return json_decode($datosExtra, true) ?? [];
        return [];
    }

    private function obtenerSucursalUsuario(?int $idPersonalEmpresa): ?int
    {
        if (!$idPersonalEmpresa) return null;

        $row = DB::connection('sqlsrvM')
            ->table('personal_empresa')
            ->where('id_personal_empresa', $idPersonalEmpresa)
            ->first();

        return $row->sucursal_asignada ?? null;
    }

    private function esUsuarioCRM($user): bool
    {
        return DB::connection('sqlsrv')
            ->table('permisos_granulares')
            ->where('id_personal_empresa', $user->id_personal_empresa)
            ->where('es_crm', 1)
            ->exists();
    }

    /**
     * Marcar una notificación como leída
     */
    public function marcarComoLeida(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Buscar notificación (compartida o personal)
            $notificacion = Notificacion::where('id_notificacion', $id)
                ->where(function($query) use ($user) {
                    $query->where('id_usuario', $user->id_personal_empresa)
                        ->orWhere('id_usuario', 0);
                })
                ->first();
            
            if (!$notificacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notificación no encontrada'
                ], 404);
            }
            
            // Marcar como leída (tanto si es compartida como personal)
            $notificacion->leida = 1;
            $notificacion->save();
            
            // DISPARAR EVENTO PARA ACTUALIZAR A TODOS LOS CRM
            try {
                broadcast(new NotificacionLeida($id));
            } catch (\Exception $e) {
                \Log::error('Error al enviar evento NotificacionLeida: ' . $e->getMessage());
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Notificación marcada como leída'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al marcar notificación como leída: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar como leída'
            ], 500);
        }
    }
    
    /**
     * Verificar si una notificación de pedido ya fue leída
     */
    public function verificarLeida($pedidoId): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Verificar si el usuario es CRM
            $esCRM = DB::connection('sqlsrv')
                ->table('permisos_granulares')
                ->where('id_personal_empresa', $user->id_personal_empresa)
                ->where('es_crm', 1)
                ->exists();
            
            // Buscar notificación compartida o personal para este pedido
            $notificacion = Notificacion::where('tipo', 'pedido_listo')
                ->where(function($query) use ($user, $esCRM) {
                    $query->where('id_usuario', $user->id_personal_empresa);
                    
                    if ($esCRM) {
                        $query->orWhere('id_usuario', 0);
                    }
                })
                ->where('datos_extra', 'LIKE', '%"pedido_id":' . $pedidoId . '%')
                ->first();
            
            return response()->json([
                'success' => true,
                'leida' => $notificacion ? $notificacion->leida : false,
                'existe' => $notificacion ? true : false
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al verificar notificación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'leida' => false,
                'message' => 'Error al verificar'
            ], 500);
        }
    }

    /**
     * Obtener historial de notificaciones del usuario (últimas N)
     */
    public function historial(HttpRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }
            
            $limit = $request->input('limit', 5);
            
            // Verificar si el usuario es CRM
            $esCRM = DB::connection('sqlsrv')
                ->table('permisos_granulares')
                ->where('id_personal_empresa', $user->id_personal_empresa)
                ->where('es_crm', 1)
                ->exists();
            
            // Mostrar notificaciones leídas: propias + compartidas (solo si es CRM)
            $notificaciones = Notificacion::where('leida', 1)
                ->where(function($query) use ($user, $esCRM) {
                    $query->where('id_usuario', $user->id_personal_empresa);
                    
                    if ($esCRM) {
                        $query->orWhere('id_usuario', 0);
                    }
                })
                ->orderBy('created_at', 'DESC')
                ->limit($limit)
                ->get()
                ->map(function($notif) {
                    $datosExtra = is_string($notif->datos_extra) ? json_decode($notif->datos_extra, true) : $notif->datos_extra;
                    
                    return [
                        'id' => $notif->id_notificacion,
                        'tipo' => $notif->tipo,
                        'titulo' => $notif->titulo,
                        'mensaje' => $notif->mensaje,
                        'leida' => $notif->leida,
                        'datos_extra' => $datosExtra,
                        'created_at' => $notif->created_at ? $notif->created_at->format('d/m/Y H:i') : '',
                        'fecha_completa' => $notif->created_at ? $notif->created_at->toDateTimeString() : '',
                        'es_compartida' => $notif->id_usuario == 0,  // Flag para saber si es compartida
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $notificaciones,
                'total' => $notificaciones->count(),
                'no_leidas' => Notificacion::contarNoLeidas($user->id_personal_empresa)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en historial notificaciones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar el historial'
            ], 500);
        }
    }
}