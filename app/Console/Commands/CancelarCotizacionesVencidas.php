<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cotizaciones\Cotizacion;
use App\Models\Cotizaciones\CatFase;
use App\Models\Configuracion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CancelarCotizacionesVencidas extends Command
{
    protected $signature = 'cotizaciones:cancelar-vencidas';
    protected $description = 'Cancelar cotizaciones con fase "En Proceso" luego de un tiempo determinado';

    public function handle()
    {
        // Obtener días de cancelación desde configuración
        $diasCancelacion = Configuracion::getValor('dias_cancelacion_cotizacion');
        
        // Si no hay configuración o está desactivada, no hacer nada
        if ($diasCancelacion === null) {
            $this->info('Configuración "dias_cancelacion_cotizacion" no encontrada o desactivada');
            return;
        }
        
        $this->info("Cancelando cotizaciones con más de {$diasCancelacion} días en 'En proceso'");
        
        // Calcular fecha límite
        $fechaLimite = Carbon::now()->subDays($diasCancelacion);
        
        // Obtener ID de la fase "Cancelada"
        $faseCancelada = CatFase::where('fase', 'Cancelada')->first();
        
        if (!$faseCancelada) {
            $this->error('No se encontró la fase "Cancelada"');
            return;
        }
        
        // Buscar cotizaciones en proceso que superan el tiempo límite
        $cotizaciones = Cotizacion::where('id_fase', function($query) {
                $query->select('id_fase')
                    ->from('cat_fases')
                    ->where('fase', 'En proceso')
                    ->limit(1);
            })
            ->where('activo', 1)
            ->where('enviado', 0)
            ->where('es_pedido', '!=', 1)
            ->where('fecha_creacion', '<', $fechaLimite)
            ->get();
        
        $contador = 0;
        
        foreach ($cotizaciones as $cotizacion) {
            $comentarioOriginal = $cotizacion->comentarios ?? '';
            $nuevoComentario = trim($comentarioOriginal . "\n[AUTOMÁTICO] Cancelada por superar los {$diasCancelacion} días en estado 'En proceso'");
            
            $cotizacion->update([
                'id_fase' => $faseCancelada->id_fase,
                'comentarios' => $nuevoComentario,
                'modificado_por' => null, // Sistema
            ]);
            
            $contador++;
            $this->info("Cancelada cotización ID: {$cotizacion->id_cotizacion} - Folio: {$cotizacion->folio}");
        }
        
        $this->info("Total canceladas: {$contador} cotizaciones");
        
        if ($contador > 0) {
            Log::info("Cancelación automática: {$contador} cotizaciones canceladas (límite: {$diasCancelacion} días)");
        }
    }
}