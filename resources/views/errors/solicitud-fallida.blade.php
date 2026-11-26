<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 · Error interno del servidor - CRM</title>
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
            background: #f6f6f4;
            color: #18232f;
            font: 16px/1.6 system-ui, sans-serif;
        }

        main {
            width: 100%;
            max-width: 520px;
            padding: 48px 36px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 20px 48px #18232f26;
            text-align: center;
        }

        .marca {
            color: #687583;
            font-size: 12px;
            margin-top: 28px;
        }

        .icono-servidor { display: grid; place-items: center; width: 100px; height: 100px; margin: 0 auto 28px; border-radius: 50%; background: #fbe9ed; color: #df3348; }
        .icono-servidor svg { width: 44px; height: 44px; }
        .codigo { color: #df3348; font-size: 80px; font-weight: 750; line-height: 1.1; letter-spacing: -3px; }

        h1 {
            font-size: 28px;
            line-height: 1.3;
            margin: 14px 0 18px;
        }

        p {
            color: #526175;
            margin: 0 0 20px;
        }

        nav {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
            margin-top: 26px;
        }

        a, button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 18px;
            border: 1px solid #005697;
            border-radius: 24px;
            background: #005697;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-family: inherit;
            font-size: 14px;
            cursor: pointer;
        }
        .secundario { background: #fff; border-color: #dce3ec; color: #425568; }
        nav svg { width: 18px; height: 18px; flex-shrink: 0; }

        :is(a, button):focus-visible {
            outline: 3px solid #e7ad26;
            outline-offset: 3px;
        }

        .carga { display: none; text-align: center; }
        .carga p { margin-bottom: 16px; }
        .spinner-border {
            display: inline-block;
            width: 2rem;
            height: 2rem;
            border: .25em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: girar .75s linear infinite;
        }
        .text-primary { color: #005697; }
        @keyframes girar { to { transform: rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) {
            .spinner-border { animation-duration: 1.5s; }
        }
        @media (max-width: 480px) {
            body { padding: 16px; }
            main { padding: 32px 22px; }
            h1 { font-size: 24px; }
        }
        .reintentando main { display: none; }
        .reintentando .carga { display: block; }
    </style>
</head>

<body>
    <div class="carga" role="status">
        <p>Cargando…</p>
        <div class="spinner-border text-primary" aria-hidden="true"></div>
    </div>
    <main>
        <div class="icono-servidor" aria-hidden="true">
            <svg viewBox="0 0 48 48" fill="currentColor">
                <rect x="3" y="3" width="42" height="18" rx="6"/>
                <rect x="3" y="27" width="42" height="18" rx="6"/>
                <g fill="#fff"><circle cx="30" cy="12" r="2"/><circle cx="37" cy="12" r="2"/><circle cx="30" cy="36" r="2"/><circle cx="37" cy="36" r="2"/></g>
            </svg>
        </div>
        <div class="codigo">500</div>
        <h1>Error interno del servidor</h1>
        <p>{{ $mensaje }}</p>
        @if($reintentoLogin ?? false)
            <p>Vuelve a iniciar sesión. Tus credenciales no se reenviarán automáticamente.</p>
        @endif
        @unless($puedeReintentar)
            <p>Si estabas guardando información, revisa el registro en el sistema antes de volver a enviarlo.</p>
        @endunless
        <nav aria-label="Opciones para continuar">
            <button type="button" class="secundario" data-inicio="{{ url('/') }}"
                onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.assign(this.dataset.inicio); }">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5m6-6-6 6 6 6"/></svg>
                Volver
            </button>
            @if($puedeReintentar)
                <a href="{{ $rutaReintento }}" class="secundario">Reintentar</a>
            @endif
            <a href="{{ url('/') }}">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 3 10 9h-3v9h-5v-6h-4v6H5v-9H2z"/></svg>
                Ir al inicio
            </a>
        </nav>
        <div class="marca">CRM · La Botica del Pueblo</div>
    </main>
</body>

</html>
