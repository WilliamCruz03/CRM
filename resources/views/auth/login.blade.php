<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - CRM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            /* background: #005BAA; */ /* Azul Principal (Endeavour) */ /* #5170ff; */
            /*  */
            background-color: #005697;
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
            <i class="bi bi-speedometer2" style="font-size: 3rem; color: #00AAB5;"></i>
            <h3 class="mt-3">CRM Sistema - LBP</h3>
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
                <input type="text" class="form-control" name="usuario" placeholder="Ingresa tu usuario" value="{{ old('usuario') }}" required autofocus>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <div style="position: relative;">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Ingresa tu contraseña" required style="padding-right: 45px;">
                    <button type="button" id="togglePasswordBtn" style="
                        position: absolute;
                        right: 0;
                        top: 50%;
                        transform: translateY(-50%);
                        border: none;
                        background: transparent;
                        padding: 0 15px;
                        cursor: pointer;
                        color: #6c757d;
                        z-index: 10;
                        height: 100%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transition: color 0.2s;
                    " onmouseover="this.style.color='#333'" onmouseout="this.style.color='#6c757d'">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">
                Ingresar
            </button>
        </form>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('togglePasswordBtn');
        const input = document.getElementById('password');
        
        if (btn && input) {
            btn.addEventListener('click', function() {
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        }
    });
</script>
</body>
</html>