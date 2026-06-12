<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaisController extends Controller
{
    public function index(Request $request)
    {
        $paises = \DB::connection('sqlsrvM')
            ->table('cat_paises')
            ->where('status', 1)
            ->orderBy('pais')
            ->get(['id as value', 'pais as text']);
        
        return response()->json($paises);
    }
}