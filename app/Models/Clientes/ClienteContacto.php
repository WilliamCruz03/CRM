<?php

namespace App\Models\Clientes;

use App\Models\Cliente;
use App\Models\CatAgendaTipo;
use Illuminate\Database\Eloquent\Model;

class ClienteContacto extends Model
{
    protected $table = 'crm_clientes_contacto';
    protected $primaryKey = 'id';
    public $timestamps = true;
    
    protected $fillable = [
        'id_cliente',
        'id_tipo_contacto'
    ];
    
    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_Cliente');
    }
    
    public function tipoContacto()
    {
        return $this->belongsTo(CatAgendaTipo::class, 'id_tipo_contacto', 'id_tipo');
    }
}