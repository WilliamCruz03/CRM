<?php

namespace App\Exports;

use App\Models\Reportes\HistorialVenta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class VentasClienteExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $fechaInicio;
    protected $fechaFin;
    protected $top;
    protected $sortBy;

    public function __construct($fechaInicio, $fechaFin, $top = 'todos', $sortBy = 'monto_total')
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->top = $top;
        $this->sortBy = $sortBy;
    }

    public function collection()
    {
        $clientes = HistorialVenta::getResumenClientes($this->fechaInicio, $this->fechaFin);
        
        // Aplicar ordenamiento si es necesario (aunque ya viene ordenado de la consulta)
        switch ($this->sortBy) {
            case 'monto_total':
                $clientes = $clientes->sortByDesc('monto_total');
                break;
            case 'monto_total_asc':
                $clientes = $clientes->sortBy('monto_total');
                break;
            case 'total_transacciones':
                $clientes = $clientes->sortByDesc('total_transacciones');
                break;
            case 'total_transacciones_asc':
                $clientes = $clientes->sortBy('total_transacciones');
                break;
        }
        
        // Aplicar TOP
        if ($this->top !== 'todos') {
            $clientes = $clientes->take((int)$this->top);
        }
        
        return $clientes;
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