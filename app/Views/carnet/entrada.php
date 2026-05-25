<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#000000">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <title>Carnet Digital</title>
    <?php
    $dni = isset($dni) && is_string($dni) ? $dni : '';
    $error = isset($error) && is_string($error) ? $error : '';
    $info = isset($info) && is_string($info) ? $info : '';
    $otpRequired = isset($otp_required) && (bool) $otp_required;
    ?>
    <style>
        :root {
            color-scheme: dark;
            --bg-1: #06111f;
            --bg-2: #0f172a;
            --panel: rgba(15, 23, 42, .84);
            --line: rgba(148, 163, 184, .18);
            --text: #e2e8f0;
            --muted: #94a3b8;
            --accent: #38bdf8;
            --accent-2: #22c55e;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            min-height: 100%;
            font-family: "Segoe UI", system-ui, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(56, 189, 248, .18), transparent 30%),
                radial-gradient(circle at bottom right, rgba(34, 197, 94, .12), transparent 28%),
                linear-gradient(180deg, var(--bg-1), var(--bg-2));
            color: var(--text);
        }

        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 1.25rem;
        }

        .shell {
            width: min(100%, 28rem);
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .8rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .brand-mark {
            width: 3rem;
            height: 3rem;
            border-radius: 1rem;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, rgba(56, 189, 248, .95), rgba(34, 197, 94, .95));
            color: #fff;
            box-shadow: 0 20px 45px rgba(0, 0, 0, .25);
            font-size: 1.25rem;
            font-weight: 800;
        }

        .brand h1 {
            margin: 0;
            font-size: 1.35rem;
            line-height: 1.1;
        }

        .brand p {
            margin: .2rem 0 0;
            color: var(--muted);
            font-size: .95rem;
        }

        .card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 1.25rem;
            padding: 1.25rem;
            backdrop-filter: blur(14px);
            box-shadow: 0 24px 70px rgba(0, 0, 0, .35);
        }

        .message {
            margin: 0 0 .9rem;
            padding: .75rem .85rem;
            border-radius: .8rem;
            background: rgba(248, 113, 113, .12);
            border: 1px solid rgba(248, 113, 113, .2);
            color: #fecaca;
            font-size: .95rem;
        }

        .message--info {
            background: rgba(56, 189, 248, .12);
            border-color: rgba(56, 189, 248, .22);
            color: #bae6fd;
        }

        .form-label {
            display: block;
            margin: 0 0 .55rem;
            font-size: .85rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #cbd5e1;
        }

        .dni-row {
            display: flex;
            gap: .7rem;
            align-items: stretch;
        }

        .dni-input {
            flex: 1 1 auto;
            min-width: 0;
            height: 3rem;
            border-radius: .9rem;
            border: 1px solid rgba(148, 163, 184, .28);
            background: rgba(15, 23, 42, .9);
            color: #fff;
            padding: 0 1rem;
            font-size: 1rem;
            outline: none;
        }

        .dni-input::placeholder {
            color: #64748b;
        }

        .dni-input:focus {
            border-color: rgba(56, 189, 248, .65);
            box-shadow: 0 0 0 4px rgba(56, 189, 248, .15);
        }

        .submit-btn {
            height: 3rem;
            border: 0;
            border-radius: .9rem;
            padding: 0 1.15rem;
            background: linear-gradient(135deg, var(--accent), #0ea5e9);
            color: #fff;
            font-weight: 800;
            cursor: pointer;
            white-space: nowrap;
        }

        .submit-btn:active {
            transform: translateY(1px);
        }

        .hint {
            margin: .8rem 0 0;
            color: var(--muted);
            font-size: .92rem;
            line-height: 1.45;
        }

        .footer {
            margin-top: 1rem;
            text-align: center;
            color: #64748b;
            font-size: .8rem;
        }

        @media (max-width: 420px) {
            .card {
                padding: 1rem;
            }

            .dni-row {
                flex-direction: column;
            }

            .submit-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main class="shell">
        <section class="brand" aria-label="Carnet Digital">
            <div class="brand-mark">CD</div>
            <div>
                <h1>Carnet Digital</h1>
                <p>Introduce tu DNI para abrir tu carnet</p>
            </div>
        </section>

        <section class="card">
            <?php if (! empty($error)): ?>
                <div class="message"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if (! empty($info)): ?>
                <div class="message message--info"><?= htmlspecialchars((string) $info, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="post" action="/index.php/m/abrir" autocomplete="off">
                <?= csrf_field() ?>
                <label class="form-label" for="dni">DNI</label>
                <div class="dni-row">
                    <input
                        id="dni"
                        name="dni"
                        class="dni-input"
                        type="text"
                        inputmode="text"
                        placeholder="Ejemplo: 12345678A"
                        value="<?= htmlspecialchars((string) $dni, ENT_QUOTES, 'UTF-8') ?>"
                        autofocus
                        required
                    >
                    <button class="submit-btn" type="submit"><?= $otpRequired ? 'Confirmar código' : 'Solicitar código' ?></button>
                </div>

                <?php if ($otpRequired): ?>
                    <div class="mt-3">
                        <label class="form-label" for="codigo_verificacion">Código de verificación</label>
                        <input
                            id="codigo_verificacion"
                            name="codigo_verificacion"
                            class="dni-input"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            maxlength="6"
                            placeholder="Ejemplo: 123456"
                            required
                        >
                    </div>
                <?php endif; ?>
            </form>

            <p class="hint">
                Introduce tu DNI. Te enviaremos un código de 6 cifras por email para validar el acceso.
            </p>

            <div class="dni-row" style="margin-top:.9rem;">
                <button class="submit-btn" id="instalarAppBtn" type="button" style="display:none; width:100%;">
                    Instalar aplicacion
                </button>
            </div>
            <p class="hint" id="instalarAppHint" style="display:none; margin-top:.55rem;"></p>
        </section>

        <div class="footer">Acceso público del carnet</div>
        <?php if (ENVIRONMENT !== 'production'): ?>
            <div class="footer">Entorno activo: <?= htmlspecialchars((string) ENVIRONMENT, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
    </main>
    <script>
        (function () {
            if (!('serviceWorker' in navigator)) {
                return;
            }

            navigator.serviceWorker.register('/service-worker.js').catch(function () {});
        }());

        (function () {
            const installBtn = document.getElementById('instalarAppBtn');
            const installHint = document.getElementById('instalarAppHint');
            let deferredPrompt = null;

            if (!installBtn || !installHint) {
                return;
            }

            const isStandalone = window.matchMedia('(display-mode: standalone)').matches
                || window.navigator.standalone === true;

            if (isStandalone) {
                installHint.style.display = 'block';
                installHint.textContent = 'La app ya esta instalada en este dispositivo.';
                return;
            }

            window.addEventListener('beforeinstallprompt', function (event) {
                event.preventDefault();
                deferredPrompt = event;
                installBtn.style.display = 'block';
                installHint.style.display = 'block';
                installHint.textContent = 'Pulsa para instalar Carnet Digital como aplicacion.';
            });

            installBtn.addEventListener('click', async function () {
                if (!deferredPrompt) {
                    installHint.style.display = 'block';
                    installHint.textContent = 'Si no aparece el boton de instalar, usa el menu del navegador: Agregar a pantalla de inicio.';
                    return;
                }

                deferredPrompt.prompt();
                try {
                    await deferredPrompt.userChoice;
                } catch (error) {}

                deferredPrompt = null;
                installBtn.style.display = 'none';
            });

            window.addEventListener('appinstalled', function () {
                installBtn.style.display = 'none';
                installHint.style.display = 'block';
                installHint.textContent = 'Carnet Digital se instalo correctamente.';
            });

            // iOS Safari no dispara beforeinstallprompt.
            const isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent || '');
            if (isIos) {
                installHint.style.display = 'block';
                installHint.textContent = 'En iPhone/iPad: comparte y elige Agregar a pantalla de inicio.';
            }
        }());
    </script>
</body>
</html>
