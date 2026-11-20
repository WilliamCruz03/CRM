<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder, WithHeadings, WithStyles, WithTitle};
use PhpOffice\PhpSpreadsheet\Cell\{Cell, DataType, DefaultValueBinder};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PedidosDirectosDetalleExport extends DefaultValueBinder implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder, WithHeadings, WithStyles, WithTitle
{
    public function __construct(private readonly Collection $filas) {}

    public function title(): string
    {
        return 'Detalle de artículos';
    }

    public function collection(): Collection
    {
        return $this->filas;
    }

    public function headings(): array
    {
        return ['Sucursal', 'Cliente', 'Folio del pedido', 'Código de barras', 'Descripción',
            'Cantidad', 'Importe estimado', 'Vendido', 'Número de ticket', 'Fecha de encargo'];
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return ['G' => '"$"#,##0.00', 'J' => 'dd/mm/yyyy'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:J' . $sheet->getHighestRow());

        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFD9E1F2']]]];
    }
}
