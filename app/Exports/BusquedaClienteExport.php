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

class BusquedaClienteExport implements FromCollection, WithCustomStartCell, WithEvents
{
    private const FILA_INICIO_TABLA = 13;

    public function __construct(
        private readonly array $cliente,
        private readonly array $datos,
        private readonly string $fechaInicio,
        private readonly string $fechaFin
    ) {}

    public function collection(): Collection
    {
        return collect($this->datos['articulos'] ?? [])->map(fn ($a) => [
            $a['ean'] ?? '—',
            $a['descripcion'] ?? '—',
            (int) ($a['repeticiones'] ?? 0),
            (float) ($a['importe'] ?? 0),
        ]);
    }

    public function startCell(): string
    {
        return 'A' . (self::FILA_INICIO_TABLA + 1);
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
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'Búsqueda por Cliente — Reporte de Compras');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A3', 'Cliente:');
        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->mergeCells('B3:D3');
        $sheet->setCellValue('B3', $this->cliente['nombre_completo'] ?? '—');

        $telefonos = collect([$this->cliente['telefono1'] ?? null, $this->cliente['telefono2'] ?? null])
            ->filter()->implode(' / ');

        $sheet->setCellValue('A4', 'Teléfono(s):');
        $sheet->getStyle('A4')->getFont()->setBold(true);
        $sheet->mergeCells('B4:D4');
        $sheet->setCellValue('B4', $telefonos ?: 'Sin teléfono');

        $sheet->setCellValue('A5', 'Período:');
        $sheet->getStyle('A5')->getFont()->setBold(true);
        $sheet->mergeCells('B5:D5');
        $sheet->setCellValue('B5', $this->fechaInicio . ' — ' . $this->fechaFin);
    }

    private function escribirKpis($sheet): void
    {
        $sheet->mergeCells('A7:D7');
        $sheet->setCellValue('A7', 'KPIs del período');
        $sheet->getStyle('A7')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A7')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E1F2');
        $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $top = $this->datos['kpis']['articulo_top'] ?? null;
        $resumen = $this->datos['resumen'] ?? [];

        $sheet->setCellValue('A8', 'Artículo más comprado:');
        $sheet->getStyle('A8')->getFont()->setBold(true);
        $sheet->mergeCells('B8:D8');
        $sheet->setCellValue('B8', $top
            ? "{$top['ean']} — {$top['descripcion']} (" . number_format((float) $top['cantidad'], 0) . " unidades, $" . number_format((float) $top['importe'], 2) . ")"
            : '—');

        $sheet->setCellValue('A9', 'Importe total:');
        $sheet->getStyle('A9')->getFont()->setBold(true);
        $sheet->mergeCells('B9:D9');
        $sheet->setCellValue('B9', (float) ($resumen['importe_total'] ?? 0));

        $sheet->setCellValue('A10', 'Tickets / Artículos:');
        $sheet->getStyle('A10')->getFont()->setBold(true);
        $sheet->mergeCells('B10:D10');
        $sheet->setCellValue('B10', (int) ($resumen['total_tickets'] ?? 0) . ' tickets · ' . (int) ($resumen['total_lineas'] ?? 0) . ' artículos vendidos');
    }

    private function escribirHeadersTabla($sheet): void
    {
        $fila = self::FILA_INICIO_TABLA;

        $sheet->mergeCells("A" . ($fila - 2) . ":D" . ($fila - 2));
        $sheet->setCellValue("A" . ($fila - 2), 'Artículos comprados');
        $sheet->getStyle("A" . ($fila - 2))->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A" . ($fila - 2))->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9E1F2');
        $sheet->getStyle("A" . ($fila - 2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = ['EAN', 'Descripción', 'Repeticiones', 'Importe'];
        foreach ($headers as $i => $titulo) {
            $col = chr(65 + $i);
            $sheet->setCellValue("{$col}{$fila}", $titulo);
        }
    }

    private function aplicarEstilos($sheet): void
    {
        $fila = self::FILA_INICIO_TABLA;
        $totalFilas = $fila + count($this->datos['articulos'] ?? []);

        $sheet->getStyle("A{$fila}:D{$fila}")
            ->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle("A{$fila}:D{$fila}")
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF305496');
        $sheet->getStyle("A{$fila}:D{$fila}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        if ($totalFilas >= $fila) {
            $sheet->getStyle("A{$fila}:D{$totalFilas}")
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }
    }

    private function aplicarAutofiltro($sheet): void
    {
        $totalFilas = self::FILA_INICIO_TABLA + count($this->datos['articulos'] ?? []);
        $sheet->setAutoFilter('A' . self::FILA_INICIO_TABLA . ':D' . $totalFilas);
    }

    private function aplicarFormatos($sheet): void
    {
        $first = self::FILA_INICIO_TABLA + 1;
        $last = self::FILA_INICIO_TABLA + count($this->datos['articulos'] ?? []);

        if ($last >= $first) {
            $sheet->getStyle("C{$first}:C{$last}")->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER);
            $sheet->getStyle("D{$first}:D{$last}")->getNumberFormat()
                ->setFormatCode('"$"#,##0.00');
            $sheet->getStyle("C{$first}:C{$last}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("D{$first}:D{$last}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        $sheet->getStyle('B9')->getNumberFormat()->setFormatCode('"$"#,##0.00');
    }

    private function ajustarAnchos($sheet): void
    {
        $anchos = ['A' => 40, 'B' => 40, 'C' => 14, 'D' => 16];
        foreach ($anchos as $col => $ancho) {
            $sheet->getColumnDimension($col)->setWidth($ancho);
        }
    }
}