<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CotizacionesClienteExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $clientes;
    protected $fechas;
    protected $statusFilter;

    public function __construct($clientes, $fechas, $statusFilter = 'todos')
    {
        $this->clientes = $clientes;
        $this->fechas = $fechas;
        $this->statusFilter = $statusFilter;
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
            'Total Cotizaciones',
            'Importe Total',
            'Ticket Promedio',
            'Última Cotización'
        ];
    }

    public function map($cliente): array
    {
        $cliente = (object) $cliente;
        
        return [
            $cliente->id_Cliente,
            $cliente->Nombre,
            $cliente->apPaterno,
            $cliente->apMaterno ?? '',
            $cliente->total_cotizaciones,
            $cliente->importe_total,
            $cliente->ticket_promedio,
            $cliente->ultima_cotizacion ? date('d/m/Y', strtotime($cliente->ultima_cotizacion)) : '-'
        ];
    }
}