<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PedidosDirectosExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private readonly Collection $filas,
        private readonly array $encabezados,
        private readonly string $inicio,
        private readonly string $fin,
    ) {}

    public function title(): string
    {
        return 'Resumen por sucursal';
    }

    public function collection(): Collection
    {
        return $this->filas;
    }

    public function headings(): array
    {
        return [['Reporte Pedidos con anticipo'], ["Del {$this->inicio} al {$this->fin}"], $this->encabezados];
    }

    public function columnFormats(): array
    {
        return ['G' => '"$"#,##0.00', 'H' => '"$"#,##0.00', 'I' => '"$"#,##0.00'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('C4');
        $ultimaFila = $sheet->getHighestRow();
        if ($sheet->getCell('B' . $ultimaFila)->getValue() === 'General') {
            $sheet->getStyle('A' . $ultimaFila . ':I' . $ultimaFila)->getFont()->setBold(true);
            $ultimaFila--;
        }
        $sheet->setAutoFilter('A3:I' . $ultimaFila);

        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            3 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFD9E1F2']]],
        ];
    }
}
