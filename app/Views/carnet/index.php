<!DOCTYPE html>
<html lang="es">
<head>
    <?php
    /** @var string $nombre_completo */
    /** @var string $tipo_socio */
    /** @var string $num_socio */
    /** @var string $valido_hasta */
    /** @var string|null $url_foto */
    /** @var array<string, array<string, string>> $posiciones */
    /** @var int $fondo_ancho */
    /** @var int $fondo_alto */
    /** @var int $socio_id */
    /** @var array<string, array<string, string>> $tipografia_config */
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
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#000000">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Carnet Digital">

    <title>Carnet — <?= $h($nombre_completo) ?></title>

    <!-- Manifest PWA -->
    <link rel="manifest" href="/manifest.json">

    <!-- Tipografía del carnet -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600;700&family=Montserrat:wght@400;600;700&family=Noto+Sans+Armenian:wght@400;600;700&family=Oswald:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Icono para iOS -->
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    <style>
        /* ── Reset y base ─────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #000;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-family: 'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif;
            overflow: auto;
        }

        :root {
            --font-family-default: 'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif;
            --nombre-font-family: var(--font-family-default);
            --tipo-font-family: var(--font-family-default);
            --numero-font-family: var(--font-family-default);
            --valido-font-family: var(--font-family-default);
            --nombre-font-scale: 1;
            --tipo-font-scale: 1;
            --numero-font-scale: 1;
            --valido-font-scale: 1;
            --nombre-font-color: #000000;
            --tipo-font-color: #000000;
            --numero-font-color: #000000;
            --valido-font-color: #000000;
        }

        .carnet-stage {
            width: 100%;
            overflow: auto;
            padding: .5rem;
            display: flex;
            justify-content: center;
        }

        .carnet-controls {
            width: 100%;
            max-width: <?= (int) $fondo_ancho ?>px;
            padding: .75rem;
            margin: 0 .5rem .25rem;
            border: 1px solid #334155;
            border-radius: .65rem;
            background: linear-gradient(180deg, rgba(30, 41, 59, .75), rgba(15, 23, 42, .9));
            display: grid;
            gap: .5rem;
            color: #e2e8f0;
            font-size: .85rem;
        }

        .carnet-control-group {
            display: grid;
            grid-template-columns: minmax(5.8rem, auto) minmax(18rem, 2.3fr) minmax(4rem, .5fr) 3.2rem minmax(7.8rem, 1fr);
            align-items: center;
            gap: .5rem;
        }

        .carnet-controls select,
        .carnet-controls input[type="range"] {
            height: 2rem;
            border-radius: .4rem;
            border: 1px solid #334155;
            background: #0f172a;
            color: #f8fafc;
            padding: 0 .5rem;
        }

        .carnet-controls select {
            width: 100%;
        }

        .carnet-controls input[type="color"] {
            width: 100%;
            height: 2rem;
            border-radius: .4rem;
            border: 1px solid #334155;
            background: #0f172a;
            padding: .15rem;
            cursor: pointer;
        }

        .carnet-color-control {
            display: grid;
            grid-template-columns: 3.5rem 1fr;
            gap: .35rem;
            align-items: center;
            min-width: 7.8rem;
        }

        .carnet-color-label {
            color: #cbd5e1;
            font-size: .73rem;
            font-weight: 600;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .carnet-color-value {
            color: #cbd5e1;
            font-variant-numeric: tabular-nums;
            font-size: .78rem;
            text-transform: uppercase;
        }

        .carnet-font-size-value {
            min-width: 3rem;
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .carnet-controls-title {
            font-weight: 700;
            letter-spacing: .02em;
            color: #f8fafc;
            margin-bottom: .1rem;
        }

        .carnet-element-label {
            color: #cbd5e1;
            font-weight: 600;
        }

        @media (max-width: 520px) {
            .carnet-control-group {
                grid-template-columns: 1fr;
                gap: .35rem;
                border-top: 1px solid rgba(148, 163, 184, .2);
                padding-top: .5rem;
            }

            .carnet-color-control {
                grid-template-columns: 4rem 1fr;
                max-width: 12rem;
            }
        }

        /* ── Contenedor del carnet ────────────────────────── */
        .carnet-wrapper {
            position: relative;
            width: <?= (int) $fondo_ancho ?>px;
            height: <?= (int) $fondo_alto ?>px;
            margin: 0 auto;
            flex: 0 0 auto;
        }

        /* Imagen de fondo del carnet */
        .carnet-fondo {
            width: 100%;
            height: 100%;
            display: block;
            border-radius: .75rem;
        }

        /* ── Elementos superpuestos ───────────────────────── */

        /* Foto del socio */
        .carnet-foto {
            position: absolute;
            top:    <?= (int)($posiciones['foto']['top']    ?? 120) ?>px;
            left:   <?= (int)($posiciones['foto']['left']   ?? 30)  ?>px;
            width:  <?= (int)($posiciones['foto']['width']  ?? 80)  ?>px;
            height: <?= (int)($posiciones['foto']['height'] ?? 80)  ?>px;
            box-shadow: 4px 4px 10px rgba(0,0,0,0.6);
            object-fit: cover;
            object-position: center;
            border-radius: 0;
            border: 0;
        }
        .carnet-foto-placeholder {
            position: absolute;
            top:    <?= (int)($posiciones['foto']['top']    ?? 120) ?>px;
            left:   <?= (int)($posiciones['foto']['left']   ?? 30)  ?>px;
            width:  <?= (int)($posiciones['foto']['width']  ?? 80)  ?>px;
            height: <?= (int)($posiciones['foto']['height'] ?? 80)  ?>px;
            border-radius: .2rem;
            border: 1px dashed rgba(255,255,255,.45);
            background: rgba(255,255,255,.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,.7);
            font-size: 2rem;
        }

        /* Nombre completo */
        .carnet-nombre {
            position: absolute;
            top:  <?= (int)($posiciones['nombre']['top']  ?? 220) ?>px;
            left: <?= (int)($posiciones['nombre']['left'] ?? 30)  ?>px;
            color: var(--nombre-font-color);
            font-size: calc(2rem * var(--nombre-font-scale));
            font-weight: 700;
            font-family: var(--nombre-font-family);
            text-shadow: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: <?= (int)($posiciones['nombre']['max_width'] ?? 220) ?>px;
            text-align: <?= $h($posiciones['nombre']['align'] ?? 'left') ?>;
        }

        /* Tipo de socio */
        .carnet-tipo-socio {
            position: absolute;
            top:  <?= (int)($posiciones['tipo_socio']['top']  ?? 235) ?>px;
            left: <?= (int)($posiciones['tipo_socio']['left'] ?? 30)  ?>px;
            color: var(--tipo-font-color);
            font-size: calc(1.56rem * var(--tipo-font-scale));
            font-weight: 700;
            font-family: var(--tipo-font-family);
            letter-spacing: .02em;
            text-shadow: none;
            line-height: 1;
            width: <?= (int)($posiciones['tipo_socio']['max_width'] ?? 220) ?>px;
            max-width: <?= (int)($posiciones['tipo_socio']['max_width'] ?? 220) ?>px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: <?= $h($posiciones['tipo_socio']['align'] ?? 'left') ?>;
        }

        /* Número de socio */
        .carnet-num-socio {
            position: absolute;
            top:  <?= (int)($posiciones['num_socio']['top']  ?? 250) ?>px;
            left: <?= (int)($posiciones['num_socio']['left'] ?? 30)  ?>px;
            color: var(--numero-font-color);
            font-size: calc(1.6rem * var(--numero-font-scale));
            font-weight: 600;
            font-family: var(--numero-font-family);
            letter-spacing: .05em;
            text-shadow: none;
            line-height: 1;
            transform: translateY(2px);
            width: <?= (int)($posiciones['num_socio']['max_width'] ?? 220) ?>px;
            max-width: <?= (int)($posiciones['num_socio']['max_width'] ?? 220) ?>px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: <?= $h($posiciones['num_socio']['align'] ?? 'left') ?>;
        }

        /* Válido hasta */
        .carnet-valido {
            position: absolute;
            top:  <?= (int)($posiciones['valido_hasta']['top']  ?? 280) ?>px;
            left: <?= (int)($posiciones['valido_hasta']['left'] ?? 30)  ?>px;
            color: var(--valido-font-color);
            font-size: calc(1.5rem * var(--valido-font-scale));
            font-family: var(--valido-font-family);
            text-shadow: none;
            line-height: 1;
            width: <?= (int)($posiciones['valido_hasta']['max_width'] ?? 220) ?>px;
            max-width: <?= (int)($posiciones['valido_hasta']['max_width'] ?? 220) ?>px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: <?= $h($posiciones['valido_hasta']['align'] ?? 'left') ?>;
        }

        /* ── Cabecera de página ───────────────────────────── */
        .page-header {
            width: 100%;
            max-width: <?= (int) $fondo_ancho ?>px;
            padding: 1rem .5rem .5rem;
            text-align: center;
            color: #94a3b8;
            font-size: .7rem;
            letter-spacing: .1em;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <div class="page-header">Carnet Digital</div>

    <div class="carnet-controls" aria-label="Controles de tipografia por elemento">
        <div class="carnet-controls-title">Tipografia y tamano por elemento</div>

        <div class="carnet-control-group">
            <span class="carnet-element-label">Nombre</span>
            <select data-font-target="nombre" aria-label="Tipografia del nombre">
                <option value="'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif" selected>Noto Sans Armenian (actual)</option>
                <option value="'Montserrat', 'Segoe UI', system-ui, sans-serif">Montserrat</option>
                <option value="'Lora', Georgia, serif">Lora</option>
                <option value="'Oswald', 'Segoe UI', sans-serif">Oswald</option>
            </select>
            <input data-size-target="nombre" type="range" min="0.8" max="1.4" step="0.05" value="1" aria-label="Tamano del nombre">
            <span class="carnet-font-size-value" data-size-value="nombre">100%</span>
            <div class="carnet-color-control">
                <span class="carnet-color-label">Color</span>
                <input data-color-target="nombre" type="color" value="#000000" aria-label="Color del nombre">
                <span class="carnet-color-value" data-color-value="nombre">#000000</span>
            </div>
        </div>

        <div class="carnet-control-group">
            <span class="carnet-element-label">Tipo socio</span>
            <select data-font-target="tipo" aria-label="Tipografia del tipo de socio">
                <option value="'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif" selected>Noto Sans Armenian (actual)</option>
                <option value="'Montserrat', 'Segoe UI', system-ui, sans-serif">Montserrat</option>
                <option value="'Lora', Georgia, serif">Lora</option>
                <option value="'Oswald', 'Segoe UI', sans-serif">Oswald</option>
            </select>
            <input data-size-target="tipo" type="range" min="0.8" max="1.4" step="0.05" value="1" aria-label="Tamano del tipo de socio">
            <span class="carnet-font-size-value" data-size-value="tipo">100%</span>
            <div class="carnet-color-control">
                <span class="carnet-color-label">Color</span>
                <input data-color-target="tipo" type="color" value="#000000" aria-label="Color del tipo de socio">
                <span class="carnet-color-value" data-color-value="tipo">#000000</span>
            </div>
        </div>

        <div class="carnet-control-group">
            <span class="carnet-element-label">Numero socio</span>
            <select data-font-target="numero" aria-label="Tipografia del numero de socio">
                <option value="'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif" selected>Noto Sans Armenian (actual)</option>
                <option value="'Montserrat', 'Segoe UI', system-ui, sans-serif">Montserrat</option>
                <option value="'Lora', Georgia, serif">Lora</option>
                <option value="'Oswald', 'Segoe UI', sans-serif">Oswald</option>
            </select>
            <input data-size-target="numero" type="range" min="0.8" max="1.4" step="0.05" value="1" aria-label="Tamano del numero de socio">
            <span class="carnet-font-size-value" data-size-value="numero">100%</span>
            <div class="carnet-color-control">
                <span class="carnet-color-label">Color</span>
                <input data-color-target="numero" type="color" value="#000000" aria-label="Color del numero de socio">
                <span class="carnet-color-value" data-color-value="numero">#000000</span>
            </div>
        </div>

        <div class="carnet-control-group">
            <span class="carnet-element-label">Valido hasta</span>
            <select data-font-target="valido" aria-label="Tipografia de valido hasta">
                <option value="'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif" selected>Noto Sans Armenian (actual)</option>
                <option value="'Montserrat', 'Segoe UI', system-ui, sans-serif">Montserrat</option>
                <option value="'Lora', Georgia, serif">Lora</option>
                <option value="'Oswald', 'Segoe UI', sans-serif">Oswald</option>
            </select>
            <input data-size-target="valido" type="range" min="0.8" max="1.4" step="0.05" value="1" aria-label="Tamano de valido hasta">
            <span class="carnet-font-size-value" data-size-value="valido">100%</span>
            <div class="carnet-color-control">
                <span class="carnet-color-label">Color</span>
                <input data-color-target="valido" type="color" value="#000000" aria-label="Color de valido hasta">
                <span class="carnet-color-value" data-color-value="valido">#000000</span>
            </div>
        </div>
    </div>

    <div class="carnet-stage">
        <div class="carnet-wrapper">
            <!-- Fondo del carnet -->
            <img
                src="/carnet/base.png"
                alt="Carnet"
                class="carnet-fondo"
                onerror="this.style.minHeight='500px';this.style.background='#1e293b';"
            >

            <!-- Foto del socio -->
            <?php if (! empty($url_foto)): ?>
                <img
                    src="<?= $h($url_foto) ?>"
                    alt="Foto del socio"
                    class="carnet-foto"
                >
            <?php else: ?>
                <div class="carnet-foto-placeholder">
                    <span>👤</span>
                </div>
            <?php endif; ?>

            <!-- Nombre completo -->
            <div class="carnet-nombre"><?= $h($nombre_completo) ?></div>

            <!-- Tipo de socio -->
            <div class="carnet-tipo-socio"><?= $h($tipo_socio) ?></div>

            <!-- Número de socio -->
            <div class="carnet-num-socio"><!-- Socio nº  --><?= substr($h($num_socio), -3) ?></div>

            <!-- Válido hasta -->
            <div class="carnet-valido">
                <!-- Válido hasta: -->
                <?php
                // Formatear fecha como DD/MM/YYYY
                $ts = strtotime($valido_hasta);
                echo $ts ? date('d/m/Y', $ts) : $h($valido_hasta);
                ?>
            </div>
        </div>
    </div>

    <!-- Registro del Service Worker -->
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
            }).catch(function (err) {
                console.warn('[SW] No pudo registrarse:', err);
            });
        }());

        (function () {
            const root = document.documentElement;
            const fontSelectors = Array.from(document.querySelectorAll('[data-font-target]'));
            const sizeSelectors = Array.from(document.querySelectorAll('[data-size-target]'));
            const colorSelectors = Array.from(document.querySelectorAll('[data-color-target]'));
            const sizeValueLabels = Array.from(document.querySelectorAll('[data-size-value]'));
            const colorValueLabels = Array.from(document.querySelectorAll('[data-color-value]'));
            const storageKey = 'carnetTypographySettingsV1';
            const socioId = <?= (int) $socio_id ?>;
            const apiUrl = '/carnet/preferencias/' + socioId;
            const serverConfig = <?= json_encode($tipografia_config ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
            const templateConfig = <?= json_encode($tipografiaPlantilla, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
            let saveTimer = null;

            if (!root || fontSelectors.length === 0 || sizeSelectors.length === 0) {
                return;
            }

            function syncSizeValue(scale) {
                const numericScale = Number.parseFloat(String(scale));
                return Number.isFinite(numericScale) ? Math.round(numericScale * 100) : 100;
            }

            function normalizeColor(color) {
                if (typeof color !== 'string') {
                    return '#000000';
                }

                const normalized = color.trim();
                return /^#([0-9a-f]{6})$/i.test(normalized) ? normalized.toLowerCase() : '#000000';
            }

            function setCssValue(target, font, size, color) {
                root.style.setProperty('--' + target + '-font-family', font);
                root.style.setProperty('--' + target + '-font-scale', size);
                root.style.setProperty('--' + target + '-font-color', normalizeColor(color));
            }

            function saveSettings(settings) {
                try {
                    window.localStorage.setItem(storageKey, JSON.stringify(settings));
                } catch (error) {
                    // Ignorar si el navegador bloquea almacenamiento.
                }
            }

            function saveSettingsOnServer(settings) {
                return fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ config: settings }),
                });
            }

            function loadSettings() {
                try {
                    const raw = window.localStorage.getItem(storageKey);
                    if (!raw) {
                        return {};
                    }

                    const parsed = JSON.parse(raw);
                    return parsed && typeof parsed === 'object' ? parsed : {};
                } catch (error) {
                    return {};
                }
            }

            function currentSettings() {
                const settings = {};

                fontSelectors.forEach((select) => {
                    const target = select.getAttribute('data-font-target');
                    if (!target) {
                        return;
                    }

                    const matchingRange = sizeSelectors.find((range) => range.getAttribute('data-size-target') === target);
                    const matchingColor = colorSelectors.find((picker) => picker.getAttribute('data-color-target') === target);
                    const sizeValue = matchingRange ? matchingRange.value : '1';
                    const colorValue = matchingColor ? matchingColor.value : '#000000';

                    settings[target] = {
                        font: select.value,
                        size: sizeValue,
                        color: normalizeColor(colorValue),
                    };
                });

                return settings;
            }

            function syncSizeLabels() {
                sizeValueLabels.forEach((label) => {
                    const target = label.getAttribute('data-size-value');
                    const matchingRange = sizeSelectors.find((range) => range.getAttribute('data-size-target') === target);
                    const percent = syncSizeValue(matchingRange ? matchingRange.value : '1');
                    label.textContent = percent + '%';
                });
            }

            function syncColorLabels() {
                colorValueLabels.forEach((label) => {
                    const target = label.getAttribute('data-color-value');
                    const matchingPicker = colorSelectors.find((picker) => picker.getAttribute('data-color-target') === target);
                    label.textContent = normalizeColor(matchingPicker ? matchingPicker.value : '#000000').toUpperCase();
                });
            }

            function applySettings() {
                const settings = currentSettings();

                fontSelectors.forEach((select) => {
                    const target = select.getAttribute('data-font-target');
                    if (!target) {
                        return;
                    }

                    const matchingRange = sizeSelectors.find((range) => range.getAttribute('data-size-target') === target);
                    const matchingColor = colorSelectors.find((picker) => picker.getAttribute('data-color-target') === target);
                    const selectedScale = matchingRange ? matchingRange.value : '1';
                    const selectedColor = matchingColor ? matchingColor.value : '#000000';
                    setCssValue(target, select.value, selectedScale, selectedColor);
                });

                syncSizeLabels();
                syncColorLabels();
                saveSettings(settings);

                if (saveTimer) {
                    window.clearTimeout(saveTimer);
                }

                saveTimer = window.setTimeout(function () {
                    saveSettingsOnServer(settings).catch(function () {
                        // El fallback local ya se guardo arriba.
                    });
                }, 350);
            }

            function hydrate(saved) {
                if (!saved || typeof saved !== 'object') {
                    return;
                }

                fontSelectors.forEach((select) => {
                    const target = select.getAttribute('data-font-target');
                    if (!target || !saved[target] || typeof saved[target] !== 'object') {
                        return;
                    }

                    const font = typeof saved[target].font === 'string' ? saved[target].font : '';
                    if (font !== '') {
                        select.value = font;
                    }
                });

                sizeSelectors.forEach((range) => {
                    const target = range.getAttribute('data-size-target');
                    if (!target || !saved[target] || typeof saved[target] !== 'object') {
                        return;
                    }

                    const size = typeof saved[target].size === 'string' ? saved[target].size : '';
                    if (size !== '') {
                        range.value = size;
                    }
                });

                colorSelectors.forEach((picker) => {
                    const target = picker.getAttribute('data-color-target');
                    if (!target || !saved[target] || typeof saved[target] !== 'object') {
                        return;
                    }

                    const color = typeof saved[target].color === 'string' ? saved[target].color : '';
                    if (color !== '') {
                        picker.value = normalizeColor(color);
                    }
                });
            }

            function hydrateFromStorage() {
                hydrate(loadSettings());
            }

            fontSelectors.forEach((select) => {
                select.addEventListener('change', applySettings);
            });

            sizeSelectors.forEach((range) => {
                range.addEventListener('input', applySettings);
                range.addEventListener('change', applySettings);
            });

            colorSelectors.forEach((picker) => {
                picker.addEventListener('input', applySettings);
                picker.addEventListener('change', applySettings);
            });

            if (serverConfig && typeof serverConfig === 'object' && Object.keys(serverConfig).length > 0) {
                hydrate(serverConfig);
            } else if (templateConfig && typeof templateConfig === 'object' && Object.keys(templateConfig).length > 0) {
                hydrate(templateConfig);
            } else {
                hydrateFromStorage();
            }

            applySettings();
        })();
    </script>

</body>
</html>
