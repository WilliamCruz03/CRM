<?php

namespace App\Http\Controllers\Ventas;

use App\Models\Pedidos\OrdenPedido;
use App\Models\Cotizaciones\MensajeTicketSobrePedido;
use App\Services\Ventas\RegistrarPedidoDirecto;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\FrameDecorator\AbstractFrameDecorator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TicketPedidoDirectoController
{
    public function __invoke(Request $request, int $id): Response
    {
        abort_unless(auth()->user()->puede('ventas', 'pedidos_anticipo', 'ver'), 403);

        $pedido = OrdenPedido::pedidosDirectosVisibles($request->user())
            ->with(['clienteDirecto', 'creador', 'sucursalOrigen', 'detalles' => fn ($query) => $query->where('se_elimino', false)])
            ->findOrFail($id);

        $detalles = $pedido->detalles;
        $total = $detalles->sum('importe');
        $cumpleUmbralPublico = (int) round($total * 100) >= RegistrarPedidoDirecto::UMBRAL_PROMOCION_PUBLICO_CENTAVOS;
        $mostrarMensaje = $detalles->contains(fn ($detalle) => $detalle->id_convenio !== null) || $cumpleUmbralPublico;
        $html = view('ventas.pedidos-directos.ticket', [
            'pedido' => $pedido,
            'detalles' => $detalles,
            'total' => $total,
            'cumpleUmbralPublico' => $cumpleUmbralPublico,
            'categoriasPublico' => (config('pedidos_directos.categorias') ?? (require config_path('pedidos_directos.php'))['categorias'])['publico'],
            'logo' => $this->logoMonocromatico(),
            'mensajePromocional' => $mostrarMensaje ? MensajeTicketSobrePedido::mensajeVigente(today()->toDateString()) : null,
        ])->render();

        $puntosPorMm = 72 / 25.4;
        $papel = [0, 0, 80 * $puntosPorMm, 1000 * $puntosPorMm];
        $pdf = Pdf::loadHTML($html)->setPaper($papel);
        $finContenido = null;
        $pdf->getDomPDF()->setCallbacks([[
            'event' => 'end_frame',
            'f' => static function (AbstractFrameDecorator $frame) use (&$finContenido): void {
                if ($frame->get_node() instanceof \DOMElement && $frame->get_node()->getAttribute('id') === 'fin-ticket') {
                    $finContenido = $frame->get_position('y') + $frame->get_margin_height();
                }
            },
        ]]);
        $pdf->render();

        if ($finContenido !== null && $pdf->getDomPDF()->getCanvas()->get_page_count() === 1) {
            $papel[3] = min($papel[3], ceil($finContenido + 6 * $puntosPorMm));
            $pdf = Pdf::loadHTML($html)->setPaper($papel);
        }

        return $pdf->stream("sobre-pedido-{$pedido->folio_pedido}.pdf");
    }

    private function logoMonocromatico(): ?string
    {
        $ruta = public_path('images/logo-fp-normal.png');
        if (!is_file($ruta) || !function_exists('imagecreatefrompng')) {
            return null;
        }

        $logo = imagecreatefrompng($ruta);
        if ($logo === false) {
            return null;
        }

        imagepalettetotruecolor($logo);
        imagealphablending($logo, false);
        imagesavealpha($logo, true);
        imagefilter($logo, IMG_FILTER_COLORIZE, -255, -255, -255);

        ob_start();
        imagepng($logo);

        return 'data:image/png;base64,' . base64_encode(ob_get_clean());
    }
}
