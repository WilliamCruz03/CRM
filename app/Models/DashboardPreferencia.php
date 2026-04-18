<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardPreferencia extends Model
{
    protected $table = 'dashboard_preferencias';
    protected $primaryKey = 'id_dashboard_preferencia';
    public $timestamps = true;
    
    protected $fillable = [
        'id_personal_empresa',
        'card_key',
        'mostrar',
        'orden'
    ];
    
    protected $casts = [
        'mostrar' => 'boolean',
        'orden' => 'integer'
    ];
    
    public function usuario()
    {
        return $this->belongsTo(PersonalEmpresa::class, 'id_personal_empresa', 'id_personal_empresa');
    }
}