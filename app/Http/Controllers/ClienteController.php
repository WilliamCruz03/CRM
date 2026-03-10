<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClienteController extends Controller
{
    //
    public function index()
    {
        // Lógica para mostrar la lista de clientes
        return view("clientes.index");
    }

    public function show($id)
    {
        // Lógica para mostrar los detalles de un cliente específico
        return view("clientes.show");
    }

    public function edit($id)
    {
        // Ya no necesitamos retornar una vista, pero mantenemos el método
        // por si necesitamos obtener datos para el modal
        return response()->json(['id' => $id]);
    }

    public function update(Request $request, $id)
    {
        // Lógica para actualizar un cliente específico
        return redirect()->route("clientes.index")->with("success", "Cliente actualizado");
    }

    public function store(Request $request)
    {
        // Lógica para almacenar un nuevo cliente
        return redirect()->route("clientes.index")->with("success", "Cliente creado");
    }

    public function destroy($id)
    {
        // Lógica para eliminar un cliente específico
        return redirect()->route("clientes.index")->with("success", "Cliente eliminado");
    }
}
