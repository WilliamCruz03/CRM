<?php

namespace App\Exports;

use App\Models\Ventas\HistorialVenta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class VentasClienteExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($fechaInicio, $fechaFin)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function collection()
    {
        return HistorialVenta::getResumenClientes($this->fechaInicio, $this->fechaFin);
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