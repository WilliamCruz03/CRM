<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>No se pudo completar la solicitud - CRM</title>
    @if($puedeReintentar)
        <script>
            (function() {
                const clave = 'crm_solicitud_reintentos';
                const destino = @json($rutaReintento);
                try {
                    const anterior = JSON.parse(sessionStorage.getItem(clave) || 'null');
                    const estado = anterior?.url === destino
                        ? anterior : {url: destino, intentos: 0};
                    if (estado.intentos >= 2) return;
                    estado.intentos++;
                    sessionStorage.setItem(clave, JSON.stringify(estado));
                    document.documentElement.classList.add('reintentando');
                    setTimeout(() => window.location.replace(destino), 800 * estado.intentos);
                } catch {}
            })();
        </script>
    @endif
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: #f3f6fa;
            color: #203047;
            font: 16px/1.6 system-ui, sans-serif;
        }

        main {
            width: 100%;
            max-width: 520px;
            padding: 36px;
            background: #fff;
            border: 1px solid #dce3ec;
            border-radius: 16px;
            box-shadow: 0 12px 36px #2030470d;
        }

        .marca {
            color: #005697;
            font-weight: 700;
        }

        h1 {
            font-size: 25px;
            line-height: 1.3;
            margin: 20px 0 12px;
        }

        p {
            color: #526175;
            margin: 0 0 20px;
        }

        nav {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        a {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 8px;
            background: #005697;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
        }

        a:focus-visible {
            outline: 3px solid #e7ad26;
            outline-offset: 3px;
        }

        .carga { display: none; }
        .reintentando main { display: none; }
        .reintentando .carga { display: block; }
    </style>
</head>

<body>
    <p class="carga" role="status">Cargando…</p>
    <main>
        <div class="marca">CRM · La Botica del Pueblo</div>
        <h1>No pudimos completar la solicitud</h1>
        <p>{{ $mensaje }}</p>
        @unless($puedeReintentar)
            <p>Si estabas guardando información, revisa el registro en el sistema antes de volver a enviarlo.</p>
        @endunless
        <nav aria-label="Opciones para continuar">
            @if($puedeReintentar)
                <a href="{{ $rutaReintento }}">Reintentar</a>
            @else
                <a href="{{ url('/') }}">Volver al sistema</a>
            @endif
        </nav>
    </main>
</body>

</html>
