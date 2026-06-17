<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PedidosClienteExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $data;
    protected $filtros;

    public function __construct($data, $filtros)
    {
        $this->data = $data;
        $this->filtros = $filtros;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            '#',
            'Cliente',
            'Total Pedidos',
            'Monto Total',
            'Promedio por Pedido'
        ];
    }

    public function map($item): array
    {
        static $index = 0;
        $index++;
        
        return [
            $index,
            $item['cliente_nombre'] ?? 'N/A',
            $item['total_pedidos'] ?? 0,
            number_format($item['monto_total'] ?? 0, 2),
            number_format($item['monto_promedio'] ?? 0, 2)
        ];
    }
}