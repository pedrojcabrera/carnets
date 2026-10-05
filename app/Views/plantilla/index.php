<!-- Vista: plantilla/index.php  (cargada dentro de layouts/main.php) -->
<?php
/** @var object|null $plantilla */
/** @var array<string, array<string, mixed>> $caras */
/** @var string $download_rotation */

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$fontOptions = [
    "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif" => 'Noto Sans Armenian (actual)',
    "'Montserrat', 'Segoe UI', system-ui, sans-serif" => 'Montserrat',
    "'Lora', Georgia, serif" => 'Lora',
    "'Oswald', 'Segoe UI', sans-serif" => 'Oswald',
];
$faceLabels = [
    'anverso' => 'Anverso',
    'reverso' => 'Reverso',
];
$elementos = [
    'foto' => ['label' => 'Foto del socio', 'tiene_size' => true, 'es_texto' => false],
    'nombre' => ['label' => 'Nombre completo', 'tiene_size' => false, 'es_texto' => true],
    'tipo_socio' => ['label' => 'Tipo (Socio/a o Colaborador/a)', 'tiene_size' => false, 'es_texto' => true],
    'num_socio' => ['label' => 'Nº Socio', 'tiene_size' => false, 'es_texto' => true],
    'valido_hasta' => ['label' => 'Válido hasta', 'tiene_size' => false, 'es_texto' => true],
];
$extrasReverso = [
    'texto_legal' => ['label' => 'Texto legal', 'es_texto' => true, 'tipo' => 'textarea', 'rows' => 3],
    'instrucciones' => ['label' => 'Instrucciones', 'es_texto' => true, 'tipo' => 'textarea', 'rows' => 4],
    'qr' => ['label' => 'Código QR', 'es_texto' => false, 'tiene_size' => true, 'tipo' => 'qr'],
];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 mb-1 fw-bold">
            <i class="bi bi-card-image me-2 text-primary"></i>Plantilla del Carnet
        </h1>
        <p class="text-muted mb-0">Configura anverso y reverso por separado. Cada cara puede tener su propio fondo y decidir qué elementos se muestran.</p>
    </div>
</div>

<style>
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

    .preview-marker--text {
        display: block;
        padding: 4px 6px;
        font-size: 11px;
        line-height: 1.2;
        white-space: pre-line;
        overflow: hidden;
        text-align: left;
    }

    .preview-marker.has-inline-image {
        padding: 0;
        overflow: hidden;
        background: #ffffff;
        border-style: solid;
        color: transparent;
    }

    .preview-marker-qr-image {
        width: 100%;
        height: 100%;
        display: none;
        object-fit: fill;
        pointer-events: none;
    }

    .preview-marker.is-hidden {
        display: none;
    }

    .plantilla-face-pane .form-label,
    .plantilla-face-pane .form-text,
    .plantilla-face-pane .text-muted {
        color: #0f172a;
    }

    .plantilla-face-pane .text-muted {
        color: #475569 !important;
    }

    .plantilla-face-pane .form-control,
    .plantilla-face-pane .form-select {
        background-color: #ffffff;
        border: 1px solid #334155;
        color: #0f172a;
    }

    .plantilla-face-pane .form-control:focus,
    .plantilla-face-pane .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .28);
    }

    .tipografia-row,
    .visible-row {
        align-items: end;
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
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
        font-size: .8rem;
        color: #475569;
        text-transform: uppercase;
    }

    .plantilla-preview-wrap {
        position: relative;
        max-width: none;
        border-radius: .75rem;
        background: linear-gradient(180deg, #f8fafc, #e2e8f0);
        box-shadow: inset 0 0 0 1px rgba(148, 163, 184, .45);
    }

    .plantilla-preview-bg {
        width: 100%;
        height: 100%;
        display: block;
        border-radius: .75rem;
    }

    .cara-tab-button {
        min-width: 9rem;
    }
</style>

<form method="post" action="/index.php/plantilla/guardar" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap gap-3 align-items-end">
            <div>
                <label for="download_rotation" class="form-label fw-semibold mb-1">Orientación al descargar en móvil</label>
                <select id="download_rotation" name="download_rotation" class="form-select">
                    <option value="keep" <?= $download_rotation === 'keep' ? 'selected' : '' ?>>Mantener orientación</option>
                    <option value="right" <?= $download_rotation === 'right' ? 'selected' : '' ?>>Girar 90º a la derecha</option>
                    <option value="left" <?= $download_rotation === 'left' ? 'selected' : '' ?>>Girar 90º a la izquierda</option>
                </select>
            </div>
            <div class="text-muted small">La exportación compuesta incluirá anverso y reverso en una sola imagen.</div>
        </div>
    </div>

    <ul class="nav nav-tabs mb-4" id="carasTab" role="tablist">
        <?php $tabIndex = 0; ?>
        <?php foreach ($faceLabels as $faceKey => $faceLabel): ?>
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link cara-tab-button <?= $tabIndex === 0 ? 'active' : '' ?>"
                    id="tab-<?= $h($faceKey) ?>"
                    data-bs-toggle="tab"
                    data-bs-target="#pane-<?= $h($faceKey) ?>"
                    type="button"
                    role="tab"
                    aria-controls="pane-<?= $h($faceKey) ?>"
                    aria-selected="<?= $tabIndex === 0 ? 'true' : 'false' ?>"
                >
                    <?= $h($faceLabel) ?>
                </button>
            </li>
            <?php $tabIndex++; ?>
        <?php endforeach; ?>
    </ul>

    <div class="alert alert-info py-2 mb-3" role="alert">
        <strong>Cara activa:</strong> usa las pestañas <strong>Anverso</strong> y <strong>Reverso</strong> para editar cada lado del carnet.
    </div>

    <div class="tab-content">
        <?php $paneIndex = 0; ?>
        <?php foreach ($faceLabels as $faceKey => $faceLabel): ?>
            <?php $cara = $caras[$faceKey] ?? []; ?>
            <?php $canvasWidth = max(1, (int) ($cara['_canvas']['width'] ?? 360)); ?>
            <?php $canvasHeight = max(1, (int) ($cara['_canvas']['height'] ?? 540)); ?>
            <?php $urlFondo = trim((string) ($cara['url_fondo'] ?? '')); ?>
            <?php $hasVisibleMarkers = false; ?>
            <?php foreach ($elementos as $elementKey => $elemento): ?>
                <?php if ((string) ($cara[$elementKey]['visible'] ?? '0') === '1') { $hasVisibleMarkers = true; break; } ?>
            <?php endforeach; ?>
            <?php if (! $hasVisibleMarkers && $faceKey === 'reverso'): ?>
                <?php foreach ($extrasReverso as $extraKey => $extraDef): ?>
                    <?php if ((string) ($cara[$extraKey]['visible'] ?? '0') === '1') { $hasVisibleMarkers = true; break; } ?>
                <?php endforeach; ?>
            <?php endif; ?>
            <div class="tab-pane fade plantilla-face-pane <?= $paneIndex === 0 ? 'show active' : '' ?>" id="pane-<?= $h($faceKey) ?>" role="tabpanel" aria-labelledby="tab-<?= $h($faceKey) ?>">
                <div class="row g-4 plantilla-face-editor" data-face="<?= $h($faceKey) ?>">
                    <div class="col-lg-5">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white fw-semibold border-bottom">
                                <i class="bi bi-sliders me-1"></i> Configuración de <?= $h($faceLabel) ?>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Imagen de fondo</label>
                                    <?php if ($urlFondo !== ''): ?>
                                        <div class="mb-2">
                                            <img src="<?= $h($urlFondo) ?>" alt="Fondo actual de <?= $h($faceLabel) ?>" style="max-width:100%;max-height:120px;border-radius:.5rem;border:1px solid #e2e8f0;">
                                            <div class="form-text">Imagen actual (<?= $h($canvasWidth) ?> x <?= $h($canvasHeight) ?> px)</div>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" id="fondo-input-<?= $h($faceKey) ?>" name="fondo_<?= $h($faceKey) ?>" class="form-control" accept="image/*">
                                    <input type="hidden" id="fondo-width-<?= $h($faceKey) ?>" name="<?= $h($faceKey) ?>_fondo_width" value="<?= $h($canvasWidth) ?>">
                                    <input type="hidden" id="fondo-height-<?= $h($faceKey) ?>" name="<?= $h($faceKey) ?>_fondo_height" value="<?= $h($canvasHeight) ?>">
                                    <div class="form-text">Dejar vacío para mantener la imagen actual.</div>
                                </div>

                                <hr class="my-3">
                                <p class="text-muted small mb-3">Cada cara puede mostrar solo los elementos que marques aquí y colocarlos de forma independiente.</p>

                                <div class="accordion accordion-flush" id="plantillaAccordion-<?= $h($faceKey) ?>">
                                    <?php $accordionIndex = 0; ?>
                                    <?php foreach ($elementos as $elementKey => $elemento): ?>
                                        <?php $accordionIndex++; ?>
                                        <?php $elementConfig = is_array($cara[$elementKey] ?? null) ? $cara[$elementKey] : []; ?>
                                        <div class="accordion-item border rounded-2 mb-2 overflow-hidden">
                                            <h2 class="accordion-header" id="heading-<?= $h($faceKey) ?>-<?= $h($elementKey) ?>">
                                                <button class="accordion-button <?= $accordionIndex === 1 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $h($faceKey) ?>-<?= $h($elementKey) ?>" aria-expanded="<?= $accordionIndex === 1 ? 'true' : 'false' ?>" aria-controls="collapse-<?= $h($faceKey) ?>-<?= $h($elementKey) ?>">
                                                    <i class="bi bi-pin-map-fill me-2 text-primary"></i>
                                                    <?= $h($elemento['label']) ?>
                                                </button>
                                            </h2>
                                            <div id="collapse-<?= $h($faceKey) ?>-<?= $h($elementKey) ?>" class="accordion-collapse collapse <?= $accordionIndex === 1 ? 'show' : '' ?>" aria-labelledby="heading-<?= $h($faceKey) ?>-<?= $h($elementKey) ?>" data-bs-parent="#plantillaAccordion-<?= $h($faceKey) ?>">
                                                <div class="accordion-body bg-light">
                                                    <div class="row g-2">
                                                        <div class="col-12 visible-row">
                                                            <div class="form-check form-switch mt-1">
                                                                <input class="form-check-input js-visible-toggle" type="checkbox" role="switch" id="<?= $h($faceKey) ?>-<?= $h($elementKey) ?>-visible" name="<?= $h($faceKey) ?>_<?= $h($elementKey) ?>_visible" value="1" data-position-field="<?= $h($elementKey) ?>" <?= (string) ($elementConfig['visible'] ?? '0') === '1' ? 'checked' : '' ?>>
                                                                <label class="form-check-label" for="<?= $h($faceKey) ?>-<?= $h($elementKey) ?>-visible">Mostrar este elemento en <?= $h($faceLabel) ?></label>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small mb-1">Top (px)</label>
                                                            <input type="number" name="<?= $h($faceKey) ?>_<?= $h($elementKey) ?>_top" class="form-control form-control-sm" value="<?= $h($elementConfig['top'] ?? 0) ?>" min="0">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small mb-1">Left (px)</label>
                                                            <input type="number" name="<?= $h($faceKey) ?>_<?= $h($elementKey) ?>_left" class="form-control form-control-sm" value="<?= $h($elementConfig['left'] ?? 0) ?>" min="0">
                                                        </div>
                                                        <?php if (! empty($elemento['tiene_size'])): ?>
                                                            <div class="col-6">
                                                                <label class="form-label small mb-1">Ancho (px)</label>
                                                                <input type="number" name="<?= $h($faceKey) ?>_<?= $h($elementKey) ?>_width" class="form-control form-control-sm" value="<?= $h($elementConfig['width'] ?? 80) ?>" min="10">
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="form-label small mb-1">Alto (px)</label>
                                                                <input type="number" name="<?= $h($faceKey) ?>_<?= $h($elementKey) ?>_height" class="form-control form-control-sm" value="<?= $h($elementConfig['height'] ?? 80) ?>" min="10">
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if (! empty($elemento['es_texto'])): ?>
                                                            <div class="col-6">
                                                                <label class="form-label small mb-1">Ancho máximo (px)</label>
                                                                <input type="number" name="<?= $h($faceKey) ?>_<?= $h($elementKey) ?>_max_width" class="form-control form-control-sm" value="<?= $h($elementConfig['max_width'] ?? 220) ?>" min="20">
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="form-label small mb-1">Alineación</label>
                                                                <?php $alignActual = (string) ($elementConfig['align'] ?? 'left'); ?>
                                                                <?php $alignActual = in_array($alignActual, ['left', 'center', 'right', 'justify'], true) ? $alignActual : 'left'; ?>
                                                                <select name="<?= $h($faceKey) ?>_<?= $h($elementKey) ?>_align" class="form-select form-select-sm">
                                                                    <option value="left" <?= $alignActual === 'left' ? 'selected' : '' ?>>Izquierda</option>
                                                                    <option value="center" <?= $alignActual === 'center' ? 'selected' : '' ?>>Centro</option>
                                                                    <option value="right" <?= $alignActual === 'right' ? 'selected' : '' ?>>Derecha</option>
                                                                    <option value="justify" <?= $alignActual === 'justify' ? 'selected' : '' ?>>Justificado</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-8 tipografia-row">
                                                                <label class="form-label small mb-1">Tipografía</label>
                                                                <?php $fontActual = (string) ($elementConfig['font_family'] ?? array_key_first($fontOptions)); ?>
                                                                <select name="<?= $h($faceKey) ?>_<?= $h($elementKey) ?>_font_family" class="form-select form-select-sm">
                                                                    <?php foreach ($fontOptions as $fontValue => $fontLabel): ?>
                                                                        <option value="<?= $h($fontValue) ?>" <?= $fontActual === $fontValue ? 'selected' : '' ?>><?= $h($fontLabel) ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-4 tipografia-row">
                                                                <label class="form-label small mb-1">Tamaño</label>
                                                                <input type="number" name="<?= $h($faceKey) ?>_<?= $h($elementKey) ?>_font_scale" class="form-control form-control-sm" value="<?= $h($elementConfig['font_scale'] ?? '1.00') ?>" min="0.80" max="1.60" step="0.05">
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label small mb-1">Color de tipografía</label>
                                                                <?php $colorActual = (string) ($elementConfig['color'] ?? '#000000'); ?>
                                                                <div class="color-picker-wrap">
                                                                    <input type="color" name="<?= $h($faceKey) ?>_<?= $h($elementKey) ?>_color" value="<?= $h($colorActual) ?>" aria-label="Color de <?= $h($elemento['label']) ?>">
                                                                    <span class="color-code"><?= $h(strtoupper($colorActual)) ?></span>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <?php if ($faceKey === 'reverso'): ?>
                                        <?php foreach ($extrasReverso as $extraKey => $extraDef): ?>
                                            <?php $extraConfig = is_array($cara[$extraKey] ?? null) ? $cara[$extraKey] : []; ?>
                                            <div class="accordion-item border rounded-2 mb-2 overflow-hidden">
                                                <h2 class="accordion-header" id="heading-<?= $h($faceKey) ?>-<?= $h($extraKey) ?>">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $h($faceKey) ?>-<?= $h($extraKey) ?>" aria-expanded="false" aria-controls="collapse-<?= $h($faceKey) ?>-<?= $h($extraKey) ?>">
                                                        <i class="bi bi-patch-text-fill me-2 text-info"></i>
                                                        <?= $h($extraDef['label']) ?>
                                                    </button>
                                                </h2>
                                                <div id="collapse-<?= $h($faceKey) ?>-<?= $h($extraKey) ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?= $h($faceKey) ?>-<?= $h($extraKey) ?>" data-bs-parent="#plantillaAccordion-<?= $h($faceKey) ?>">
                                                    <div class="accordion-body bg-light">
                                                        <div class="row g-2">
                                                            <div class="col-12 visible-row">
                                                                <div class="form-check form-switch mt-1">
                                                                    <input class="form-check-input js-visible-toggle" type="checkbox" role="switch" id="<?= $h($faceKey) ?>-<?= $h($extraKey) ?>-visible" name="<?= $h($faceKey) ?>_<?= $h($extraKey) ?>_visible" value="1" data-position-field="<?= $h($extraKey) ?>" <?= (string) ($extraConfig['visible'] ?? '0') === '1' ? 'checked' : '' ?>>
                                                                    <label class="form-check-label" for="<?= $h($faceKey) ?>-<?= $h($extraKey) ?>-visible">Mostrar este bloque en <?= $h($faceLabel) ?></label>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="form-label small mb-1">Top (px)</label>
                                                                <input type="number" name="<?= $h($faceKey) ?>_<?= $h($extraKey) ?>_top" class="form-control form-control-sm" value="<?= $h($extraConfig['top'] ?? 0) ?>" min="0">
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="form-label small mb-1">Left (px)</label>
                                                                <input type="number" name="<?= $h($faceKey) ?>_<?= $h($extraKey) ?>_left" class="form-control form-control-sm" value="<?= $h($extraConfig['left'] ?? 0) ?>" min="0">
                                                            </div>

                                                            <?php if ($extraDef['tipo'] === 'qr'): ?>
                                                                <div class="col-6">
                                                                    <label class="form-label small mb-1">Ancho (px)</label>
                                                                    <input type="number" name="<?= $h($faceKey) ?>_<?= $h($extraKey) ?>_width" class="form-control form-control-sm" value="<?= $h($extraConfig['width'] ?? 88) ?>" min="30">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label class="form-label small mb-1">Alto (px)</label>
                                                                    <input type="number" name="<?= $h($faceKey) ?>_<?= $h($extraKey) ?>_height" class="form-control form-control-sm" value="<?= $h($extraConfig['height'] ?? 88) ?>" min="30">
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label small mb-1">Valor del QR (URL o texto)</label>
                                                                    <input type="text" name="<?= $h($faceKey) ?>_<?= $h($extraKey) ?>_value" class="form-control form-control-sm" value="<?= $h($extraConfig['value'] ?? '') ?>" placeholder="Si lo dejas vacío, usará la URL pública del carnet">
                                                                </div>
                                                                <?php $qrImageUrl = trim((string) ($extraConfig['image_url'] ?? '')); ?>
                                                                <?php if ($qrImageUrl !== ''): ?>
                                                                    <div class="col-12">
                                                                        <label class="form-label small mb-1">Imagen QR actual</label>
                                                                        <div>
                                                                            <img src="<?= $h($qrImageUrl) ?>" alt="Imagen QR actual" style="max-width:120px;max-height:120px;border-radius:.35rem;border:1px solid #cbd5e1;display:block;">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-12">
                                                                        <div class="form-check mt-1">
                                                                            <input class="form-check-input" type="checkbox" value="1" id="<?= $h($faceKey) ?>-<?= $h($extraKey) ?>-clear-image" name="<?= $h($faceKey) ?>_<?= $h($extraKey) ?>_clear_image">
                                                                            <label class="form-check-label" for="<?= $h($faceKey) ?>-<?= $h($extraKey) ?>-clear-image">Eliminar imagen y volver al QR generado</label>
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <div class="col-12">
                                                                    <label class="form-label small mb-1">Subir imagen para este bloque QR</label>
                                                                    <input type="file" id="<?= $h($faceKey) ?>-<?= $h($extraKey) ?>-image" name="<?= $h($faceKey) ?>_<?= $h($extraKey) ?>_image" class="form-control form-control-sm" accept="image/*">
                                                                    <div class="form-text">Si subes una imagen, se mostrará ajustada exactamente al ancho y alto definidos arriba.</div>
                                                                </div>
                                                            <?php else: ?>
                                                                <div class="col-6">
                                                                    <label class="form-label small mb-1">Ancho máximo (px)</label>
                                                                    <input type="number" name="<?= $h($faceKey) ?>_<?= $h($extraKey) ?>_max_width" class="form-control form-control-sm" value="<?= $h($extraConfig['max_width'] ?? 312) ?>" min="60">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label class="form-label small mb-1">Alto (px)</label>
                                                                    <input type="number" name="<?= $h($faceKey) ?>_<?= $h($extraKey) ?>_height" class="form-control form-control-sm" value="<?= $h($extraConfig['height'] ?? ($extraKey === 'texto_legal' ? 56 : 96)) ?>" min="24">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label class="form-label small mb-1">Alineación</label>
                                                                    <?php $alignExtra = (string) ($extraConfig['align'] ?? 'left'); ?>
                                                                    <?php $alignExtra = in_array($alignExtra, ['left', 'center', 'right', 'justify'], true) ? $alignExtra : 'left'; ?>
                                                                    <select name="<?= $h($faceKey) ?>_<?= $h($extraKey) ?>_align" class="form-select form-select-sm">
                                                                        <option value="left" <?= $alignExtra === 'left' ? 'selected' : '' ?>>Izquierda</option>
                                                                        <option value="center" <?= $alignExtra === 'center' ? 'selected' : '' ?>>Centro</option>
                                                                        <option value="right" <?= $alignExtra === 'right' ? 'selected' : '' ?>>Derecha</option>
                                                                        <option value="justify" <?= $alignExtra === 'justify' ? 'selected' : '' ?>>Justificado</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-8 tipografia-row">
                                                                    <label class="form-label small mb-1">Tipografía</label>
                                                                    <?php $fontExtra = (string) ($extraConfig['font_family'] ?? array_key_first($fontOptions)); ?>
                                                                    <select name="<?= $h($faceKey) ?>_<?= $h($extraKey) ?>_font_family" class="form-select form-select-sm">
                                                                        <?php foreach ($fontOptions as $fontValue => $fontLabel): ?>
                                                                            <option value="<?= $h($fontValue) ?>" <?= $fontExtra === $fontValue ? 'selected' : '' ?>><?= $h($fontLabel) ?></option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-4 tipografia-row">
                                                                    <label class="form-label small mb-1">Tamaño</label>
                                                                    <input type="number" name="<?= $h($faceKey) ?>_<?= $h($extraKey) ?>_font_scale" class="form-control form-control-sm" value="<?= $h($extraConfig['font_scale'] ?? '0.70') ?>" min="0.45" max="1.60" step="0.05">
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label small mb-1">Color de tipografía</label>
                                                                    <?php $colorExtra = (string) ($extraConfig['color'] ?? '#ffffff'); ?>
                                                                    <div class="color-picker-wrap">
                                                                        <input type="color" name="<?= $h($faceKey) ?>_<?= $h($extraKey) ?>_color" value="<?= $h($colorExtra) ?>" aria-label="Color de <?= $h($extraDef['label']) ?>">
                                                                        <span class="color-code"><?= $h(strtoupper($colorExtra)) ?></span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label small mb-1">Contenido</label>
                                                                    <textarea name="<?= $h($faceKey) ?>_<?= $h($extraKey) ?>_text" class="form-control form-control-sm" rows="<?= $h((int) ($extraDef['rows'] ?? 3)) ?>"><?= $h($extraConfig['text'] ?? '') ?></textarea>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white fw-semibold border-bottom">
                                <i class="bi bi-eye me-1"></i> Vista previa de <?= $h($faceLabel) ?>
                            </div>
                            <div class="card-body text-center" style="overflow:auto;">
                                <div class="text-muted py-5 preview-empty" <?= ($urlFondo !== '' || $hasVisibleMarkers) ? 'style="display:none;"' : '' ?>>
                                    <i class="bi bi-image fs-2 d-block mb-2"></i>
                                    Sube una imagen de fondo o activa elementos para ver esta cara.
                                </div>

                                <div class="plantilla-preview-wrap preview-wrapper" style="display:<?= ($urlFondo !== '' || $hasVisibleMarkers) ? 'inline-block' : 'none' ?>;width:<?= $h($canvasWidth) ?>px;height:<?= $h($canvasHeight) ?>px;">
                                    <img src="<?= $h($urlFondo) ?>" class="plantilla-preview-bg preview-fondo" alt="Vista previa <?= $h($faceLabel) ?>" <?= $urlFondo === '' ? 'style="display:none;"' : '' ?>>

                                    <?php foreach ($elementos as $elementKey => $elemento): ?>
                                        <?php $elementConfig = is_array($cara[$elementKey] ?? null) ? $cara[$elementKey] : []; ?>
                                        <?php $visible = (string) ($elementConfig['visible'] ?? '0') === '1'; ?>
                                        <?php $esTexto = ! empty($elemento['es_texto']); ?>
                                        <?php $markerWidth = $esTexto ? (int) ($elementConfig['max_width'] ?? 220) : (int) ($elementConfig['width'] ?? 80); ?>
                                        <?php $markerHeight = $esTexto ? 26 : (int) ($elementConfig['height'] ?? 80); ?>
                                        <?php $justify = (string) ($elementConfig['align'] ?? 'left'); ?>
                                        <?php $justify = in_array($justify, ['left', 'center', 'right', 'justify'], true) ? $justify : 'left'; ?>
                                        <?php $defaultPreview = match ($elementKey) {
                                            'nombre' => 'NOMBRE APELLIDOS',
                                            'tipo_socio' => 'SOCIO/A',
                                            'num_socio' => '123',
                                            'valido_hasta' => '31/12/2026',
                                            default => $elemento['label'],
                                        }; ?>
                                        <div class="preview-marker <?= $esTexto ? 'preview-marker--text' : '' ?> <?= $visible ? '' : 'is-hidden' ?>" data-marker-type="<?= $esTexto ? 'text' : 'box' ?>" data-preview-default="<?= $h($defaultPreview) ?>" data-position-field="<?= $h($elementKey) ?>" style="top:<?= $h((int) ($elementConfig['top'] ?? 0)) ?>px;left:<?= $h((int) ($elementConfig['left'] ?? 0)) ?>px;width:<?= $h($markerWidth) ?>px;height:<?= $h($markerHeight) ?>px;justify-content:<?= $h($justify === 'center' ? 'center' : ($justify === 'right' ? 'flex-end' : 'flex-start')) ?>;text-align:<?= $h($justify) ?>;">
                                            <span class="preview-marker-text"><?= $h($defaultPreview) ?></span>
                                        </div>
                                    <?php endforeach; ?>

                                    <?php if ($faceKey === 'reverso'): ?>
                                        <?php foreach ($extrasReverso as $extraKey => $extraDef): ?>
                                            <?php $extraConfig = is_array($cara[$extraKey] ?? null) ? $cara[$extraKey] : []; ?>
                                            <?php $visibleExtra = (string) ($extraConfig['visible'] ?? '0') === '1'; ?>
                                            <?php $esQr = $extraDef['tipo'] === 'qr'; ?>
                                            <?php $markerWidthExtra = $esQr ? (int) ($extraConfig['width'] ?? 88) : (int) ($extraConfig['max_width'] ?? 312); ?>
                                            <?php $markerHeightExtra = $esQr ? (int) ($extraConfig['height'] ?? 88) : (int) ($extraConfig['height'] ?? ($extraKey === 'texto_legal' ? 56 : 96)); ?>
                                            <?php $justifyExtra = (string) ($extraConfig['align'] ?? 'left'); ?>
                                            <?php $justifyExtra = in_array($justifyExtra, ['left', 'center', 'right', 'justify'], true) ? $justifyExtra : 'left'; ?>
                                            <?php $qrImageMarkerUrl = $esQr ? trim((string) ($extraConfig['image_url'] ?? '')) : ''; ?>
                                            <?php $previewTextExtra = trim((string) ($extraConfig['text'] ?? '')) !== ''
                                                ? (string) $extraConfig['text']
                                                : (string) $extraDef['label']; ?>
                                            <div class="preview-marker <?= (! $esQr) ? 'preview-marker--text' : '' ?> <?= $visibleExtra ? '' : 'is-hidden' ?> <?= ($esQr && $qrImageMarkerUrl !== '') ? 'has-inline-image' : '' ?>" data-marker-type="<?= $esQr ? 'box' : 'text' ?>" data-preview-default="<?= $h((string) $extraDef['label']) ?>" data-position-field="<?= $h($extraKey) ?>" <?= $esQr ? 'data-image-url="' . $h($qrImageMarkerUrl) . '"' : '' ?> style="top:<?= $h((int) ($extraConfig['top'] ?? 0)) ?>px;left:<?= $h((int) ($extraConfig['left'] ?? 0)) ?>px;width:<?= $h($markerWidthExtra) ?>px;height:<?= $h($markerHeightExtra) ?>px;justify-content:<?= $h($justifyExtra === 'center' ? 'center' : ($justifyExtra === 'right' ? 'flex-end' : 'flex-start')) ?>;text-align:<?= $h($justifyExtra) ?>;">
                                                <?php if ($esQr): ?>
                                                    <img src="<?= $h($qrImageMarkerUrl) ?>" alt="Previsualización QR" class="preview-marker-qr-image" style="display:<?= $qrImageMarkerUrl !== '' ? 'block' : 'none' ?>;">
                                                <?php else: ?>
                                                    <span class="preview-marker-text"><?= $h($previewTextExtra) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php $paneIndex++; ?>
        <?php endforeach; ?>
    </div>

    <div class="d-flex justify-content-center mt-4 mb-2">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-floppy-fill me-1"></i> Guardar plantilla
        </button>
    </div>
</form>

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

<script>
    (function () {
        const tabButtons = Array.from(document.querySelectorAll('#carasTab [data-bs-target]'));
        const panes = Array.from(document.querySelectorAll('.tab-content > .tab-pane'));

        if (tabButtons.length === 0 || panes.length === 0) {
            return;
        }

        function activate(targetSelector) {
            tabButtons.forEach((btn) => {
                const isActive = btn.getAttribute('data-bs-target') === targetSelector;
                btn.classList.toggle('active', isActive);
                btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            panes.forEach((pane) => {
                const isActive = '#' + pane.id === targetSelector;
                pane.classList.toggle('show', isActive);
                pane.classList.toggle('active', isActive);
            });
        }

        tabButtons.forEach((btn) => {
            btn.addEventListener('click', function (event) {
                // Si Bootstrap Tabs no está disponible, este fallback mantiene el cambio de cara.
                if (typeof window.bootstrap === 'undefined') {
                    event.preventDefault();
                }

                const target = String(btn.getAttribute('data-bs-target') || '');
                if (target !== '') {
                    activate(target);
                }
            });
        });
    })();
</script>

<script src="/js/plantilla-preview.js"></script>