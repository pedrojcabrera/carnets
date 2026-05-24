<!DOCTYPE html>
<html lang="es">
<head>
    <?php
    /** @var string $nombre_completo */
    /** @var string $tipo_socio */
    /** @var string $num_socio */
    /** @var string $valido_hasta */
    /** @var string|null $url_foto */
    /** @var string $url_fondo */
    /** @var array<string, array<string, string>> $posiciones */
    /** @var int $fondo_ancho */
    /** @var int $fondo_alto */
    /** @var int $socio_id */
    /** @var array<string, array<string, string>> $tipografia_config */
    /** @var string $download_rotation */
    $h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

    $tipografiaPlantilla = [
        'nombre' => [
            'font' => (string) ($posiciones['nombre']['font_family'] ?? "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif"),
            'size' => (string) ($posiciones['nombre']['font_scale'] ?? '1.00'),
            'color' => (string) ($posiciones['nombre']['color'] ?? '#000000'),
        ],
        'tipo' => [
            'font' => (string) ($posiciones['tipo_socio']['font_family'] ?? "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif"),
            'size' => (string) ($posiciones['tipo_socio']['font_scale'] ?? '1.00'),
            'color' => (string) ($posiciones['tipo_socio']['color'] ?? '#000000'),
        ],
        'numero' => [
            'font' => (string) ($posiciones['num_socio']['font_family'] ?? "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif"),
            'size' => (string) ($posiciones['num_socio']['font_scale'] ?? '1.00'),
            'color' => (string) ($posiciones['num_socio']['color'] ?? '#000000'),
        ],
        'valido' => [
            'font' => (string) ($posiciones['valido_hasta']['font_family'] ?? "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif"),
            'size' => (string) ($posiciones['valido_hasta']['font_scale'] ?? '1.00'),
            'color' => (string) ($posiciones['valido_hasta']['color'] ?? '#000000'),
        ],
    ];

    $cfg = is_array($tipografia_config) && ! empty($tipografia_config) ? $tipografia_config : $tipografiaPlantilla;

    $safeColor = static function ($value): string {
        $value = is_string($value) ? strtolower(trim($value)) : '';
        return preg_match('/^#([0-9a-f]{6})$/', $value) === 1 ? $value : '#000000';
    };

    $fontNombre = (string) ($cfg['nombre']['font'] ?? $tipografiaPlantilla['nombre']['font']);
    $fontTipo = (string) ($cfg['tipo']['font'] ?? $tipografiaPlantilla['tipo']['font']);
    $fontNumero = (string) ($cfg['numero']['font'] ?? $tipografiaPlantilla['numero']['font']);
    $fontValido = (string) ($cfg['valido']['font'] ?? $tipografiaPlantilla['valido']['font']);

    $scaleNombre = (string) ($cfg['nombre']['size'] ?? $tipografiaPlantilla['nombre']['size']);
    $scaleTipo = (string) ($cfg['tipo']['size'] ?? $tipografiaPlantilla['tipo']['size']);
    $scaleNumero = (string) ($cfg['numero']['size'] ?? $tipografiaPlantilla['numero']['size']);
    $scaleValido = (string) ($cfg['valido']['size'] ?? $tipografiaPlantilla['valido']['size']);

    $colorNombre = $safeColor($cfg['nombre']['color'] ?? $tipografiaPlantilla['nombre']['color']);
    $colorTipo = $safeColor($cfg['tipo']['color'] ?? $tipografiaPlantilla['tipo']['color']);
    $colorNumero = $safeColor($cfg['numero']['color'] ?? $tipografiaPlantilla['numero']['color']);
    $colorValido = $safeColor($cfg['valido']['color'] ?? $tipografiaPlantilla['valido']['color']);

    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#000000">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Carnet Digital">

    <title>Carnet — <?= $h($nombre_completo) ?></title>

    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600;700&family=Montserrat:wght@400;600;700&family=Noto+Sans+Armenian:wght@400;600;700&family=Oswald:wght@400;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        :root {
            --font-family-default: 'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif;
            --nombre-font-family: <?= $h($fontNombre) ?>;
            --tipo-font-family: <?= $h($fontTipo) ?>;
            --numero-font-family: <?= $h($fontNumero) ?>;
            --valido-font-family: <?= $h($fontValido) ?>;
            --nombre-font-scale: <?= $h($scaleNombre) ?>;
            --tipo-font-scale: <?= $h($scaleTipo) ?>;
            --numero-font-scale: <?= $h($scaleNumero) ?>;
            --valido-font-scale: <?= $h($scaleValido) ?>;
            --nombre-font-color: <?= $h($colorNombre) ?>;
            --tipo-font-color: <?= $h($colorTipo) ?>;
            --numero-font-color: <?= $h($colorNumero) ?>;
            --valido-font-color: <?= $h($colorValido) ?>;
        }

        body {
            margin: 0;
            min-height: 100dvh;
            background: #000;
            font-family: 'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif;
            color: #e2e8f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .9rem;
            padding: .8rem;
        }

        .carnet-stage {
            position: relative;
            width: <?= (int) $fondo_ancho ?>px;
            height: <?= (int) $fondo_alto ?>px;
            max-width: 100%;
            margin: 0 auto;
            overflow: hidden;
            border-radius: .75rem;
        }

        .carnet-wrapper {
            position: absolute;
            width: <?= (int) $fondo_ancho ?>px;
            height: <?= (int) $fondo_alto ?>px;
            left: 0;
            top: 0;
            transform-origin: top left;
            flex: 0 0 auto;
        }

        .carnet-fondo {
            width: 100%;
            height: 100%;
            display: block;
            border-radius: .75rem;
        }

        .carnet-foto {
            position: absolute;
            top: <?= (int)($posiciones['foto']['top'] ?? 120) ?>px;
            left: <?= (int)($posiciones['foto']['left'] ?? 30) ?>px;
            width: <?= (int)($posiciones['foto']['width'] ?? 80) ?>px;
            height: <?= (int)($posiciones['foto']['height'] ?? 80) ?>px;
            box-shadow: 4px 4px 10px rgba(0,0,0,0.6);
            object-fit: cover;
            object-position: center;
            border: 0;
            border-radius: 0;
        }

        .carnet-foto-placeholder {
            position: absolute;
            top: <?= (int)($posiciones['foto']['top'] ?? 120) ?>px;
            left: <?= (int)($posiciones['foto']['left'] ?? 30) ?>px;
            width: <?= (int)($posiciones['foto']['width'] ?? 80) ?>px;
            height: <?= (int)($posiciones['foto']['height'] ?? 80) ?>px;
            border-radius: .2rem;
            border: 1px dashed rgba(255,255,255,.45);
            background: rgba(255,255,255,.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,.7);
            font-size: 2rem;
        }

        .carnet-nombre {
            position: absolute;
            top: <?= (int)($posiciones['nombre']['top'] ?? 220) ?>px;
            left: <?= (int)($posiciones['nombre']['left'] ?? 30) ?>px;
            width: <?= (int)($posiciones['nombre']['max_width'] ?? 220) ?>px;
            max-width: <?= (int)($posiciones['nombre']['max_width'] ?? 220) ?>px;
            color: var(--nombre-font-color);
            font-size: calc(2rem * var(--nombre-font-scale));
            font-weight: 700;
            font-family: var(--nombre-font-family);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: <?= $h($posiciones['nombre']['align'] ?? 'left') ?>;
        }

        .carnet-tipo-socio {
            position: absolute;
            top: <?= (int)($posiciones['tipo_socio']['top'] ?? 235) ?>px;
            left: <?= (int)($posiciones['tipo_socio']['left'] ?? 30) ?>px;
            width: <?= (int)($posiciones['tipo_socio']['max_width'] ?? 220) ?>px;
            max-width: <?= (int)($posiciones['tipo_socio']['max_width'] ?? 220) ?>px;
            color: var(--tipo-font-color);
            font-size: calc(1.56rem * var(--tipo-font-scale));
            font-weight: 700;
            font-family: var(--tipo-font-family);
            letter-spacing: .02em;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: <?= $h($posiciones['tipo_socio']['align'] ?? 'left') ?>;
        }

        .carnet-num-socio {
            position: absolute;
            top: <?= (int)($posiciones['num_socio']['top'] ?? 250) ?>px;
            left: <?= (int)($posiciones['num_socio']['left'] ?? 30) ?>px;
            width: <?= (int)($posiciones['num_socio']['max_width'] ?? 220) ?>px;
            max-width: <?= (int)($posiciones['num_socio']['max_width'] ?? 220) ?>px;
            color: var(--numero-font-color);
            font-size: calc(1.6rem * var(--numero-font-scale));
            font-weight: 600;
            font-family: var(--numero-font-family);
            letter-spacing: .05em;
            line-height: 1;
            transform: translateY(2px);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: <?= $h($posiciones['num_socio']['align'] ?? 'left') ?>;
        }

        .carnet-valido {
            position: absolute;
            top: <?= (int)($posiciones['valido_hasta']['top'] ?? 280) ?>px;
            left: <?= (int)($posiciones['valido_hasta']['left'] ?? 30) ?>px;
            width: <?= (int)($posiciones['valido_hasta']['max_width'] ?? 220) ?>px;
            max-width: <?= (int)($posiciones['valido_hasta']['max_width'] ?? 220) ?>px;
            color: var(--valido-font-color);
            font-size: calc(1.5rem * var(--valido-font-scale));
            font-family: var(--valido-font-family);
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: <?= $h($posiciones['valido_hasta']['align'] ?? 'left') ?>;
        }

        .actions {
            width: min(100%, <?= (int) $fondo_ancho ?>px);
            display: flex;
            justify-content: center;
            gap: .55rem;
            flex-wrap: wrap;
        }

        .btn {
            border: 1px solid #334155;
            border-radius: .65rem;
            background: #0f172a;
            color: #e2e8f0;
            padding: .55rem .9rem;
            font-size: .88rem;
            font-weight: 700;
            cursor: pointer;
        }

        .btn--primary {
            background: linear-gradient(135deg, #2563eb, #0ea5e9);
            border-color: transparent;
            color: #fff;
        }

        .hint {
            color: #94a3b8;
            font-size: .78rem;
            text-align: center;
            width: min(100%, <?= (int) $fondo_ancho ?>px);
            line-height: 1.4;
        }

        .status {
            min-height: 1.15rem;
            color: #93c5fd;
            font-size: .8rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="carnet-stage" id="carnetStage">
        <div class="carnet-wrapper" id="carnetExportable">
            <img
                src="<?= $h($url_fondo) ?>"
                alt="Carnet"
                class="carnet-fondo"
                onerror="this.style.minHeight='500px';this.style.background='#1e293b';"
            >

            <?php if (! empty($url_foto)): ?>
                <img src="<?= $h($url_foto) ?>" alt="Foto del socio" class="carnet-foto">
            <?php else: ?>
                <div class="carnet-foto-placeholder"><span>👤</span></div>
            <?php endif; ?>

            <div class="carnet-nombre"><?= $h($nombre_completo) ?></div>
            <div class="carnet-tipo-socio"><?= $h($tipo_socio) ?></div>
            <div class="carnet-num-socio"><?= substr($h($num_socio), -3) ?></div>
            <div class="carnet-valido">
                <?php
                $ts = strtotime($valido_hasta);
                echo $ts ? date('d/m/Y', $ts) : $h($valido_hasta);
                ?>
            </div>
        </div>
    </div>

    <div class="actions">
        <button type="button" class="btn btn--primary" id="guardarImagenBtn">Guardar como imagen</button>
    </div>
    <div class="status" id="estadoGuardado"></div>
    <p class="hint">Consejo: tras guardar la imagen en la galería, podrás abrirla sin conexión desde Fotos.</p>

    <script>
        (function () {
            if (!('serviceWorker' in navigator)) return;
            var refreshing = false;
            navigator.serviceWorker.addEventListener('controllerchange', function () {
                if (!refreshing) { refreshing = true; window.location.reload(); }
            });
            navigator.serviceWorker.register('/service-worker.js').then(function (reg) {
                reg.addEventListener('updatefound', function () {
                    var newWorker = reg.installing;
                    if (!newWorker) return;
                    newWorker.addEventListener('statechange', function () {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            newWorker.postMessage({ type: 'SKIP_WAITING' });
                        }
                    });
                });
            }).catch(function () {});
        }());

        (function () {
            const stage = document.getElementById('carnetStage');
            const exportable = document.getElementById('carnetExportable');
            const saveBtn = document.getElementById('guardarImagenBtn');
            const statusEl = document.getElementById('estadoGuardado');
            const actionsEl = document.querySelector('.actions');
            const hintEl = document.querySelector('.hint');
            const baseWidth = <?= (int) $fondo_ancho ?>;
            const baseHeight = <?= (int) $fondo_alto ?>;
            const downloadRotation = <?= json_encode($download_rotation ?? 'keep') ?>;

            function rotateCanvas(sourceCanvas, rotation) {
                if (rotation !== 'right' && rotation !== 'left') {
                    return sourceCanvas;
                }

                const rotatedCanvas = document.createElement('canvas');
                rotatedCanvas.width = sourceCanvas.height;
                rotatedCanvas.height = sourceCanvas.width;

                const ctx = rotatedCanvas.getContext('2d');
                if (!ctx) {
                    return sourceCanvas;
                }

                if (rotation === 'right') {
                    ctx.translate(rotatedCanvas.width, 0);
                    ctx.rotate(Math.PI / 2);
                } else {
                    ctx.translate(0, rotatedCanvas.height);
                    ctx.rotate(-Math.PI / 2);
                }

                ctx.drawImage(sourceCanvas, 0, 0);

                return rotatedCanvas;
            }

            function ajustarEscalaVista() {
                if (!stage || !exportable) {
                    return;
                }

                const horizontalPadding = 16;
                const extraVertical = 70;
                const controlsHeight =
                    (actionsEl ? actionsEl.offsetHeight : 0)
                    + (statusEl ? statusEl.offsetHeight : 0)
                    + (hintEl ? hintEl.offsetHeight : 0)
                    + extraVertical;

                const availableWidth = Math.max(140, window.innerWidth - horizontalPadding * 2);
                const availableHeight = Math.max(140, window.innerHeight - controlsHeight);
                const scale = Math.min(1, availableWidth / baseWidth, availableHeight / baseHeight);

                stage.style.width = Math.floor(baseWidth * scale) + 'px';
                stage.style.height = Math.floor(baseHeight * scale) + 'px';
                exportable.style.transform = 'scale(' + scale + ')';
            }

            function setStatus(message, isError) {
                statusEl.textContent = message;
                statusEl.style.color = isError ? '#fca5a5' : '#93c5fd';
            }

            async function guardarCarnetComoImagen() {
                if (!exportable || typeof html2canvas === 'undefined') {
                    setStatus('No se pudo preparar la imagen.', true);
                    return;
                }

                setStatus('Generando imagen...');

                try {
                    const snapshotNode = exportable.cloneNode(true);
                    snapshotNode.style.position = 'fixed';
                    snapshotNode.style.left = '-10000px';
                    snapshotNode.style.top = '0';
                    snapshotNode.style.width = String(baseWidth) + 'px';
                    snapshotNode.style.height = String(baseHeight) + 'px';
                    snapshotNode.style.transform = 'none';
                    document.body.appendChild(snapshotNode);

                    const canvas = await html2canvas(snapshotNode, {
                        scale: 2,
                        backgroundColor: null,
                        useCORS: true,
                        logging: false,
                    });

                    snapshotNode.remove();

                    const outputCanvas = rotateCanvas(canvas, String(downloadRotation || 'keep'));
                    const dataUrl = outputCanvas.toDataURL('image/png');
                    const enlace = document.createElement('a');
                    const fileName = 'carnet-' + String(<?= (int) $socio_id ?>) + '.png';

                    enlace.href = dataUrl;
                    enlace.download = fileName;
                    document.body.appendChild(enlace);
                    enlace.click();
                    enlace.remove();

                    setStatus('Imagen lista. Si el navegador lo pide, confirma la descarga.');
                } catch (error) {
                    setStatus('No se pudo generar la imagen en este dispositivo.', true);
                }
            }

            if (saveBtn) {
                saveBtn.addEventListener('click', guardarCarnetComoImagen);
            }

            ajustarEscalaVista();
            window.addEventListener('resize', ajustarEscalaVista);
        })();
    </script>
</body>
</html>
