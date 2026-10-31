<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BusquedaArticuloExport implements FromCollection, WithEvents
{
    private const FILA_INICIO_TABLA = 6;

    public function __construct(
        private readonly array $articulo,
        private readonly array $kpis,
        private readonly Collection $clientes,
        private readonly string $sortBy
    ) {}

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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $this->escribirEncabezado($sheet);
                $this->escribirKpis($sheet);
                $this->escribirHeadersTabla($sheet);
                $this->aplicarAutofiltro($sheet);
                $this->aplicarFormatos($sheet);
            },
        ];
    }

    private function escribirEncabezado($sheet): void
    {
        $sheet->setCellValue('A1', 'Código:');
        $sheet->setCellValue('B1', $this->articulo['ean']);
        $sheet->setCellValue('A2', 'Descripción:');
        $sheet->setCellValue('B2', $this->articulo['descripcion']);
        $sheet->setCellValue('A3', 'Orden aplicado:');
        $sheet->setCellValue('B3', $this->sortBy);
    }

    private function escribirKpis($sheet): void
    {
        $sheet->setCellValue('A4', 'KPIs');
        $sheet->getStyle('A4')->getFont()->setBold(true);

        $caro = $this->kpis['articulo_mayor_costo'] ?? null;
        $inv = $this->kpis['inventario_por_costo'] ?? [];
        $masVendido = $this->kpis['producto_mayor_venta'] ?? null;

        $costoTotal = collect($inv)->sum(fn ($r) => (float) ($r['costo_total'] ?? 0));
        $unidades = collect($inv)->sum(fn ($r) => (float) ($r['unidades'] ?? 0));

        $sheet->setCellValue('A5', 'Artículo mayor costo:');
        $sheet->setCellValue('B5', $caro ? "{$caro['ean']} — {$caro['descripcion']} (costo: {$caro['costo']})" : '—');

        $sheet->setCellValue('C5', 'Costo compra vendido:');
        $sheet->setCellValue('D5', $costoTotal);
        $sheet->setCellValue('E5', 'Unidades:');
        $sheet->setCellValue('F5', $unidades);

        $sheet->setCellValue('G5', 'Producto mayor venta:');
        $sheet->setCellValue('H5', $masVendido ? "{$masVendido['ean']} — {$masVendido['descripcion']} (monto: {$masVendido['monto']})" : '—');
    }

    private function escribirHeadersTabla($sheet): void
    {
        $headers = [
            'Cliente', 'Teléfono', 'Sucursal', 'Tickets', 'Frecuencia (días)',
            'Primera compra', 'Última compra', 'Importe',
        ];

        foreach ($headers as $i => $titulo) {
            $col = chr(65 + $i);
            $sheet->setCellValue("{$col}" . self::FILA_INICIO_TABLA, $titulo);
        }

        $lastCol = chr(65 + count($headers) - 1);
        $sheet->getStyle('A' . self::FILA_INICIO_TABLA . ":{$lastCol}" . self::FILA_INICIO_TABLA)
            ->getFont()->setBold(true);
        $sheet->getStyle('A' . self::FILA_INICIO_TABLA . ":{$lastCol}" . self::FILA_INICIO_TABLA)
            ->getFill()->setFillType('solid')
            ->getStartColor()->setARGB('FFE7E6E6');
    }

    private function aplicarAutofiltro($sheet): void
    {
        $totalFilas = self::FILA_INICIO_TABLA + $this->clientes->count();
        $sheet->setAutoFilter('A' . self::FILA_INICIO_TABLA . ':H' . $totalFilas);
    }

    private function aplicarFormatos($sheet): void
    {
        $first = self::FILA_INICIO_TABLA + 1;
        $last = self::FILA_INICIO_TABLA + $this->clientes->count();

        if ($last < $first) return;

        $sheet->getStyle("D{$first}:D{$last}")->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER);
        $sheet->getStyle("E{$first}:E{$last}")->getNumberFormat()
            ->setFormatCode('0.00');
        $sheet->getStyle("F{$first}:G{$last}")->getNumberFormat()
            ->setFormatCode('dd/mm/yyyy');
        $sheet->getStyle("H{$first}:H{$last}")->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');

        foreach (['A', 'B', 'C'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function fechaExcel($fecha): ?\DateTimeInterface
    {
        if (empty($fecha)) return null;
        try {
            return \Carbon\Carbon::parse($fecha)->toDateTime();
        } catch (\Throwable) {
            return null;
        }
    }
}