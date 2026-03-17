<?php

namespace App\Http\Controllers;

use App\Models\PersonalEmpresa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $usuarios = PersonalEmpresa::orderBy('id', 'asc')->get();
        return view('seguridad.usuarios.index', compact('usuarios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'Nombre' => 'required|string|max:50',
            'ApPaterno' => 'required|string|max:50',
            'ApMaterno' => 'nullable|string|max:50',
            'Direccion' => 'nullable|string|max:100',
            'Localidad' => 'nullable|string|max:80',
            'Municipio' => 'nullable|string|max:60',
            'TelefonoFijo' => 'nullable|string|max:50',
            'TelefonoMovil' => 'nullable|string|max:50',
            'contacto' => 'nullable|string|max:100',
            'parentescoDeContacto' => 'nullable|string|max:50',
            'TelefonoContacto' => 'nullable|string|max:50',
            'fecha_ingreso' => 'nullable|date',
            'fecha_alta_sistema' => 'nullable|date',
            'fecha_alta_seguro' => 'nullable|date',
            'Activo' => 'nullable|boolean',
            'fecha_baja' => 'nullable|date',
            'motivo_baja' => 'nullable|string|max:254',
            'sucursal_origen' => 'nullable|integer',
            'sucursal_asignada' => 'nullable|integer',
            'curp' => 'nullable|string|max:18',
            'fecha_nacimiento' => 'nullable|date',
            'usuario' => 'required|string|max:15|unique:personal_empresa,usuario',
            'password' => 'nullable|string|max:30',
            'passw' => 'required|string|min:6', // Se hashea automáticamente en el modelo
        ]);

        // Valores por defecto
        $validated['sucursal_origen'] = $validated['sucursal_origen'] ?? 0;
        $validated['Activo'] = $validated['Activo'] ?? 1;

        $usuario = PersonalEmpresa::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado correctamente',
            'data' => $usuario
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): JsonResponse
    {
        $usuario = PersonalEmpresa::findOrFail($id);
        
        // No enviar los campos de contraseña por seguridad
        $usuario->makeHidden(['password', 'passw']);
        
        return response()->json([
            'success' => true,
            'data' => $usuario
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $usuario = PersonalEmpresa::findOrFail($id);

        $validated = $request->validate([
            'Nombre' => 'required|string|max:50',
            'ApPaterno' => 'required|string|max:50',
            'ApMaterno' => 'nullable|string|max:50',
            'Direccion' => 'nullable|string|max:100',
            'Localidad' => 'nullable|string|max:80',
            'Municipio' => 'nullable|string|max:60',
            'TelefonoFijo' => 'nullable|string|max:50',
            'TelefonoMovil' => 'nullable|string|max:50',
            'contacto' => 'nullable|string|max:100',
            'parentescoDeContacto' => 'nullable|string|max:50',
            'TelefonoContacto' => 'nullable|string|max:50',
            'fecha_ingreso' => 'nullable|date',
            'fecha_alta_sistema' => 'nullable|date',
            'fecha_alta_seguro' => 'nullable|date',
            'Activo' => 'nullable|boolean',
            'fecha_baja' => 'nullable|date',
            'motivo_baja' => 'nullable|string|max:254',
            'sucursal_origen' => 'nullable|integer',
            'sucursal_asignada' => 'nullable|integer',
            'curp' => 'nullable|string|max:18',
            'fecha_nacimiento' => 'nullable|date',
            'usuario' => 'required|string|max:15|unique:personal_empresa,usuario,' . $id,
            'password' => 'nullable|string|max:30',
            'passw' => 'nullable|string|min:6', // Solo se actualiza si se envía
        ]);

        // Si se envía nueva contraseña, hashearla
        if (isset($validated['passw'])) {
            $validated['passw'] = Hash::make($validated['passw']);
        } else {
            unset($validated['passw']); // No actualizar si no viene
        }

        $usuario->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado correctamente',
            'data' => $usuario
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $usuario = PersonalEmpresa::findOrFail($id);
        $usuario->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuario eliminado correctamente'
        ]);
    }
}