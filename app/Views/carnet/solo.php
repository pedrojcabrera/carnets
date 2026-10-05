<!DOCTYPE html>
<html lang="es">
<head>
    <?php
    /** @var string $nombre_completo */
    /** @var string $tipo_socio */
    /** @var string $num_socio */
    /** @var string $valido_hasta */
    /** @var string|null $url_foto */
    /** @var array<string, array<string, mixed>> $caras */
    /** @var int $fondo_ancho */
    /** @var int $fondo_alto */
    /** @var int $socio_id */
    /** @var array<string, array<string, string>> $tipografia_config */
    /** @var string $download_rotation */
    /** @var bool $carnet_disponible */
    /** @var string $aviso_campos_faltantes */
    /** @var bool $tiene_reverso */
    /** @var string $qr_default_value */

    $h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    $nombrePlantilla = function_exists('mb_strtoupper')
        ? mb_strtoupper((string) $nombre_completo, 'UTF-8')
        : strtoupper((string) $nombre_completo);
    $tsValido = strtotime($valido_hasta);
    $validoFormateado = $tsValido ? date('d/m/Y', $tsValido) : (string) $valido_hasta;
    $slides = [
        'anverso' => $caras['anverso'] ?? [],
    ];
    $shellMaxWidth = max((int) $fondo_ancho, (int) $fondo_alto);

    if (! empty($tiene_reverso)) {
        $slides['reverso'] = $caras['reverso'] ?? [];
    }
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
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.4/build/qrcode.min.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        :root {
            --nombre-base-size: 2rem;
            --tipo-base-size: 1.56rem;
            --numero-base-size: 1.6rem;
            --valido-base-size: 1.5rem;
        }

        body {
            margin: 0;
            min-height: 100dvh;
            background: radial-gradient(circle at top, #111827 0%, #020617 52%, #000 100%);
            font-family: 'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif;
            color: #e2e8f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .9rem;
            padding: .8rem;
        }

        .viewer-shell {
            width: min(100%, <?= (int) $shellMaxWidth ?>px);
            display: flex;
            flex-direction: column;
            gap: .75rem;
        }

        .viewer-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .55rem;
        }

        .viewer-counter {
            min-width: 4.5rem;
            text-align: center;
            font-size: .84rem;
            color: #dbeafe;
            letter-spacing: .02em;
        }

        .viewer-dots {
            display: flex;
            justify-content: center;
            gap: .45rem;
        }

        .viewer-dot {
            width: .6rem;
            height: .6rem;
            border-radius: 999px;
            border: 0;
            background: rgba(148, 163, 184, .55);
            padding: 0;
        }

        .viewer-dot.is-active {
            background: #38bdf8;
        }

        .carnet-stage {
            position: relative;
            width: <?= (int) $fondo_ancho ?>px;
            height: <?= (int) $fondo_alto ?>px;
            max-width: 100%;
            margin: 0 auto;
            overflow-x: auto;
            overflow-y: hidden;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            border-radius: .9rem;
            -ms-overflow-style: none;
            scrollbar-width: none;
            background: rgba(15, 23, 42, .35);
            box-shadow: 0 18px 40px rgba(2, 6, 23, .45);
            -webkit-overflow-scrolling: touch;
            overscroll-behavior-x: contain;
        }

        .carnet-stage.is-rotated {
            overflow-y: hidden;
        }

        .carnet-stage::-webkit-scrollbar {
            display: none;
        }

        .carnet-track {
            display: flex;
            height: 100%;
        }

        .carnet-slide {
            flex: 0 0 100%;
            height: 100%;
            scroll-snap-align: start;
            scroll-snap-stop: always;
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            overflow: hidden;
            opacity: .92;
            transition: opacity .22s ease;
        }

        .carnet-slide.is-active {
            opacity: 1;
        }

        .carnet-slide-scale {
            width: <?= (int) $fondo_ancho ?>px;
            height: <?= (int) $fondo_alto ?>px;
            transform-origin: top left;
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            transition: transform .2s ease-out;
        }

        .carnet-wrapper {
            position: relative;
            flex: 0 0 auto;
        }

        .carnet-fondo {
            width: 100%;
            height: 100%;
            display: block;
            border-radius: .75rem;
        }

        .carnet-fondo--empty {
            background: linear-gradient(160deg, #1e293b, #0f172a);
            box-shadow: inset 0 0 0 1px rgba(148, 163, 184, .25);
        }

        .carnet-foto-frame {
            position: absolute;
            box-shadow: 4px 4px 10px rgba(0,0,0,0.6);
            overflow: hidden;
            border-radius: 0;
        }

        .carnet-foto {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            object-position: center;
            border: 0;
            border-radius: 0;
        }

        .carnet-foto-placeholder {
            position: absolute;
            border-radius: .2rem;
            border: 1px dashed rgba(255,255,255,.45);
            background: rgba(255,255,255,.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,.7);
            font-size: 2rem;
        }

        .carnet-nombre,
        .carnet-tipo-socio,
        .carnet-num-socio,
        .carnet-valido {
            position: absolute;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1;
        }

        .carnet-nombre { font-weight: 700; }
        .carnet-tipo-socio { font-weight: 700; letter-spacing: .02em; }
        .carnet-num-socio { font-weight: 600; letter-spacing: .05em; transform: translateY(2px); }

        .carnet-extra-text {
            position: absolute;
            white-space: pre-line;
            line-height: 1.25;
            text-wrap: pretty;
            font-weight: 500;
        }

        .carnet-qr-wrap {
            position: absolute;
            background: rgba(255, 255, 255, .96);
            border-radius: .35rem;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 22px rgba(15, 23, 42, .28);
        }

        .carnet-qr {
            width: 100%;
            height: 100%;
            display: block;
            border-radius: .2rem;
            background: #fff;
        }

        .carnet-qr-image {
            width: 100%;
            height: 100%;
            display: block;
            border-radius: .2rem;
            object-fit: fill;
            background: #fff;
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
            transition: transform .15s ease, opacity .15s ease;
        }

        .btn:active {
            transform: translateY(1px);
        }

        .btn[disabled] {
            cursor: not-allowed;
            opacity: .55;
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

        @media (max-width: 480px) {
            body {
                gap: .65rem;
                padding: .55rem;
            }

            .viewer-shell {
                gap: .55rem;
            }

            .viewer-counter {
                font-size: .78rem;
            }

            .btn {
                padding: .48rem .75rem;
                font-size: .82rem;
            }

            .hint {
                font-size: .73rem;
            }
        }

        @media (max-height: 500px) and (orientation: landscape) {
            body {
                gap: .35rem;
                padding: .3rem;
            }

            .viewer-shell {
                gap: .35rem;
            }
        }
    </style>
</head>
<body>
    <div class="viewer-shell">
        <?php if (count($slides) > 1): ?>
            <div class="viewer-nav">
                <button type="button" class="btn" id="prevFaceBtn">←</button>
                <div class="viewer-counter" id="viewerCounter">1 / <?= $h(count($slides)) ?></div>
                <button type="button" class="btn" id="nextFaceBtn">→</button>
            </div>
            <div class="viewer-dots" id="viewerDots">
                <?php $dotIndex = 0; ?>
                <?php foreach ($slides as $faceKey => $faceConfig): ?>
                    <button type="button" class="viewer-dot <?= $dotIndex === 0 ? 'is-active' : '' ?>" data-face-index="<?= $h($dotIndex) ?>" aria-label="Ir a <?= $h($faceKey) ?>"></button>
                    <?php $dotIndex++; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="carnet-stage" id="carnetStage">
            <div class="carnet-track" id="carnetTrack">
                <?php foreach ($slides as $faceKey => $faceConfig): ?>
                    <section class="carnet-slide" data-face="<?= $h($faceKey) ?>">
                        <div class="carnet-slide-scale">
                            <?= view('carnet/_cara', [
                                'cara_id' => 'carnetExportable-' . $faceKey,
                                'cara_clave' => $faceKey,
                                'cara' => $faceConfig,
                                'nombrePlantilla' => $nombrePlantilla,
                                'tipo_socio' => $tipo_socio,
                                'num_socio' => $num_socio,
                                'valido_formateado' => $validoFormateado,
                                'url_foto' => $url_foto,
                                'tipografia_config' => $tipografia_config,
                                'qr_default_value' => $qr_default_value,
                                'h' => $h,
                            ]) ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php if (empty($carnet_disponible)): ?>
        <p class="hint" style="color:#fca5a5;"><?= $h($aviso_campos_faltantes ?: 'Faltan datos en su ficha de socio.') ?></p>
    <?php endif; ?>

    <div class="actions">
        <button type="button" class="btn btn--primary" id="downloadFacesBtn" <?= empty($carnet_disponible) ? 'disabled' : '' ?>>
            Descargar anverso y reverso
        </button>
    </div>
    <p class="status" id="downloadStatus"></p>

    <script>
        (function () {
            if (!('serviceWorker' in navigator)) {
                return;
            }

            navigator.serviceWorker.register('/service-worker.js').catch(function () {});
        }());

        (function () {
            const stage = document.getElementById('carnetStage');
            const track = document.getElementById('carnetTrack');
            const navEl = document.querySelector('.viewer-nav');
            const dotsEl = document.getElementById('viewerDots');
            const counterEl = document.getElementById('viewerCounter');
            const dots = Array.from(document.querySelectorAll('.viewer-dot'));
            const prevBtn = document.getElementById('prevFaceBtn');
            const nextBtn = document.getElementById('nextFaceBtn');
            const slideScales = Array.from(document.querySelectorAll('.carnet-slide-scale'));
            const slides = Array.from(document.querySelectorAll('.carnet-slide'));
            const exportables = <?= json_encode(array_map(static fn(string $faceKey): string => 'carnetExportable-' . $faceKey, array_keys($slides))) ?>;
            const faceKeys = <?= json_encode(array_keys($slides)) ?>;
            const baseWidth = <?= (int) $fondo_ancho ?>;
            const baseHeight = <?= (int) $fondo_alto ?>;
            const downloadRotation = <?= json_encode($download_rotation ?? 'keep') ?>;
            const carnetDisponible = <?= ! empty($carnet_disponible) ? 'true' : 'false' ?>;
            const socioId = <?= (int) ($socio_id ?? 0) ?>;
            const avisoCamposFaltantes = <?= json_encode((string) ($aviso_campos_faltantes ?? '')) ?>;
            const downloadFacesBtn = document.getElementById('downloadFacesBtn');
            const downloadStatus = document.getElementById('downloadStatus');
            let currentScale = 1;
            let scrollAnimationFrame = null;
            let snapTimer = null;
            let orientationLocked = false;
            let visualRotation = 'none';
            const mobileOrientationRegex = /android|iphone|ipad|ipod|iemobile|opera mini|mobile/i;

            function shouldApplyTemplateOrientation() {
                return mobileOrientationRegex.test(String(navigator.userAgent || ''));
            }

            function getEffectiveRotation() {
                if (!shouldApplyTemplateOrientation()) {
                    return 'keep';
                }

                return String(downloadRotation || 'keep');
            }

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
                if (!stage || !track) {
                    return;
                }

                const indexBeforeResize = currentIndex();

                const isMobile = shouldApplyTemplateOrientation();
                const horizontalPadding = isMobile ? 8 : 16;
                const extraVertical = isMobile ? 14 : 28;
                const controlsHeight =
                    (navEl ? navEl.offsetHeight : 0)
                    + (dotsEl ? dotsEl.offsetHeight : 0)
                    + extraVertical;

                const requiresLandscape = preferredScreenOrientation() === 'landscape' && !orientationLocked;
                const effectiveBaseWidth = requiresLandscape ? baseHeight : baseWidth;
                const effectiveBaseHeight = requiresLandscape ? baseWidth : baseHeight;
                const availableWidth = Math.max(140, window.innerWidth - horizontalPadding * 2);
                const availableHeight = Math.max(140, window.innerHeight - controlsHeight);
                const scale = Math.min(1, availableWidth / effectiveBaseWidth, availableHeight / effectiveBaseHeight);
                currentScale = scale;

                if (requiresLandscape) {
                    stage.classList.add('is-rotated');
                    stage.style.width = Math.floor(effectiveBaseWidth * scale) + 'px';
                    stage.style.height = Math.floor(effectiveBaseHeight * scale) + 'px';
                    visualRotation = getEffectiveRotation() === 'left' ? 'left' : 'right';
                } else {
                    stage.classList.remove('is-rotated');
                    stage.style.width = Math.floor(effectiveBaseWidth * scale) + 'px';
                    stage.style.height = Math.floor(effectiveBaseHeight * scale) + 'px';
                    visualRotation = 'none';
                }

                updateSwipeMotion();

                stage.scrollLeft = stage.clientWidth * indexBeforeResize;
            }

            function currentIndex() {
                if (!stage) {
                    return 0;
                }

                const width = stage.clientWidth || 1;
                return Math.max(0, Math.min(exportables.length - 1, Math.round(stage.scrollLeft / width)));
            }

            function updateViewerUi() {
                const index = currentIndex();

                if (counterEl) {
                    counterEl.textContent = String(index + 1) + ' / ' + String(exportables.length);
                }

                dots.forEach((dot, dotIndex) => {
                    dot.classList.toggle('is-active', dotIndex === index);
                });

                if (prevBtn) {
                    prevBtn.disabled = index === 0;
                }

                if (nextBtn) {
                    nextBtn.disabled = index === exportables.length - 1;
                }

                slides.forEach((slideNode, slideIndex) => {
                    slideNode.classList.toggle('is-active', slideIndex === index);
                });
            }

            function updateSwipeMotion() {
                if (!stage) {
                    return;
                }

                const width = stage.clientWidth || 1;
                const activePosition = stage.scrollLeft / width;

                slideScales.forEach((scaleNode, index) => {
                    const distance = Math.min(1, Math.abs(activePosition - index));
                    const offset = (activePosition - index) * 10;
                    const localScale = visualRotation === 'none'
                        ? Math.max(0.985, 1 - distance * 0.015)
                        : 1;
                    const finalScale = currentScale * localScale;

                    if (visualRotation === 'right') {
                        const shiftY = baseWidth * finalScale;
                        scaleNode.style.transform = 'translateY(' + shiftY + 'px) rotate(-90deg) scale(' + finalScale + ')';
                        return;
                    }

                    if (visualRotation === 'left') {
                        const shiftX = baseHeight * finalScale;
                        scaleNode.style.transform = 'translateX(' + shiftX + 'px) rotate(90deg) scale(' + finalScale + ')';
                        return;
                    }

                    scaleNode.style.transform = 'translateX(' + offset + 'px) scale(' + finalScale + ')';
                });
            }

            function scheduleSnapToNearest() {
                if (!stage) {
                    return;
                }

                window.clearTimeout(snapTimer);
                snapTimer = window.setTimeout(function () {
                    goToIndex(currentIndex());
                }, 130);
            }

            function renderQrCodes(root) {
                if (typeof QRCode === 'undefined') {
                    return;
                }

                const qrNodes = Array.from(root.querySelectorAll('canvas.carnet-qr[data-qr-value]'));
                qrNodes.forEach((node) => {
                    const value = String(node.dataset.qrValue || '').trim();
                    const size = Number.parseInt(String(node.dataset.qrSize || '88'), 10) || 88;

                    if (value === '') {
                        return;
                    }

                    QRCode.toCanvas(node, value, {
                        width: size,
                        margin: 1,
                        color: {
                            dark: '#0f172a',
                            light: '#ffffff',
                        },
                    }, function () {});
                });
            }

            function goToIndex(index) {
                if (!stage) {
                    return;
                }

                const nextIndex = Math.max(0, Math.min(exportables.length - 1, index));
                stage.scrollTo({
                    left: stage.clientWidth * nextIndex,
                    behavior: 'smooth',
                });
            }

            async function captureFace(node) {
                const width = Number.parseInt(String(node.dataset.canvasWidth || '0'), 10) || node.offsetWidth || baseWidth;
                const height = Number.parseInt(String(node.dataset.canvasHeight || '0'), 10) || node.offsetHeight || baseHeight;
                const snapshotNode = node.cloneNode(true);

                snapshotNode.style.position = 'fixed';
                snapshotNode.style.left = '-10000px';
                snapshotNode.style.top = '0';
                snapshotNode.style.width = String(width) + 'px';
                snapshotNode.style.height = String(height) + 'px';
                snapshotNode.style.transform = 'none';
                document.body.appendChild(snapshotNode);

                const originalCanvases = Array.from(node.querySelectorAll('canvas'));
                const clonedCanvases = Array.from(snapshotNode.querySelectorAll('canvas'));
                clonedCanvases.forEach((canvasNode, idx) => {
                    const sourceCanvas = originalCanvases[idx];
                    if (!sourceCanvas) {
                        return;
                    }

                    const ctx = canvasNode.getContext('2d');
                    if (!ctx) {
                        return;
                    }

                    ctx.drawImage(sourceCanvas, 0, 0);
                });

                try {
                    return await html2canvas(snapshotNode, {
                        scale: 2,
                        backgroundColor: null,
                        useCORS: true,
                        logging: false,
                    });
                } finally {
                    snapshotNode.remove();
                }
            }

            function descargarArchivo(blob, fileName) {
                const objectUrl = URL.createObjectURL(blob);
                const enlace = document.createElement('a');

                enlace.href = objectUrl;
                enlace.download = fileName;
                document.body.appendChild(enlace);
                enlace.click();
                enlace.remove();
                window.setTimeout(function () {
                    URL.revokeObjectURL(objectUrl);
                }, 1000);
            }

            function abrirBaseDescargas() {
                return new window.Promise(function (resolve, reject) {
                    if (!window.indexedDB) {
                        reject(new Error('Almacenamiento local no disponible'));
                        return;
                    }

                    const request = window.indexedDB.open('carnet-descargas', 1);
                    request.onupgradeneeded = function () {
                        const database = request.result;
                        if (!database.objectStoreNames.contains('descargas')) {
                            database.createObjectStore('descargas', { keyPath: 'key' });
                        }
                    };
                    request.onsuccess = function () { resolve(request.result); };
                    request.onerror = function () { reject(request.error); };
                });
            }

            async function leerDescargaGuardada() {
                const database = await abrirBaseDescargas();

                return new window.Promise(function (resolve, reject) {
                    const transaction = database.transaction('descargas', 'readonly');
                    const request = transaction.objectStore('descargas').get(String(socioId));
                    request.onsuccess = function () {
                        resolve(request.result && Array.isArray(request.result.files) ? request.result.files : null);
                    };
                    request.onerror = function () { reject(request.error); };
                    transaction.oncomplete = function () { database.close(); };
                    transaction.onerror = function () { database.close(); };
                });
            }

            async function guardarDescargaLocal(files) {
                try {
                    const database = await abrirBaseDescargas();

                    await new window.Promise(function (resolve, reject) {
                        const transaction = database.transaction('descargas', 'readwrite');
                        transaction.objectStore('descargas').put({ key: String(socioId), files: files });
                        transaction.oncomplete = resolve;
                        transaction.onerror = function () { reject(transaction.error); };
                        transaction.onabort = function () { reject(transaction.error); };
                    });

                    database.close();
                } catch (error) {
                    // La descarga funciona aunque el navegador no permita guardarla localmente.
                }
            }

            function getFaceFileName(face) {
                const normalized = String(face || '').trim().toLowerCase();

                if (normalized === 'anverso') {
                    return 'Carnet-Anverso.png';
                }

                if (normalized === 'reverso') {
                    return 'Carnet-Reverso.png';
                }

                return 'Carnet-' + String(face || 'Cara') + '.png';
            }

            function getDownloadSignature() {
                return JSON.stringify({
                    rotation: getEffectiveRotation(),
                    faces: exportables.map(function (id) {
                        const node = document.getElementById(id);
                        return node ? node.outerHTML : '';
                    }),
                });
            }

            function setDownloadStatus(message, isError) {
                if (!downloadStatus) {
                    return;
                }

                downloadStatus.textContent = message;
                downloadStatus.style.color = isError ? '#fca5a5' : '#93c5fd';
            }

            async function guardarCarnetComoImagen() {
                if (!carnetDisponible || typeof html2canvas === 'undefined') {
                    if (!carnetDisponible) {
                        setDownloadStatus(avisoCamposFaltantes || 'No es posible descargar este carnet.', true);
                    }
                    return;
                }

                if (shouldApplyTemplateOrientation()) {
                    try {
                        const archivosGuardados = await leerDescargaGuardada();
                        if (archivosGuardados && archivosGuardados.length > 0) {
                            const reutilizar = window.confirm(
                                'Ya hay una descarga de este carnet guardada en este navegador. ¿Quieres usarla o crear una nueva?'
                            );

                            if (reutilizar) {
                                archivosGuardados.forEach(function (archivo) {
                                    descargarArchivo(archivo.blob, archivo.fileName);
                                });
                                setDownloadStatus('Descarga guardada iniciada.', false);
                                return;
                            }
                        }
                    } catch (error) {
                        // Si no se puede consultar el almacenamiento, se genera una descarga nueva.
                    }
                }

                try {
                    if (downloadFacesBtn) {
                        downloadFacesBtn.disabled = true;
                    }

                    setDownloadStatus('Preparando descarga...', false);
                    const caras = [];

                    for (let idx = 0; idx < exportables.length; idx++) {
                        const exportableId = exportables[idx];
                        const node = document.getElementById(exportableId);
                        if (!node) {
                            continue;
                        }

                        const canvas = await captureFace(node);
                        const face = String(node.dataset.face || faceKeys[idx] || (idx === 0 ? 'anverso' : 'reverso'));
                        caras.push({ face, canvas });
                    }

                    if (caras.length === 0) {
                        throw new Error('No hay caras exportables');
                    }

                    const generatedFiles = [];
                    const archivosGenerados = [];

                    for (const cara of caras) {
                        const fileName = getFaceFileName(cara.face);
                        const outputCanvas = rotateCanvas(cara.canvas, getEffectiveRotation());
                        const blob = await new window.Promise(function (resolve) {
                            outputCanvas.toBlob(resolve, 'image/png');
                        });
                        if (!blob) {
                            throw new Error('No se pudo crear la imagen');
                        }

                        descargarArchivo(blob, fileName);
                        archivosGenerados.push({ fileName: fileName, blob: blob });
                        generatedFiles.push(fileName.replace('.png', ''));
                    }

                    await guardarDescargaLocal(archivosGenerados);
                    setDownloadStatus('Descarga iniciada: ' + generatedFiles.join(', ') + '.', false);
                } catch (error) {
                    setDownloadStatus('No se pudo generar la descarga. Inténtalo de nuevo.', true);
                } finally {
                    if (downloadFacesBtn) {
                        downloadFacesBtn.disabled = !carnetDisponible;
                    }
                }
            }

            if (stage) {
                stage.addEventListener('scroll', function () {
                    if (scrollAnimationFrame !== null) {
                        return;
                    }

                    scrollAnimationFrame = window.requestAnimationFrame(function () {
                        scrollAnimationFrame = null;
                        updateViewerUi();
                        updateSwipeMotion();
                    });

                    scheduleSnapToNearest();
                }, { passive: true });
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', function () {
                    goToIndex(currentIndex() - 1);
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function () {
                    goToIndex(currentIndex() + 1);
                });
            }

            dots.forEach((dot) => {
                dot.addEventListener('click', function () {
                    const faceIndex = Number.parseInt(String(dot.dataset.faceIndex || '0'), 10);
                    goToIndex(faceIndex);
                });
            });

            function preferredScreenOrientation() {
                const rotation = getEffectiveRotation();
                if (rotation === 'right' || rotation === 'left') {
                    return 'landscape';
                }

                return 'portrait';
            }

            async function lockOrientationIfPossible() {
                if (!shouldApplyTemplateOrientation()) {
                    orientationLocked = false;
                    return;
                }

                if (!screen.orientation || typeof screen.orientation.lock !== 'function') {
                    orientationLocked = false;
                    return;
                }

                try {
                    await screen.orientation.lock(preferredScreenOrientation());
                    orientationLocked = true;
                } catch (error) {
                    // Algunos navegadores solo permiten lock en PWA instalada o fullscreen.
                    orientationLocked = false;
                }
            }

            if (stage) {
                stage.scrollLeft = 0;
            }

            if (downloadFacesBtn) {
                downloadFacesBtn.addEventListener('click', function () {
                    guardarCarnetComoImagen();
                });
            }

            renderQrCodes(document);
            lockOrientationIfPossible();
            ajustarEscalaVista();
            updateViewerUi();
            updateSwipeMotion();
            window.addEventListener('resize', ajustarEscalaVista);
        })();
    </script>
</body>
</html>