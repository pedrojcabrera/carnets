<!-- Vista: plantilla/index.php  (cargada dentro de layouts/main.php) -->
<?php
/** @var object|null $plantilla */
/** @var array<string, array<string, string>> $posiciones */
$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$canvasWidth = (int) ($posiciones['_canvas']['width'] ?? 360);
$canvasHeight = (int) ($posiciones['_canvas']['height'] ?? 540);
$canvasWidth = $canvasWidth > 0 ? $canvasWidth : 360;
$canvasHeight = $canvasHeight > 0 ? $canvasHeight : 540;
$fontOptions = [
    "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif" => 'Noto Sans Armenian (actual)',
    "'Montserrat', 'Segoe UI', system-ui, sans-serif" => 'Montserrat',
    "'Lora', Georgia, serif" => 'Lora',
    "'Oswald', 'Segoe UI', sans-serif" => 'Oswald',
];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h4 mb-0 fw-bold">
        <i class="bi bi-card-image me-2 text-primary"></i>Plantilla del Carnet
    </h1>
</div>

<form method="post" action="/index.php/plantilla/guardar" enctype="multipart/form-data">
    <?= csrf_field() ?>

<div class="row g-4 align-items-start">

    <style>
        .plantilla-editor-shell {
            min-height: calc(100vh - 180px);
        }

        .plantilla-editor-left {
            max-height: calc(100vh - 180px);
            overflow-y: auto;
            padding-right: .5rem;
        }

        .plantilla-editor-right {
            position: sticky;
            top: 1rem;
            align-self: flex-start;
        }

        .preview-marker {
            position: absolute;
            cursor: move;
            user-select: none;
            touch-action: none;
            z-index: 10;
            border: 2px dashed rgba(59, 130, 246, .7);
            background: rgba(59, 130, 246, .1);
            border-radius: 4px;
            font-size: 9px;
            color: #1e40af;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2px;
            box-sizing: border-box;
        }

        .preview-sticky {
            position: sticky;
            top: 0;
        }

        .tipografia-row {
            align-items: end;
        }

        .plantilla-editor-left .card-body {
            background: #ffffff;
        }

        .plantilla-editor-left .form-label {
            color: #0f172a;
        }

        .plantilla-editor-left .form-text,
        .plantilla-editor-left .text-muted {
            color: #475569 !important;
        }

        .plantilla-editor-left .form-control,
        .plantilla-editor-left .form-select {
            background-color: #ffffff;
            border: 1px solid #334155;
            color: #0f172a;
        }

        .plantilla-editor-left .form-control::placeholder {
            color: #64748b;
            opacity: 1;
        }

        .plantilla-editor-left .form-control:focus,
        .plantilla-editor-left .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .28);
        }

        .plantilla-editor-left .accordion-body {
            background: #f8fafc !important;
        }

        .color-picker-wrap {
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .color-picker-wrap input[type="color"] {
            width: 3rem;
            height: 2rem;
            padding: .15rem;
            border: 1px solid #334155;
            border-radius: .35rem;
            background: #fff;
        }

        .color-code {
            min-width: 5.5rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: .8rem;
            color: #475569;
            text-transform: uppercase;
        }
    </style>

    <!-- ── Formulario de configuración ─────────────────────── -->
    <div class="col-lg-5 plantilla-editor-left">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-sliders me-1"></i> Configuración
            </div>
            <div class="card-body">
                    <!-- Imagen de fondo -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Imagen de fondo del carnet</label>
                        <?php if (! empty($plantilla->url_fondo)): ?>
                            <div class="mb-2">
                                <img src="<?= $h($plantilla->url_fondo) ?>" alt="Fondo actual"
                                     style="max-width:100%;max-height:120px;border-radius:.5rem;border:1px solid #e2e8f0;">
                                <div class="form-text">Imagen actual (<?= $h($canvasWidth) ?> x <?= $h($canvasHeight) ?> px)</div>
                            </div>
                        <?php endif; ?>
                        <input type="file" id="fondo-input" name="fondo" class="form-control" accept="image/*">
                        <input type="hidden" id="fondo-width" name="fondo_width" value="<?= $h($canvasWidth) ?>">
                        <input type="hidden" id="fondo-height" name="fondo_height" value="<?= $h($canvasHeight) ?>">
                        <div class="form-text">Dejar vacío para mantener la imagen actual. Recomendado: PNG con ratio carnet.</div>
                    </div>

                    <div class="mb-4">
                        <label for="download_rotation" class="form-label fw-semibold">Orientación al descargar en móvil</label>
                        <?php $downloadRotation = (string) ($posiciones['_download']['rotation'] ?? 'keep'); ?>
                        <select id="download_rotation" name="download_rotation" class="form-select">
                            <option value="keep" <?= $downloadRotation === 'keep' ? 'selected' : '' ?>>Mantener orientación</option>
                            <option value="right" <?= $downloadRotation === 'right' ? 'selected' : '' ?>>Girar 90º a la derecha</option>
                            <option value="left" <?= $downloadRotation === 'left' ? 'selected' : '' ?>>Girar 90º a la izquierda</option>
                        </select>
                        <div class="form-text">Afecta a la imagen descargada desde el botón "Guardar como imagen".</div>
                    </div>

                    <hr class="my-3">
                    <p class="text-muted small mb-3">
                        Indica la posición (en píxeles desde la esquina superior-izquierda del carnet)
                        de cada elemento superpuesto.
                    </p>

                    <?php
                    // Elementos configurables con sus etiquetas
                    $elementos = [
                        'foto'         => ['label' => 'Foto del socio',   'tiene_size' => true,  'es_texto' => false],
                        'nombre'       => ['label' => 'Nombre completo',  'tiene_size' => false, 'es_texto' => true],
                        'tipo_socio'   => ['label' => 'Tipo (Socio/a o Colaborador/a)', 'tiene_size' => false, 'es_texto' => true],
                        'num_socio'    => ['label' => 'Nº Socio',         'tiene_size' => false, 'es_texto' => true],
                        'valido_hasta' => ['label' => 'Válido hasta',     'tiene_size' => false, 'es_texto' => true],
                    ];
                    ?>

                    <div class="accordion accordion-flush" id="plantillaAccordion">
                        <?php $accordionIndex = 0; ?>
                        <?php foreach ($elementos as $key => $elem): ?>
                            <?php $accordionIndex++; ?>
                            <div class="accordion-item border rounded-2 mb-2 overflow-hidden">
                                <h2 class="accordion-header" id="heading-<?= $h($key) ?>">
                                    <button class="accordion-button <?= $accordionIndex === 1 ? '' : 'collapsed' ?>" type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapse-<?= $h($key) ?>"
                                            aria-expanded="<?= $accordionIndex === 1 ? 'true' : 'false' ?>"
                                            aria-controls="collapse-<?= $h($key) ?>">
                                        <i class="bi bi-pin-map-fill me-2 text-primary"></i>
                                        <?= $h($elem['label']) ?>
                                    </button>
                                </h2>
                                <div id="collapse-<?= $h($key) ?>"
                                     class="accordion-collapse collapse <?= $accordionIndex === 1 ? 'show' : '' ?>"
                                     aria-labelledby="heading-<?= $h($key) ?>"
                                     data-bs-parent="#plantillaAccordion">
                                    <div class="accordion-body bg-light">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label small mb-1">Top (px)</label>
                                                <input type="number" name="<?= $key ?>_top"
                                                       class="form-control form-control-sm"
                                                       value="<?= $h($posiciones[$key]['top'] ?? 0) ?>"
                                                       min="0">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small mb-1">Left (px)</label>
                                                <input type="number" name="<?= $key ?>_left"
                                                       class="form-control form-control-sm"
                                                       value="<?= $h($posiciones[$key]['left'] ?? 0) ?>"
                                                       min="0">
                                            </div>
                                            <?php if ($elem['tiene_size']): ?>
                                                <div class="col-6">
                                                    <label class="form-label small mb-1">Ancho (px)</label>
                                                    <input type="number" name="<?= $key ?>_width"
                                                           class="form-control form-control-sm"
                                                           value="<?= $h($posiciones[$key]['width'] ?? 80) ?>"
                                                           min="10">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small mb-1">Alto (px)</label>
                                                    <input type="number" name="<?= $key ?>_height"
                                                           class="form-control form-control-sm"
                                                           value="<?= $h($posiciones[$key]['height'] ?? 80) ?>"
                                                           min="10">
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($elem['es_texto']): ?>
                                                <div class="col-6">
                                                    <label class="form-label small mb-1">Ancho máximo (px)</label>
                                                    <input type="number" name="<?= $key ?>_max_width"
                                                           class="form-control form-control-sm"
                                                           value="<?= $h($posiciones[$key]['max_width'] ?? 220) ?>"
                                                           min="20">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small mb-1">Alineación</label>
                                                    <select name="<?= $key ?>_align" class="form-select form-select-sm">
                                                        <?php $alignActual = (string) ($posiciones[$key]['align'] ?? 'left'); ?>
                                                        <option value="left" <?= $alignActual === 'left' ? 'selected' : '' ?>>Izquierda</option>
                                                        <option value="center" <?= $alignActual === 'center' ? 'selected' : '' ?>>Centro</option>
                                                        <option value="right" <?= $alignActual === 'right' ? 'selected' : '' ?>>Derecha</option>
                                                    </select>
                                                </div>
                                                <div class="col-8 tipografia-row">
                                                    <label class="form-label small mb-1">Tipografía</label>
                                                    <?php $fontActual = (string) ($posiciones[$key]['font_family'] ?? array_key_first($fontOptions)); ?>
                                                    <select name="<?= $key ?>_font_family" class="form-select form-select-sm">
                                                        <?php foreach ($fontOptions as $fontValue => $fontLabel): ?>
                                                            <option value="<?= $h($fontValue) ?>" <?= $fontActual === $fontValue ? 'selected' : '' ?>>
                                                                <?= $h($fontLabel) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-4 tipografia-row">
                                                    <label class="form-label small mb-1">Tamaño (escala)</label>
                                                    <input type="number" name="<?= $key ?>_font_scale"
                                                           class="form-control form-control-sm"
                                                           value="<?= $h($posiciones[$key]['font_scale'] ?? '1.00') ?>"
                                                           min="0.80" max="1.40" step="0.05">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small mb-1">Color de tipografía</label>
                                                    <?php $colorActual = (string) ($posiciones[$key]['color'] ?? '#000000'); ?>
                                                    <div class="color-picker-wrap">
                                                        <input type="color"
                                                               name="<?= $key ?>_color"
                                                               value="<?= $h($colorActual) ?>"
                                                               aria-label="Color de tipografía de <?= $h($elem['label']) ?>">
                                                        <span class="color-code"><?= $h(strtoupper($colorActual)) ?></span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

            </div>
        </div>
    </div>

    <!-- ── Vista previa ────────────────────────────────────── -->
    <div class="col-lg-7 plantilla-editor-right">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-eye me-1"></i> Vista previa de posiciones
            </div>
            <div class="card-body text-center" style="overflow:auto;">
                <div id="preview-empty" class="text-muted py-5" <?= ! empty($plantilla->url_fondo) ? 'style="display:none;"' : '' ?>>
                    <i class="bi bi-image fs-2 d-block mb-2"></i>
                    Sube una imagen de fondo para ver la vista previa.
                </div>

                <div id="preview-wrapper" style="position:relative;display:<?= ! empty($plantilla->url_fondo) ? 'inline-block' : 'none' ?>;width:<?= $h($canvasWidth) ?>px;height:<?= $h($canvasHeight) ?>px;max-width:none;">
                    <img src="<?= ! empty($plantilla->url_fondo) ? $h($plantilla->url_fondo) : '' ?>"
                         id="preview-fondo"
                         style="width:100%;height:100%;border-radius:.5rem;display:block;">

                    <!-- Marcadores de posición -->
                    <?php foreach ($elementos as $key => $elem): ?>
                        <?php $pos = $posiciones[$key] ?? []; ?>
                        <?php $esTexto = isset($elem['es_texto']) && $elem['es_texto']; ?>
                        <div
                            class="preview-marker"
                            data-position-field="<?= $h($key) ?>"
                            style="top:<?= (int)($pos['top'] ?? 0) ?>px;left:<?= (int)($pos['left'] ?? 0) ?>px;<?= $esTexto ? 'width:' . (int) ($posiciones[$key]['max_width'] ?? 220) . 'px;' : (isset($pos['width']) ? 'width:' . (int) $pos['width'] . 'px;' : '') ?><?= isset($pos['height']) ? 'height:' . (int) $pos['height'] . 'px;' : '' ?><?= isset($posiciones[$key]['align']) ? 'justify-content:' . ($posiciones[$key]['align'] === 'center' ? 'center' : ($posiciones[$key]['align'] === 'right' ? 'flex-end' : 'flex-start')) . ';' : '' ?>"
                        >
                            <?= $h($key) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="d-flex justify-content-center mt-4 mb-2">
    <button type="submit" class="btn btn-primary px-4">
        <i class="bi bi-floppy-fill me-1"></i> Guardar plantilla
    </button>
</div>

</form>

<script>
    (function () {
        const leftPanel = document.querySelector('.plantilla-editor-left');
        const rightPanel = document.querySelector('.plantilla-editor-right');

        if (!leftPanel || !rightPanel) {
            return;
        }

        function syncPanelsHeight() {
            const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
            const availableHeight = Math.max(420, viewportHeight - 180);
            leftPanel.style.maxHeight = `${availableHeight}px`;
            leftPanel.style.overflowY = 'auto';
            rightPanel.style.top = '1rem';
        }

        syncPanelsHeight();
        window.addEventListener('resize', syncPanelsHeight);
    })();
</script>

<script>
    (function () {
        const colorInputs = Array.from(document.querySelectorAll('input[type="color"][name$="_color"]'));

        colorInputs.forEach((input) => {
            const wrapper = input.closest('.color-picker-wrap');
            const label = wrapper ? wrapper.querySelector('.color-code') : null;

            if (!label) {
                return;
            }

            const sync = () => {
                label.textContent = String(input.value || '#000000').toUpperCase();
            };

            input.addEventListener('input', sync);
            input.addEventListener('change', sync);
            sync();
        });
    })();
</script>

<script src="/js/plantilla-preview.js"></script>
