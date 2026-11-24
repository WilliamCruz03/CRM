<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sesión expirada - CRM</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; background: #f3f6fa; color: #203047; font: 16px/1.6 system-ui, sans-serif; }
        main { width: 100%; max-width: 520px; padding: 36px; background: #fff; border: 1px solid #dce3ec; border-radius: 16px; box-shadow: 0 12px 36px #2030470d; }
        .marca { color: #005697; font-weight: 700; }
        .codigo { margin-top: 20px; color: #526175; font-weight: 600; }
        h1 { font-size: 26px; line-height: 1.3; margin: 4px 0 16px; }
        p { color: #526175; margin: 0 0 20px; }
        nav { display: flex; flex-wrap: wrap; gap: 12px; }
        a, button { display: inline-block; padding: 10px 18px; border: 1px solid #005697; border-radius: 8px; background: #005697; color: #fff; text-decoration: none; font: inherit; font-weight: 600; cursor: pointer; }
        button { color: #005697; background: #fff; }
        :is(a, button):focus-visible { outline: 3px solid #e7ad26; outline-offset: 3px; }
    </style>
</head>
<body>
    <main>
        <div class="marca">CRM · La Botica del Pueblo</div>
        <div class="codigo">419</div>
        <h1>Sesión expirada</h1>
        <p>Tu sesión ha expirado por inactividad o no se pudo validar la seguridad del formulario.</p>
        <p>Vuelve a iniciar sesión para continuar.</p>
        <nav aria-label="Opciones para continuar">
            <button type="button" onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.assign(this.dataset.inicio); }"
                data-inicio="{{ route('login', ['expired' => 1], false) }}">Volver atrás</button>
            <a href="{{ route('login', ['expired' => 1], false) }}">Ir al inicio</a>
        </nav>
    </main>
</body>
</html>
