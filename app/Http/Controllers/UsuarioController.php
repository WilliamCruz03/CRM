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
        $usuarios = PersonalEmpresa::orderBy('id_personal_empresa', 'asc')->get();
        return view('seguridad.usuarios.index', compact('usuarios'));
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $usuario = PersonalEmpresa::with('permisos.accion')->findOrFail($id);
        return view('seguridad.permisos.show', compact('usuario'));
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
            'passw' => 'required|string|min:6',
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
        $usuario = PersonalEmpresa::with('permisosGranulares')->findOrFail($id);
        
        // Obtener permisos formateados
        $permisos = $usuario->permisos_formateados;
        
        // No enviar los campos de contraseña por seguridad
        $usuario->makeHidden(['password', 'passw']);
        
        return response()->json([
            'success' => true,
            'data' => $usuario,
            'permisos' => $permisos  // ← Esto debe llegar al modal
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
            'usuario' => 'required|string|max:15|unique:personal_empresa,usuario,' . $id . ',id_personal_empresa',
            'password' => 'nullable|string|max:30',
            'passw' => 'nullable|string|min:6',
            'permisos_modulos' => 'nullable|array',
        ]);

        // Preparar datos para actualizar
        $datosActualizar = [
            'Nombre' => $validated['Nombre'],
            'ApPaterno' => $validated['ApPaterno'],
            'ApMaterno' => $validated['ApMaterno'] ?? null,
            'Direccion' => $validated['Direccion'] ?? null,
            'Localidad' => $validated['Localidad'] ?? null,
            'Municipio' => $validated['Municipio'] ?? null,
            'TelefonoFijo' => $validated['TelefonoFijo'] ?? null,
            'TelefonoMovil' => $validated['TelefonoMovil'] ?? null,
            'contacto' => $validated['contacto'] ?? null,
            'parentescoDeContacto' => $validated['parentescoDeContacto'] ?? null,
            'TelefonoContacto' => $validated['TelefonoContacto'] ?? null,
            'fecha_ingreso' => $validated['fecha_ingreso'] ?? null,
            'fecha_alta_sistema' => $validated['fecha_alta_sistema'] ?? null,
            'fecha_alta_seguro' => $validated['fecha_alta_seguro'] ?? null,
            'Activo' => $validated['Activo'] ?? $usuario->Activo,
            'fecha_baja' => $validated['fecha_baja'] ?? null,
            'motivo_baja' => $validated['motivo_baja'] ?? null,
            'sucursal_origen' => $validated['sucursal_origen'] ?? $usuario->sucursal_origen,
            'sucursal_asignada' => $validated['sucursal_asignada'] ?? null,
            'curp' => $validated['curp'] ?? null,
            'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
            'usuario' => $validated['usuario'],
        ];

        // Si se envió nueva contraseña
        if (!empty($validated['passw'])) {
            $datosActualizar['passw'] = $validated['passw'];
        }

        $usuario->update($datosActualizar);

        // Sincronizar permisos si se enviaron
        if ($request->has('permisos_modulos')) {
            $usuario->sincronizarPermisos($request->permisos_modulos);
        }

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