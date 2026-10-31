<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BusquedaArticuloExport implements FromCollection, WithCustomStartCell, WithEvents
{
    private readonly int $filaInicioTabla;

    public function __construct(
        private readonly array $articulo,
        private readonly array $kpis,
        private readonly Collection $clientes,
        private readonly string $sortBy
    ) {
        $sucursales = count($kpis['inventario_por_costo'] ?? []);
        $filasInventario = $sucursales > 0 ? $sucursales + 1 : 0; // +1 por fila Total
        $this->filaInicioTabla = 14 + $filasInventario;
    }

    public function collection(): Collection
    {
        return $this->clientes->map(fn ($c) => [
            $c->nombre_cliente ?? 'CLIENTE NO IDENTIFICADO',
            $c->telefono ?? '—',
            $c->sucursal ?? '—',
            (int) ($c->compras ?? 0),
            $c->frecuencia !== null ? round((float) $c->frecuencia, 2) : null,
            $this->fechaExcel($c->primera_compra),
            $this->fechaExcel($c->ultima_compra),
            (float) ($c->monto_total ?? 0),
        ]);
    }

    public function startCell(): string
    {
        return 'A' . ($this->filaInicioTabla + 1);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $this->escribirEncabezado($sheet);
                $this->escribirKpis($sheet);
                $this->escribirHeadersTabla($sheet);
                $this->aplicarEstilos($sheet);
                $this->aplicarAutofiltro($sheet);
                $this->aplicarFormatos($sheet);
                $this->ajustarAnchos($sheet);
            },
        ];
    }

    private function escribirEncabezado($sheet): void
    {
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'Búsqueda por Artículo — Reporte de Compras');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A3', 'Código:');
        $sheet->setCellValue('B3', $this->articulo['ean']);
        $sheet->getStyle('A3')->getFont()->setBold(true);

        $sheet->mergeCells('D3:H3');
        $sheet->setCellValue('D3', 'Descripción: ' . $this->articulo['descripcion']);
        $sheet->getStyle('D3')->getFont()->setBold(true);
    }

    private function escribirKpis($sheet): void
    {
        $sheet->mergeCells('A5:H5');
        $sheet->setCellValue('A5', 'KPIs del período');
        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A5')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E1F2');
        $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $caro = $this->kpis['articulo_mayor_costo'] ?? null;
        $inv = collect($this->kpis['inventario_por_costo'] ?? []);
        $masVendido = $this->kpis['producto_mayor_venta'] ?? null;

        $sheet->setCellValue('A6', 'Artículo con mayor costo:');
        $sheet->getStyle('A6')->getFont()->setBold(true);
        $sheet->mergeCells('B6:H6');
        $sheet->setCellValue('B6', $caro
            ? "{$caro['ean']} — {$caro['descripcion']} (costo: " . number_format((float) $caro['costo'], 2) . ")"
            : '—');

        $sheet->setCellValue('A8', 'Inventario por costo:');
        $sheet->getStyle('A8')->getFont()->setBold(true);

        $fila = 9;
        if ($inv->isNotEmpty()) {
            $sheet->setCellValue("A{$fila}", 'Sucursal');
            $sheet->setCellValue("B{$fila}", 'Unidades');
            $sheet->setCellValue("C{$fila}", 'Costo total');
            $sheet->getStyle("A{$fila}:C{$fila}")->getFont()->setBold(true);
            $sheet->getStyle("A{$fila}:C{$fila}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF2F2F2');
            $fila++;

            foreach ($inv as $r) {
                $sheet->setCellValue("A{$fila}", $r['sucursal'] ?? '—');
                $sheet->setCellValue("B{$fila}", (float) ($r['unidades'] ?? 0));
                $sheet->setCellValue("C{$fila}", (float) ($r['costo_total'] ?? 0));
                $fila++;
            }

            $totalCosto = $inv->sum(fn ($r) => (float) ($r['costo_total'] ?? 0));
            $totalUnidades = $inv->sum(fn ($r) => (float) ($r['unidades'] ?? 0));

            $sheet->setCellValue("A{$fila}", 'Total');
            $sheet->setCellValue("B{$fila}", $totalUnidades);
            $sheet->setCellValue("C{$fila}", $totalCosto);
            $sheet->getStyle("A{$fila}:C{$fila}")->getFont()->setBold(true);
            $sheet->getStyle("A{$fila}:C{$fila}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFD9E1F2');
            $fila++;
        } else {
            $sheet->setCellValue("A{$fila}", 'Sin ventas en el período');
            $fila++;
            // no agregamos fila Total si no hay sucursales
        }

        $fila++;
        $sheet->setCellValue("A{$fila}", 'Producto con mayor venta:');
        $sheet->getStyle("A{$fila}")->getFont()->setBold(true);
        $fila++;
        $sheet->mergeCells("A{$fila}:H{$fila}");
        $sheet->setCellValue("A{$fila}", $masVendido
            ? "{$masVendido['ean']} — {$masVendido['descripcion']} (monto: " . number_format((float) $masVendido['monto'], 2) . ")"
            : '—');
    }

    private function escribirHeadersTabla($sheet): void
    {
        $headers = [
            'Cliente', 'Teléfono', 'Sucursal', 'Tickets', 'Frecuencia (días)',
            'Primera compra', 'Última compra', 'Importe',
        ];

        foreach ($headers as $i => $titulo) {
            $col = chr(65 + $i);
            $sheet->setCellValue("{$col}{$this->filaInicioTabla}", $titulo);
        }
    }

    private function aplicarEstilos($sheet): void
    {
        $fila = $this->filaInicioTabla;

        $sheet->getStyle("A{$fila}:H{$fila}")
            ->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle("A{$fila}:H{$fila}")
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF305496');
        $sheet->getStyle("A{$fila}:H{$fila}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $lastRow = $fila + $this->clientes->count();
        if ($lastRow >= $fila) {
            $sheet->getStyle("A{$fila}:H{$lastRow}")
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }
    }

    private function aplicarAutofiltro($sheet): void
    {
        $totalFilas = $this->filaInicioTabla + $this->clientes->count();
        $sheet->setAutoFilter("A{$this->filaInicioTabla}:H{$totalFilas}");
    }

    private function aplicarFormatos($sheet): void
    {
        $first = $this->filaInicioTabla + 1;
        $last = $this->filaInicioTabla + $this->clientes->count();

        if ($last < $first) return;

        $sheet->getStyle("D{$first}:D{$last}")->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER);
        $sheet->getStyle("E{$first}:E{$last}")->getNumberFormat()
            ->setFormatCode('0.00');
        $sheet->getStyle("H{$first}:H{$last}")->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');

        $sheet->getStyle("D{$first}:E{$last}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("H{$first}:H{$last}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        if (isset($this->kpis['inventario_por_costo']) && count($this->kpis['inventario_por_costo']) > 0) {
            $invStart = 10;
            $invEnd = $invStart + count($this->kpis['inventario_por_costo']) - 1 + 1;
            $sheet->getStyle("C{$invStart}:C{$invEnd}")->getNumberFormat()
                ->setFormatCode('"$"#,##0.00');
        }
    }

    private function ajustarAnchos($sheet): void
    {
        $anchos = [
            'A' => 32, 'B' => 16, 'C' => 14, 'D' => 10,
            'E' => 14, 'F' => 14, 'G' => 14, 'H' => 16,
        ];
        foreach ($anchos as $col => $ancho) {
            $sheet->getColumnDimension($col)->setWidth($ancho);
        }
    }

    private function fechaExcel($fecha): ?string
    {
        if (empty($fecha)) return null;
        try {
            return \Carbon\Carbon::parse($fecha)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}