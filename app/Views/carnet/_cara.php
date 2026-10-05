<?php
/** @var string $cara_id */
/** @var string $cara_clave */
/** @var array<string, mixed> $cara */
/** @var string $nombrePlantilla */
/** @var string $tipo_socio */
/** @var string $num_socio */
/** @var string $valido_formateado */
/** @var string|null $url_foto */
/** @var array<string, array<string, string>> $tipografia_config */
/** @var string $qr_default_value */
/** @var callable $h */

$visible = static fn(string $key): bool => (string) ($cara[$key]['visible'] ?? '0') === '1';

$textTargetMap = [
    'nombre' => 'nombre',
    'tipo_socio' => 'tipo',
    'num_socio' => 'numero',
    'valido_hasta' => 'valido',
];

$sanitizeAlign = static fn(string $align): string => in_array($align, ['left', 'center', 'right', 'justify'], true) ? $align : 'left';

$resolveTextStyle = static function (string $fieldKey) use ($cara, $tipografia_config, $textTargetMap, $sanitizeAlign): string {
    $config = is_array($cara[$fieldKey] ?? null) ? $cara[$fieldKey] : [];
    $prefKey = $textTargetMap[$fieldKey] ?? $fieldKey;
    $userConfig = is_array($tipografia_config[$prefKey] ?? null) ? $tipografia_config[$prefKey] : [];

    $fontFamily = (string) ($userConfig['font'] ?? $config['font_family'] ?? "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif");
    $fontScale = (string) ($userConfig['size'] ?? $config['font_scale'] ?? '1.00');
    $fontColor = (string) ($userConfig['color'] ?? $config['color'] ?? '#000000');
    $top = (int) ($config['top'] ?? 0);
    $left = (int) ($config['left'] ?? 0);
    $width = (int) ($config['max_width'] ?? 220);
    $align = $sanitizeAlign((string) ($config['align'] ?? 'left'));

    return sprintf(
        'top:%dpx;left:%dpx;width:%dpx;max-width:%dpx;text-align:%s;font-family:%s;font-size:calc(var(--%s-base-size) * %s);color:%s;',
        $top,
        $left,
        $width,
        $width,
        $align,
        $fontFamily,
        $prefKey,
        $fontScale,
        $fontColor
    );
};

$resolveParagraphStyle = static function (string $fieldKey, float $baseSizeRem) use ($cara, $sanitizeAlign): string {
    $config = is_array($cara[$fieldKey] ?? null) ? $cara[$fieldKey] : [];
    $fontFamily = (string) ($config['font_family'] ?? "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif");
    $fontScale = (string) ($config['font_scale'] ?? '0.70');
    $fontColor = (string) ($config['color'] ?? '#ffffff');
    $top = (int) ($config['top'] ?? 0);
    $left = (int) ($config['left'] ?? 0);
    $width = (int) ($config['max_width'] ?? 312);
    $heightDefault = $fieldKey === 'texto_legal' ? 56 : 96;
    $height = (int) ($config['height'] ?? $heightDefault);
    $align = $sanitizeAlign((string) ($config['align'] ?? 'left'));

    return sprintf(
        'top:%dpx;left:%dpx;width:%dpx;max-width:%dpx;height:%dpx;max-height:%dpx;overflow:hidden;text-align:%s;font-family:%s;font-size:calc(%.3frem * %s);color:%s;',
        $top,
        $left,
        $width,
        $width,
        $height,
        $height,
        $align,
        $fontFamily,
        $baseSizeRem,
        $fontScale,
        $fontColor
    );
};

$canvasWidth = (int) ($cara['_canvas']['width'] ?? 360);
$canvasHeight = (int) ($cara['_canvas']['height'] ?? 540);
$urlFondo = trim((string) ($cara['url_fondo'] ?? ''));
$qrValue = trim((string) ($cara['qr']['value'] ?? ''));
$qrImageUrl = trim((string) ($cara['qr']['image_url'] ?? ''));
$qrValue = $qrValue !== '' ? $qrValue : $qr_default_value;
?>

<div
    class="carnet-wrapper"
    id="<?= $h($cara_id) ?>"
    data-face="<?= $h($cara_clave) ?>"
    data-canvas-width="<?= $h($canvasWidth) ?>"
    data-canvas-height="<?= $h($canvasHeight) ?>"
    style="width:<?= $h($canvasWidth) ?>px;height:<?= $h($canvasHeight) ?>px;"
>
    <?php if ($urlFondo !== ''): ?>
        <img
            src="<?= $h($urlFondo) ?>"
            alt="Cara <?= $h($cara_clave) ?> del carnet"
            class="carnet-fondo"
            onerror="this.style.background='#1e293b';this.style.minHeight='100%';"
        >
    <?php else: ?>
        <div class="carnet-fondo carnet-fondo--empty"></div>
    <?php endif; ?>

    <?php if ($visible('foto')): ?>
        <?php $fotoTop = (int) ($cara['foto']['top'] ?? 120); ?>
        <?php $fotoLeft = (int) ($cara['foto']['left'] ?? 30); ?>
        <?php $fotoWidth = (int) ($cara['foto']['width'] ?? 80); ?>
        <?php $fotoHeight = (int) ($cara['foto']['height'] ?? 80); ?>
        <?php if (! empty($url_foto)): ?>
            <div class="carnet-foto-frame" style="top:<?= $h($fotoTop) ?>px;left:<?= $h($fotoLeft) ?>px;width:<?= $h($fotoWidth) ?>px;height:<?= $h($fotoHeight) ?>px;">
                <img src="<?= $h($url_foto) ?>" alt="Foto del socio" class="carnet-foto">
            </div>
        <?php else: ?>
            <div class="carnet-foto-placeholder" style="top:<?= $h($fotoTop) ?>px;left:<?= $h($fotoLeft) ?>px;width:<?= $h($fotoWidth) ?>px;height:<?= $h($fotoHeight) ?>px;"><span>👤</span></div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($visible('nombre')): ?>
        <div class="carnet-nombre" style="<?= $h($resolveTextStyle('nombre')) ?>"><?= $h($nombrePlantilla) ?></div>
    <?php endif; ?>

    <?php if ($visible('tipo_socio')): ?>
        <div class="carnet-tipo-socio" style="<?= $h($resolveTextStyle('tipo_socio')) ?>"><?= $h($tipo_socio) ?></div>
    <?php endif; ?>

    <?php if ($visible('num_socio')): ?>
        <div class="carnet-num-socio" style="<?= $h($resolveTextStyle('num_socio')) ?>"><?= $h(substr($num_socio, -3)) ?></div>
    <?php endif; ?>

    <?php if ($visible('valido_hasta')): ?>
        <div class="carnet-valido" style="<?= $h($resolveTextStyle('valido_hasta')) ?>"><?= $h($valido_formateado) ?></div>
    <?php endif; ?>

    <?php if ($visible('texto_legal')): ?>
        <div class="carnet-extra-text" style="<?= $h($resolveParagraphStyle('texto_legal', 0.72)) ?>"><?= nl2br($h((string) ($cara['texto_legal']['text'] ?? '')), false) ?></div>
    <?php endif; ?>

    <?php if ($visible('instrucciones')): ?>
        <div class="carnet-extra-text" style="<?= $h($resolveParagraphStyle('instrucciones', 0.82)) ?>"><?= nl2br($h((string) ($cara['instrucciones']['text'] ?? '')), false) ?></div>
    <?php endif; ?>

    <?php if ($visible('qr')): ?>
        <?php $qrTop = (int) ($cara['qr']['top'] ?? 52); ?>
        <?php $qrLeft = (int) ($cara['qr']['left'] ?? 248); ?>
        <?php $qrWidth = max(30, (int) ($cara['qr']['width'] ?? 88)); ?>
        <?php $qrHeight = max(30, (int) ($cara['qr']['height'] ?? 88)); ?>
        <div class="carnet-qr-wrap" style="top:<?= $h($qrTop) ?>px;left:<?= $h($qrLeft) ?>px;width:<?= $h($qrWidth) ?>px;height:<?= $h($qrHeight) ?>px;">
            <?php if ($qrImageUrl !== ''): ?>
                <img src="<?= $h($qrImageUrl) ?>" alt="Imagen QR" class="carnet-qr-image">
            <?php else: ?>
                <canvas
                    class="carnet-qr"
                    width="<?= $h($qrWidth) ?>"
                    height="<?= $h($qrHeight) ?>"
                    data-qr-value="<?= $h($qrValue) ?>"
                    data-qr-size="<?= $h(min($qrWidth, $qrHeight)) ?>"
                ></canvas>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>