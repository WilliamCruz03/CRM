@extends('layouts.app')

@section('title', 'Detalle del Cliente - CRM')
@section('page-title', 'Datos del Cliente')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <h3><i class="bi bi-person-vcard"></i> Datos del Cliente</h3>
        <p class="text-muted">Gestiona el historial médico, alergias, y condiciones especiales del cliente</p>
    </div>

    <!-- Información básica del cliente -->
    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span><i class="bi bi-info-circle text-warning"></i> Información del Cliente</span>
                <button type="button" class="btn btn-warning"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEditarCliente"
                        title="Editar cliente">
                    <i class="bi bi-pencil"> Editar datos generales</i>
                </button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="info-label">Nombre</div>
                    <div class="info-value h5 mb-3">Carlos Ramírez</div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Correo electrónico</div>
                    <div class="info-value">
                        <i class="bi bi-envelope text-primary"></i> carlos.ramirez@gmail.com
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-label">Teléfono</div>
                    <div class="info-value">
                        <i class="bi bi-telephone text-primary"></i> 483 123 4567
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="info-label">Dirección</div>
                    <div class="info-value">
                        <i class="bi bi-geo-alt text-primary"></i> Loma bonita, Tamazunchale
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de padecimientos -->
    <div class="card">
        <div class="card-header bg-white">
            <span><i class="bi bi-heart-pulse"></i> Historial Médico</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Padecimiento o Enfermedad</th>
                            <th>Categoría</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>
                                <span class="fw-medium">Alergia a Penicilina</span>
                            </td>
                            <td>
                                <span class="badge bg-info">Alergia</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-danger btn-action">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>
                                <span class="fw-medium">Diabetes Tipo 2</span>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark">Crónico Degenerativa</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-danger btn-action">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>
                                <span class="fw-medium">Hipertensión Arterial</span>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark">Crónico Degenerativa</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-danger btn-action">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <button class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus"></i> Agregar Enfermedad
            </button>
        </div>
    </div>

    <!-- Botones de navegación -->
    <div class="mt-4">
        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver al listado
        </a>
    </div>
</div>
@endsection
