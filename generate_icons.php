<?php
/**
 * Script de utilidad: genera los iconos placeholder para la PWA.
 * Ejecutar una sola vez desde CLI: php generate_icons.php
 */
foreach ([192, 512] as $size) {
    $im = imagecreatetruecolor($size, $size);
    $bg = imagecolorallocate($im, 30, 41, 59);
    $fg = imagecolorallocate($im, 59, 130, 246);
    imagefill($im, 0, 0, $bg);
    $cx = (int)($size / 2);
    $cy = (int)($size / 2);
    $r  = (int)($size * 0.4);
    imagefilledellipse($im, $cx, $cy, $r * 2, $r * 2, $fg);
    imagepng($im, __DIR__ . '/public/icons/icon-' . $size . '.png');
    imagedestroy($im);
    echo "Generado: public/icons/icon-{$size}.png\n";
}
echo "Listo.\n";
