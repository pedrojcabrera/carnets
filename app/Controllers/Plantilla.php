<?php

namespace App\Controllers;

use App\Models\PlantillaModel;

/**
 * Controlador para el mantenimiento de la plantilla del carnet.
 * Permite seleccionar la imagen de fondo y configurar
 * las posiciones absolutas de cada elemento superpuesto.
 */
class Plantilla extends BaseController
{
    protected PlantillaModel $model;

    private const FACE_KEYS = ['anverso', 'reverso'];

    private const ELEMENT_KEYS = ['foto', 'nombre', 'tipo_socio', 'num_socio', 'valido_hasta'];

    private const REVERSE_EXTRA_KEYS = ['texto_legal', 'instrucciones', 'qr'];

    public function __construct()
    {
        $this->model = new PlantillaModel();
    }

    /**
     * Muestra el formulario de configuración de la plantilla.
     */
    public function index(): string
    {
        $plantilla = $this->model->getActiva();

        $config = $this->normalizeTemplateConfig($plantilla);

        return view('layouts/main', [
            'titulo'     => 'Plantilla del Carnet',
            'view_content' => 'plantilla/index',
            'plantilla'  => $plantilla,
            'caras'      => $config['caras'],
            'download_rotation' => $config['download_rotation'],
        ]);
    }

    /**
     * Guarda o actualiza la plantilla activa.
     */
    public function guardar()
    {
        $rules = [
            'fondo_anverso' => 'if_exist|uploaded[fondo_anverso]|max_size[fondo_anverso,2048]|is_image[fondo_anverso]',
            'fondo_reverso' => 'if_exist|uploaded[fondo_reverso]|max_size[fondo_reverso,2048]|is_image[fondo_reverso]',
            'reverso_qr_image' => 'if_exist|uploaded[reverso_qr_image]|max_size[reverso_qr_image,2048]|is_image[reverso_qr_image]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->with('errors', $this->validator->getErrors())
                ->withInput();
        }

        $plantillaActual = $this->model->getActiva();

        $configActual = $this->normalizeTemplateConfig($plantillaActual);
        $caras = [];

        foreach (self::FACE_KEYS as $faceKey) {
            $caras[$faceKey] = $this->buildFaceConfigFromRequest($faceKey, $configActual['caras'][$faceKey] ?? $this->getDefaultFaceConfig($faceKey));
            $caras[$faceKey]['url_fondo'] = $this->resolveUploadedBackground($faceKey, $caras[$faceKey]);
            $caras[$faceKey] = $this->resolveUploadedQrImage($faceKey, $caras[$faceKey]);
        }

        $configPosiciones = [
            '_version' => 'dual-face-v2',
            '_download' => [
                'rotation' => $this->sanitizeDownloadRotation((string) $this->request->getPost('download_rotation')),
            ],
            'anverso' => $caras['anverso'],
            'reverso' => $caras['reverso'],
        ];

        $datos = [
            'url_fondo'         => $caras['anverso']['url_fondo'] ?: null,
            'config_posiciones' => json_encode($configPosiciones),
            'activa'            => 1,
        ];

        if ($plantillaActual) {
            // Desactivar otras plantillas activas antes de actualizar la actual
            $this->model
                ->where('id !=', (int) $plantillaActual->id)
                ->set('activa', 0)
                ->update();
            $this->model->update($plantillaActual->id, $datos);
        } else {
            $this->model->insert($datos);
        }

        return redirect()->to('/index.php/plantilla')->with('success', 'Plantilla guardada correctamente.');
    }

    private function sanitizeHexColor(string $value): string
    {
        $value = strtolower(trim($value));

        if (preg_match('/^#([0-9a-f]{6})$/', $value) === 1) {
            return $value;
        }

        return '#000000';
    }

    private function sanitizeDownloadRotation(string $value): string
    {
        $value = strtolower(trim($value));

        if (in_array($value, ['keep', 'right', 'left'], true)) {
            return $value;
        }

        return 'keep';
    }

    private function sanitizeTextAlign(string $value): string
    {
        $value = strtolower(trim($value));

        if (in_array($value, ['left', 'center', 'right', 'justify'], true)) {
            return $value;
        }

        return 'left';
    }

    private function normalizeTemplateConfig(?object $plantilla): array
    {
        $caras = [
            'anverso' => $this->getDefaultFaceConfig('anverso'),
            'reverso' => $this->getDefaultFaceConfig('reverso'),
        ];
        $downloadRotation = 'keep';

        if (! $plantilla) {
            return [
                'caras' => $caras,
                'download_rotation' => $downloadRotation,
            ];
        }

        $guardadas = [];
        if (! empty($plantilla->config_posiciones)) {
            $decoded = json_decode((string) $plantilla->config_posiciones, true);
            $guardadas = is_array($decoded) ? $decoded : [];
        }

        $isDualFace = isset($guardadas['anverso']) && is_array($guardadas['anverso']);

        if ($isDualFace) {
            foreach (self::FACE_KEYS as $faceKey) {
                $caras[$faceKey] = $this->mergeFaceConfig(
                    $caras[$faceKey],
                    is_array($guardadas[$faceKey] ?? null) ? $guardadas[$faceKey] : []
                );
            }
        } else {
            $caras['anverso'] = $this->mergeFaceConfig($caras['anverso'], $guardadas);
        }

        if (! empty($plantilla->url_fondo)) {
            $caras['anverso']['url_fondo'] = (string) $plantilla->url_fondo;
        }

        foreach (self::FACE_KEYS as $faceKey) {
            $caras[$faceKey]['url_fondo'] = $this->resolveFaceBackgroundUrl($caras[$faceKey], $faceKey === 'anverso' ? (string) ($plantilla->url_fondo ?? '') : '');
            $caras[$faceKey]['_canvas'] = $this->resolveFaceCanvas($caras[$faceKey]);
        }

        $downloadRotation = $this->sanitizeDownloadRotation((string) ($guardadas['_download']['rotation'] ?? 'keep'));

        return [
            'caras' => $caras,
            'download_rotation' => $downloadRotation,
        ];
    }

    private function getDefaultFaceConfig(string $faceKey): array
    {
        $visible = $faceKey === 'anverso';
        $isReverso = $faceKey === 'reverso';

        return [
            'url_fondo' => '',
            '_canvas' => ['width' => '360', 'height' => '540'],
            'foto' => ['visible' => $visible ? '1' : '0', 'top' => '120', 'left' => '30', 'width' => '80', 'height' => '80'],
            'nombre' => ['visible' => $visible ? '1' : '0', 'top' => '220', 'left' => '30', 'max_width' => '220', 'align' => 'left', 'font_family' => "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif", 'font_scale' => '1.00', 'color' => '#000000'],
            'tipo_socio' => ['visible' => $visible ? '1' : '0', 'top' => '235', 'left' => '30', 'max_width' => '220', 'align' => 'left', 'font_family' => "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif", 'font_scale' => '1.00', 'color' => '#000000'],
            'num_socio' => ['visible' => $visible ? '1' : '0', 'top' => '250', 'left' => '30', 'max_width' => '220', 'align' => 'left', 'font_family' => "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif", 'font_scale' => '1.00', 'color' => '#000000'],
            'valido_hasta' => ['visible' => $visible ? '1' : '0', 'top' => '280', 'left' => '30', 'max_width' => '220', 'align' => 'left', 'font_family' => "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif", 'font_scale' => '1.00', 'color' => '#000000'],
            'texto_legal' => [
                'visible' => $isReverso ? '1' : '0',
                'top' => '340',
                'left' => '24',
                'max_width' => '312',
                'height' => '56',
                'align' => 'left',
                'font_family' => "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif",
                'font_scale' => '0.62',
                'color' => '#ffffff',
                'text' => $isReverso ? 'Documento personal e intransferible. Su uso indebido puede conllevar la baja de la entidad.' : '',
            ],
            'instrucciones' => [
                'visible' => $isReverso ? '1' : '0',
                'top' => '402',
                'left' => '24',
                'max_width' => '312',
                'height' => '96',
                'align' => 'left',
                'font_family' => "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif",
                'font_scale' => '0.70',
                'color' => '#ffffff',
                'text' => $isReverso ? "1. Presentar este carnet al acceder.\n2. Comunicar cualquier incidencia a secretaría." : '',
            ],
            'qr' => [
                'visible' => $isReverso ? '1' : '0',
                'top' => '52',
                'left' => '248',
                'width' => '88',
                'height' => '88',
                'value' => '',
                'image_url' => '',
            ],
        ];
    }

    private function mergeFaceConfig(array $base, array $incoming): array
    {
        $merged = array_replace_recursive($base, $incoming);

        $keys = array_merge(self::ELEMENT_KEYS, self::REVERSE_EXTRA_KEYS);
        foreach ($keys as $elementKey) {
            if (! isset($merged[$elementKey]['visible'])) {
                $merged[$elementKey]['visible'] = $base[$elementKey]['visible'] ?? '0';
            }
        }

        return $merged;
    }

    private function resolveFaceBackgroundUrl(array $faceConfig, string $fallback = ''): string
    {
        $url = trim((string) ($faceConfig['url_fondo'] ?? ''));
        if ($url !== '') {
            return $url;
        }

        return trim($fallback);
    }

    private function resolveFaceCanvas(array $faceConfig): array
    {
        $width = (int) ($faceConfig['_canvas']['width'] ?? 0);
        $height = (int) ($faceConfig['_canvas']['height'] ?? 0);

        if ($width > 0 && $height > 0) {
            return [
                'width' => (string) $width,
                'height' => (string) $height,
            ];
        }

        $urlFondo = (string) ($faceConfig['url_fondo'] ?? '');
        $path = parse_url($urlFondo, PHP_URL_PATH);
        $path = is_string($path) ? ltrim($path, '/\\') : '';
        $localPath = $path !== '' ? FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path) : '';

        if ($localPath !== '' && is_file($localPath)) {
            $imgInfo = @getimagesize($localPath);
            if (is_array($imgInfo)) {
                $width = (int) ($imgInfo[0] ?? 0);
                $height = (int) ($imgInfo[1] ?? 0);
            }
        }

        return [
            'width' => (string) ($width > 0 ? $width : 360),
            'height' => (string) ($height > 0 ? $height : 540),
        ];
    }

    private function buildFaceConfigFromRequest(string $faceKey, array $currentFace): array
    {
        $face = $this->mergeFaceConfig($this->getDefaultFaceConfig($faceKey), $currentFace);
        $prefix = $faceKey . '_';

        $fieldKeys = self::ELEMENT_KEYS;
        if ($faceKey === 'reverso') {
            $fieldKeys = array_merge($fieldKeys, self::REVERSE_EXTRA_KEYS);
        }

        foreach ($fieldKeys as $elementKey) {
            $face[$elementKey]['visible'] = $this->request->getPost($prefix . $elementKey . '_visible') !== null ? '1' : '0';
        }

        $face['foto']['top'] = (string) $this->request->getPost($prefix . 'foto_top');
        $face['foto']['left'] = (string) $this->request->getPost($prefix . 'foto_left');
        $face['foto']['width'] = (string) $this->request->getPost($prefix . 'foto_width');
        $face['foto']['height'] = (string) $this->request->getPost($prefix . 'foto_height');

        foreach (['nombre', 'tipo_socio', 'num_socio', 'valido_hasta'] as $elementKey) {
            $face[$elementKey]['top'] = (string) $this->request->getPost($prefix . $elementKey . '_top');
            $face[$elementKey]['left'] = (string) $this->request->getPost($prefix . $elementKey . '_left');
            $face[$elementKey]['max_width'] = (string) $this->request->getPost($prefix . $elementKey . '_max_width');
            $face[$elementKey]['align'] = $this->sanitizeTextAlign((string) $this->request->getPost($prefix . $elementKey . '_align'));
            $face[$elementKey]['font_family'] = (string) $this->request->getPost($prefix . $elementKey . '_font_family');
            $face[$elementKey]['font_scale'] = (string) $this->request->getPost($prefix . $elementKey . '_font_scale');
            $face[$elementKey]['color'] = $this->sanitizeHexColor((string) $this->request->getPost($prefix . $elementKey . '_color'));
        }

        if ($faceKey === 'reverso') {
            foreach (['texto_legal', 'instrucciones'] as $elementKey) {
                $face[$elementKey]['top'] = (string) $this->request->getPost($prefix . $elementKey . '_top');
                $face[$elementKey]['left'] = (string) $this->request->getPost($prefix . $elementKey . '_left');
                $face[$elementKey]['max_width'] = (string) $this->request->getPost($prefix . $elementKey . '_max_width');
                $face[$elementKey]['height'] = (string) $this->request->getPost($prefix . $elementKey . '_height');
                $face[$elementKey]['align'] = $this->sanitizeTextAlign((string) $this->request->getPost($prefix . $elementKey . '_align'));
                $face[$elementKey]['font_family'] = (string) $this->request->getPost($prefix . $elementKey . '_font_family');
                $face[$elementKey]['font_scale'] = (string) $this->request->getPost($prefix . $elementKey . '_font_scale');
                $face[$elementKey]['color'] = $this->sanitizeHexColor((string) $this->request->getPost($prefix . $elementKey . '_color'));
                $face[$elementKey]['text'] = $this->sanitizeMultilineText((string) $this->request->getPost($prefix . $elementKey . '_text'));
            }

            $face['qr']['top'] = (string) $this->request->getPost($prefix . 'qr_top');
            $face['qr']['left'] = (string) $this->request->getPost($prefix . 'qr_left');
            $face['qr']['width'] = (string) $this->request->getPost($prefix . 'qr_width');
            $face['qr']['height'] = (string) $this->request->getPost($prefix . 'qr_height');
            $face['qr']['value'] = trim((string) $this->request->getPost($prefix . 'qr_value'));

            if ($this->request->getPost($prefix . 'qr_clear_image') !== null) {
                $face['qr']['image_url'] = '';
            }
        }

        $face['_canvas'] = [
            'width' => (string) max(1, (int) ($this->request->getPost($prefix . 'fondo_width') ?: ($face['_canvas']['width'] ?? 360))),
            'height' => (string) max(1, (int) ($this->request->getPost($prefix . 'fondo_height') ?: ($face['_canvas']['height'] ?? 540))),
        ];

        return $face;
    }

    private function resolveUploadedBackground(string $faceKey, array &$face): string
    {
        $existing = trim((string) ($face['url_fondo'] ?? ''));
        $file = $this->request->getFile('fondo_' . $faceKey);

        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return $existing;
        }

        $imgInfo = @getimagesize($file->getTempName());
        if (is_array($imgInfo)) {
            $face['_canvas'] = [
                'width' => (string) ((int) ($imgInfo[0] ?? 360)),
                'height' => (string) ((int) ($imgInfo[1] ?? 540)),
            ];
        }

        $baseName = $faceKey === 'anverso' ? 'base' : 'base-reverso';
        $nuevoNombre = $baseName . '.' . $file->getExtension();
        $file->move(FCPATH . 'carnet', $nuevoNombre, true);

        return '/carnet/' . $nuevoNombre;
    }

    private function sanitizeMultilineText(string $value): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", trim($value));
        return mb_substr($normalized, 0, 700);
    }

    private function resolveUploadedQrImage(string $faceKey, array $face): array
    {
        if (! is_array($face['qr'] ?? null)) {
            return $face;
        }

        $existing = trim((string) ($face['qr']['image_url'] ?? ''));
        $file = $this->request->getFile($faceKey . '_qr_image');

        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            $face['qr']['image_url'] = $existing;
            return $face;
        }

        $nuevoNombre = 'qr-' . $faceKey . '.' . $file->getExtension();
        $file->move(FCPATH . 'carnet', $nuevoNombre, true);

        $face['qr']['image_url'] = '/carnet/' . $nuevoNombre;

        return $face;
    }
}
