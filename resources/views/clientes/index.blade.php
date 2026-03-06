@extends('layouts.app')

@section('title', 'Clientes - CRM')
@section('page-title', 'Gestión de Clientes')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <h3><i class="bi bi-people"></i> Gestión de Clientes</h3>
        <p class="text-muted">Gestión y alta de nuevos clientes</p>
    </div>

    <!-- Search and Actions -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" placeholder="Buscar por nombre, correo o teléfono...">
            </div>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoCliente">
                <i class="bi bi-plus-circle"></i> Nuevo Cliente
            </button>
        </div>
    </div>

    <!-- Tabla de Clientes -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Contacto</th>
                            <th>Dirección</th>
                            <th>Enfermedades</th>
                            <th>Preferencias</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Cliente 1: Maria Gonzalez -->
                        <tr>
                            <td><span class="badge bg-secondary">1024</span></td>
                            <td>
                                <strong>Maria Gonzalez</strong>
                            </td>
                            <td>
                                <div class="small">
                                    <i class="bi bi-envelope text-muted"></i> maria.gonzalez@gmail.com<br>
                                    <i class="bi bi-telephone text-muted"></i> 123 456 789
                                </div>
                            </td>
                            <td>
                                <small>Zona Centro, Tamazunchale</small>
                            </td>
                            <td>
                                <div class="small">
                                    <span class="badge bg-danger">Hipertensión</span><br>
                                    <span class="badge bg-warning text-dark">Diabetes Tipo 2</span><br>
                                    <span class="badge bg-info">Alergia</span>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="bi bi-whatsapp text-success"></i> Prefiere ser contactado solo por WhatsApp
                                </small>
                            </td>
                            <td>
                                <span class="badge-status badge-active">Activo</span>
                            </td>
                            <td>
                                <a href="{{ route('clientes.show', 1024) }}" class="btn btn-sm btn-outline-info btn-action" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('clientes.edit', 1024) }}" class="btn btn-sm btn-outline-primary btn-action" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger btn-action" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Cliente 2: Carlos Ramírez -->
                        <tr>
                            <td><span class="badge bg-secondary">1023</span></td>
                            <td>
                                <strong>Carlos Ramírez</strong>
                            </td>
                            <td>
                                <div class="small">
                                    <i class="bi bi-envelope text-muted"></i> carlos.ramirez@gmail.com<br>
                                    <i class="bi bi-telephone text-muted"></i> 818 765 4321
                                </div>
                            </td>
                            <td>
                                <small>Loma bonita, Tamazunchale</small>
                            </td>
                            <td>
                                <span class="text-muted small">-</span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    Interes en articulos para bebé.
                                </small>
                            </td>
                            <td>
                                <span class="badge-status badge-active">Activo</span>
                            </td>
                            <td>
                                <a href="{{ route('clientes.show', 1023) }}" class="btn btn-sm btn-outline-info btn-action">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('clientes.edit', 1023) }}" class="btn btn-sm btn-outline-primary btn-action">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger btn-action">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Cliente 3: Ana López -->
                        <tr>
                            <td><span class="badge bg-secondary">1022</span></td>
                            <td>
                                <strong>Ana López</strong>
                            </td>
                            <td>
                                <div class="small">
                                    <i class="bi bi-envelope text-muted"></i> ana.lopez@gmail.com<br>
                                    <i class="bi bi-telephone text-muted"></i> 332 211 4155
                                </div>
                            </td>
                            <td>
                                <small>Zona Centro, Tamazunchale</small>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark">Diabetes T2</span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    Le interesa recibir notificaciones para comprar mas medicamento
                                </small>
                            </td>
                            <td>
                                <span class="badge-status badge-inactive">Inactivo</span>
                            </td>
                            <td>
                                <a href="{{ route('clientes.show', 1022) }}" class="btn btn-sm btn-outline-info btn-action">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('clientes.edit', 1022) }}" class="btn btn-sm btn-outline-primary btn-action">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger btn-action">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Cliente 4: Jorge Hernández -->
                        <tr>
                            <td><span class="badge bg-secondary">1021</span></td>
                            <td>
                                <strong>Jorge Hernández</strong>
                            </td>
                            <td>
                                <div class="small">
                                    <i class="bi bi-envelope text-muted"></i> jorge.hdz@gmail.com<br>
                                    <i class="bi bi-telephone text-muted"></i> 559 876 5432
                                </div>
                            </td>
                            <td>
                                <small>-</small>
                            </td>
                            <td>
                                <span class="text-muted small">-</span>
                            </td>
                            <td>
                                <span class="text-muted small">Entregas únicamente en horario matutino</span>
                            </td>
                            <td>
                                <span class="badge-status badge-active">Activo</span>
                            </td>
                            <td>
                                <a href="{{ route('clientes.show', 1021) }}" class="btn btn-sm btn-outline-info btn-action">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('clientes.edit', 1021) }}" class="btn btn-sm btn-outline-primary btn-action">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger btn-action">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3">
            <div class="pagination-info">
                Mostrando 4 registros
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled">
                        <span class="page-link">Anterior</span>
                    </li>
                    <li class="page-item active"><span class="page-link">1</span></li>
                    <li class="page-item"><span class="page-link">2</span></li>
                    <li class="page-item"><span class="page-link">3</span></li>
                    <li class="page-item">
                        <span class="page-link">Siguiente</span>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>
@endsection