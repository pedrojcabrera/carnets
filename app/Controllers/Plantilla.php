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

        // Posiciones por defecto si no existe plantilla
        $posiciones = [
            '_canvas'     => ['width' => '360', 'height' => '540'],
            '_download'   => ['rotation' => 'keep'],
            'foto'        => ['top' => '120', 'left' => '30',  'width' => '80',  'height' => '80'],
            'nombre'      => ['top' => '220', 'left' => '30', 'max_width' => '220', 'align' => 'left', 'font_family' => "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif", 'font_scale' => '1.00', 'color' => '#000000'],
            'tipo_socio'  => ['top' => '235', 'left' => '30', 'max_width' => '220', 'align' => 'left', 'font_family' => "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif", 'font_scale' => '1.00', 'color' => '#000000'],
            'num_socio'   => ['top' => '250', 'left' => '30', 'max_width' => '220', 'align' => 'left', 'font_family' => "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif", 'font_scale' => '1.00', 'color' => '#000000'],
            'valido_hasta'=> ['top' => '280', 'left' => '30', 'max_width' => '220', 'align' => 'left', 'font_family' => "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif", 'font_scale' => '1.00', 'color' => '#000000'],
        ];

        $canvasEnConfig = false;

        if ($plantilla && ! empty($plantilla->config_posiciones)) {
            $guardadas = json_decode($plantilla->config_posiciones, true);

            if (is_array($guardadas)) {
                $posiciones = array_replace_recursive($posiciones, $guardadas);
                $canvasEnConfig = isset($guardadas['_canvas']) && is_array($guardadas['_canvas']);
            }
        }

        if (! $canvasEnConfig && $plantilla && ! empty($plantilla->url_fondo)) {
            $path = parse_url((string) $plantilla->url_fondo, PHP_URL_PATH);
            $path = is_string($path) ? ltrim($path, '/\\') : '';
            $localPath = $path !== '' ? FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path) : '';

            if ($localPath !== '' && is_file($localPath)) {
                $imgInfo = @getimagesize($localPath);
                if (is_array($imgInfo)) {
                    $posiciones['_canvas']['width'] = (string) ((int) ($imgInfo[0] ?? 360));
                    $posiciones['_canvas']['height'] = (string) ((int) ($imgInfo[1] ?? 540));
                }
            }
        }

        return view('layouts/main', [
            'titulo'     => 'Plantilla del Carnet',
            'view_content' => 'plantilla/index',
            'plantilla'  => $plantilla,
            'posiciones' => $posiciones,
        ]);
    }

    /**
     * Guarda o actualiza la plantilla activa.
     */
    public function guardar()
    {
        $rules = [
            'fondo' => 'if_exist|uploaded[fondo]|max_size[fondo,2048]|is_image[fondo]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->with('errors', $this->validator->getErrors())
                ->withInput();
        }

        // Recoger posiciones del formulario
        $posiciones = [
            'foto' => [
                'top'    => $this->request->getPost('foto_top'),
                'left'   => $this->request->getPost('foto_left'),
                'width'  => $this->request->getPost('foto_width'),
                'height' => $this->request->getPost('foto_height'),
            ],
            'nombre' => [
                'top'       => $this->request->getPost('nombre_top'),
                'left'      => $this->request->getPost('nombre_left'),
                'max_width' => $this->request->getPost('nombre_max_width'),
                'align'     => $this->request->getPost('nombre_align'),
                'font_family' => $this->request->getPost('nombre_font_family'),
                'font_scale' => $this->request->getPost('nombre_font_scale'),
                'color' => $this->sanitizeHexColor((string) $this->request->getPost('nombre_color')),
            ],
            'tipo_socio' => [
                'top'       => $this->request->getPost('tipo_socio_top'),
                'left'      => $this->request->getPost('tipo_socio_left'),
                'max_width' => $this->request->getPost('tipo_socio_max_width'),
                'align'     => $this->request->getPost('tipo_socio_align'),
                'font_family' => $this->request->getPost('tipo_socio_font_family'),
                'font_scale' => $this->request->getPost('tipo_socio_font_scale'),
                'color' => $this->sanitizeHexColor((string) $this->request->getPost('tipo_socio_color')),
            ],
            'num_socio' => [
                'top'       => $this->request->getPost('num_socio_top'),
                'left'      => $this->request->getPost('num_socio_left'),
                'max_width' => $this->request->getPost('num_socio_max_width'),
                'align'     => $this->request->getPost('num_socio_align'),
                'font_family' => $this->request->getPost('num_socio_font_family'),
                'font_scale' => $this->request->getPost('num_socio_font_scale'),
                'color' => $this->sanitizeHexColor((string) $this->request->getPost('num_socio_color')),
            ],
            'valido_hasta' => [
                'top'       => $this->request->getPost('valido_hasta_top'),
                'left'      => $this->request->getPost('valido_hasta_left'),
                'max_width' => $this->request->getPost('valido_hasta_max_width'),
                'align'     => $this->request->getPost('valido_hasta_align'),
                'font_family' => $this->request->getPost('valido_hasta_font_family'),
                'font_scale' => $this->request->getPost('valido_hasta_font_scale'),
                'color' => $this->sanitizeHexColor((string) $this->request->getPost('valido_hasta_color')),
            ],
        ];

        $plantillaActual = $this->model->getActiva();
        $urlFondo        = $plantillaActual->url_fondo ?? null;

        $canvasWidth = (int) ($this->request->getPost('fondo_width') ?? 0);
        $canvasHeight = (int) ($this->request->getPost('fondo_height') ?? 0);

        if ($plantillaActual && ! empty($plantillaActual->config_posiciones)) {
            $guardadas = json_decode($plantillaActual->config_posiciones, true);

            if (is_array($guardadas) && isset($guardadas['_canvas']) && is_array($guardadas['_canvas'])) {
                $canvasWidth = $canvasWidth > 0 ? $canvasWidth : (int) ($guardadas['_canvas']['width'] ?? 0);
                $canvasHeight = $canvasHeight > 0 ? $canvasHeight : (int) ($guardadas['_canvas']['height'] ?? 0);
            }
        }

        // Subir nueva imagen de fondo si se ha enviado
        $file = $this->request->getFile('fondo');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $imgInfo = @getimagesize($file->getTempName());
            if (is_array($imgInfo)) {
                $canvasWidth = (int) ($imgInfo[0] ?? 0);
                $canvasHeight = (int) ($imgInfo[1] ?? 0);
            }

            $nuevoNombre = 'base.' . $file->getExtension();
            $file->move(FCPATH . 'carnet', $nuevoNombre, true);
            $urlFondo = '/carnet/' . $nuevoNombre;
        }

        $canvasWidth = $canvasWidth > 0 ? $canvasWidth : 360;
        $canvasHeight = $canvasHeight > 0 ? $canvasHeight : 540;

        $posiciones['_canvas'] = [
            'width' => (string) $canvasWidth,
            'height' => (string) $canvasHeight,
        ];

        $posiciones['_download'] = [
            'rotation' => $this->sanitizeDownloadRotation((string) $this->request->getPost('download_rotation')),
        ];

        $datos = [
            'url_fondo'         => $urlFondo,
            'config_posiciones' => json_encode($posiciones),
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
}
