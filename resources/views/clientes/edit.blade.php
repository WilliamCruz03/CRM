@extends('layouts.app')

@section('title', 'Editar Cliente - CRM')
@section('page-title', 'Editar Cliente')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="page-header">
        <h3><i class="bi bi-pencil-square"></i> Editar Cliente</h3>
        <p class="text-muted">Modifica la información del cliente</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" value="Carlos">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Apellidos</label>
                        <input type="text" class="form-control" value="Ramirez">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Calle</label>
                    <input type="text" class="form-control" value="Calle S/N">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Colonia/Barrio/Localidad</label>
                        <input type="text" class="form-control" value="Barrio San Juan">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ciudad/Municipio</label>
                        <input type="text" class="form-control" value="Tamazunchale">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" value="carlosramirez@gmail.com">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" value="818 765 4321">
                    </div>
                </div>

                <hr class="my-4">

                <h6 class="mb-3">Datos clínicos</h6>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Padecimiento</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>
                                    <select class="form-select">
                                        <option selected>Alergia a Penicilina</option>
                                        <option>Diabetes Tipo 2</option>
                                        <option>Hipertensión Arterial</option>
                                    </select>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>
                                    <input type="text" class="form-control" value="Diabetes Tipo 2">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>
                                    <input type="text" class="form-control" value="Hipertensión Arterial">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <button type="button" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus"></i> Agregar Enfermedad
                </button>
            </form>
        </div>
        <div class="card-footer bg-white">
            <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Guardar cambios
            </button>
        </div>
    </div>
</div>
@endsection