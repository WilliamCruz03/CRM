<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #5170ff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h3 {
            color: #333;
            font-weight: 600;
        }
        .login-header p {
            color: #666;
            font-size: 0.9rem;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .form-control:focus {
            border-color: #5170ff;
            box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25);
        }
        .btn-login {
            background: #005BAA;
            border: none;
            border-radius: 10px;
            padding: 12px;
            color: white;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
        }
        .error-message {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="bi bi-speedometer2" style="font-size: 3rem; color: #667eea;"></i>
            <h3 class="mt-3">CRM Sistema</h3>
            <p>Inicia sesión para continuar</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first('usuario') }}
            </div>
        @endif

        @if(request()->has('expired'))
        <div class="alert alert-danger">
            Tu sesión ha caducado. Dudas o aclaraciones favor de comunicarse al área de TICS.
        </div>
    @endif
    

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Usuario</label>
                <input type="text" class="form-control" name="usuario" value="{{ old('usuario') }}" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" class="form-control" name="password" required>
            </div>

            <button type="submit" class="btn-login">
                Ingresar
            </button>
        </form>
    </div>
</body>
</html>