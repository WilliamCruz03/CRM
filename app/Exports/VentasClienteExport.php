<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class VentasClienteExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithChunkReading
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

    public function chunkSize(): int
    {
        return 500;
    }

    public function headings(): array
    {
        return [
            'ID Cliente',
            'Nombre',
            'Apellido Paterno',
            'Apellido Materno',
            'Ventas Totales',
            'Monto Total',
            'Ticket Promedio',
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
            $cliente->ultima_compra ?? '-'
        ];
    }
}