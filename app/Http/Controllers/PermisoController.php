<?php

namespace App\Http\Controllers;

use App\Models\PersonalEmpresa;
use Illuminate\View\View;

class PermisoController extends Controller
{
    /**
     * Muestra todos los usuarios con sus permisos asignados (solo vista)
     */
    public function index(): View
    {
        $usuarios = PersonalEmpresa::with('permisosGranulares')
            ->orderBy('id_personal_empresa', 'asc')
            ->get();
        
        return view('seguridad.permisos.index', compact('usuarios'));
    }
}