<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TopClientesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $clientes;
    protected $fechas;
    protected $top;
    protected $searchCliente;
    protected $indicacionId;

    public function __construct($clientes, $fechas, $top = 10, $searchCliente = null, $indicacionId = null)
    {
        $this->clientes = $clientes;
        $this->fechas = $fechas;
        $this->top = $top;
        $this->searchCliente = $searchCliente;
        $this->indicacionId = $indicacionId;
    }

    public function collection()
    {
        return $this->clientes;
    }

    public function headings(): array
    {
        return [
            'ID Cliente',
            'Nombre',
            'Apellido Paterno',
            'Apellido Materno',
            'Total Transacciones',
            'Monto Total',
            'Ticket Promedio',
            'Primera Compra',
            'Última Compra'
        ];
    }

    public function map($cliente): array
    {
        $cliente = (object) $cliente;
        
        return [
            $cliente->id_Cliente ?? '',
            $cliente->Nombre ?? '',
            $cliente->apPaterno ?? '',
            $cliente->apMaterno ?? '',
            $cliente->total_transacciones ?? 0,
            number_format($cliente->monto_total ?? 0, 2),
            number_format($cliente->ticket_promedio ?? 0, 2),
            $cliente->primera_compra ?? '-',
            $cliente->ultima_compra ?? '-'
        ];
    }
}