@extends('layouts.app')

@section('title', 'Bienvenido - CRM')
@section('page-title', 'Bienvenido')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card text-center">
                <div class="card-body py-5">
                    <i class="bi bi-shield-lock" style="font-size: 5rem; color: #6c757d;"></i>
                    <h2 class="mt-4">¡Bienvenido, {{ $usuario }}!</h2>
                    <p class="lead text-muted">
                        Actualmente no tienes acceso a ningún módulo del sistema.
                    </p>
                    <p class="text-muted">
                        Para poder acceder a las funcionalidades del CRM, necesitas que un administrador
                        te asigne los permisos correspondientes.
                    </p>
                    <hr class="my-4">
                    <p class="text-muted small">
                        Si crees que esto es un error, contacta al administrador del sistema.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection