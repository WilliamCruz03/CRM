<?php
// app/Exports/VentasClienteExport.php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class VentasClienteExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $clientes;
    protected $fechas;
    protected $top;
    protected $sortBy;
    protected $searchCliente;
    protected $indicacionId;

    public function __construct($clientes, $fechas, $top = 'todos', $sortBy = 'monto_total', $searchCliente = null, $indicacionId = null)
    {
        $this->clientes = $clientes;
        $this->fechas = $fechas;
        $this->top = $top;
        $this->sortBy = $sortBy;
        $this->searchCliente = $searchCliente;
        $this->indicacionId = $indicacionId;
    }

    public function collection()
    {
        return $this->clientes;
    }

    public function headings(): array
    {
        $headings = [
            'ID Cliente',
            'Nombre',
            'Apellido Paterno',
            'Apellido Materno',
            'Ventas Totales',
            'Monto Total',
            'Ticket Promedio',
            'Primera Compra',
            'Última Compra'
        ];
        
        return $headings;
    }

    public function map($cliente): array
    {
        return [
            $cliente->id_Cliente,
            $cliente->Nombre,
            $cliente->apPaterno,
            $cliente->apMaterno ?? '',
            $cliente->total_transacciones,
            $cliente->monto_total,
            $cliente->ticket_promedio,
            $cliente->primera_compra,
            $cliente->ultima_compra
        ];
    }
}