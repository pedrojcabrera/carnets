<?php

namespace App\Controllers;

use App\Models\SocioModel;
use App\Models\PlantillaModel;
use App\Models\CarnetPreferenciaModel;

/**
 * Controlador del carnet digital (PWA pública).
 */
class Carnet extends BaseController
{
    private const OTP_SESSION_KEY = 'carnet_otp_pending';
    private const OTP_TTL_SECONDS = 600;
    private const VERIFICATION_ERROR_MESSAGE = 'Problema de verificacion. Por favor, intente de nuevo una vez solventado el problema.';

    private const FONT_OPTIONS = [
        "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif",
        "'Montserrat', 'Segoe UI', system-ui, sans-serif",
        "'Lora', Georgia, serif",
        "'Oswald', 'Segoe UI', sans-serif",
    ];

    private const FONT_TARGETS = ['nombre', 'tipo', 'numero', 'valido'];

    /**
     * Pantalla pública inicial para pedir el DNI y abrir el carnet.
     */
    public function inicio(): string
    {
        return view('carnet/entrada', [
            'dni' => old('dni', ''),
            'error' => session()->getFlashdata('error'),
            'info' => session()->getFlashdata('info'),
            'otp_required' => (bool) session()->getFlashdata('otp_required'),
        ]);
    }

    /**
     * Recibe el DNI y redirige al carnet público generado.
     */
    public function abrir()
    {
        $session = session();
        $dni = strtoupper(trim((string) $this->request->getPost('dni')));
        $codigo = trim((string) $this->request->getPost('codigo_verificacion'));
        $rutaActual = '/' . ltrim((string) $this->request->getUri()->getPath(), '/');
        $esFlujoMovil = str_starts_with($rutaActual, '/m/');

        if ($dni === '') {
            log_message('warning', 'OTP request rejected: empty DNI.');
            return $this->rejectVerification('DNI vacío.', true);
        }

        $socio = $this->findSocioByDni($dni);

        if (! is_object($socio) || $this->hasMissingSocioFields($socio)) {
            log_message('warning', 'OTP request rejected: socio not found or missing required fields for DNI {dni}.', [
                'dni' => $dni,
            ]);
            if (! is_object($socio)) {
                return $this->rejectVerification('No existe socio para el DNI indicado: ' . $dni . '.', true);
            }

            return $this->rejectVerification('Faltan campos obligatorios para el socio con DNI ' . $dni . '.', true);
        }

        if ($esFlujoMovil) {
            if ($codigo === '') {
                $pendingActual = $session->get(self::OTP_SESSION_KEY);
                $tienePendiente = is_array($pendingActual)
                    && isset($pendingActual['dni'], $pendingActual['expires_at'])
                    && (string) $pendingActual['dni'] === $dni
                    && (int) $pendingActual['expires_at'] >= time();

                if ($tienePendiente) {
                    return redirect()->to('/m')
                        ->withInput()
                        ->with('otp_required', true)
                        ->with('info', 'Ya se envio un codigo de verificacion a tu email.');
                }

                $codigoGenerado = (string) random_int(100000, 999999);

                $session->set(self::OTP_SESSION_KEY, [
                    'dni' => $dni,
                    'hash' => password_hash($codigoGenerado, PASSWORD_DEFAULT),
                    'expires_at' => time() + self::OTP_TTL_SECONDS,
                ]);

                $emailSocio = trim((string) ($socio->email ?? ''));

                if ($emailSocio === '' || ! filter_var($emailSocio, FILTER_VALIDATE_EMAIL)) {
                    log_message('warning', 'OTP bloqueado: email vacío o inválido para DNI {dni}.', ['dni' => $dni]);
                    $session->remove(self::OTP_SESSION_KEY);
                    return $this->rejectVerification('Email no registrado o inválido para este socio.', true);
                }

                $sendResult = $this->sendOtpCode($emailSocio, $codigoGenerado, (string) $socio->nombre_completo);

                if ($sendResult !== true) {
                    log_message('error', 'OTP request rejected: email send failed for DNI {dni}: {err}', [
                        'dni' => $dni,
                        'err' => $sendResult,
                    ]);

                    if (ENVIRONMENT !== 'production') {
                        return redirect()->to('/m')
                            ->withInput()
                            ->with('otp_required', true)
                            ->with('info', 'SMTP no disponible en local. Codigo temporal de prueba: ' . $codigoGenerado);
                    }

                    $session->remove(self::OTP_SESSION_KEY);

                    return $this->rejectVerification('No se pudo enviar el codigo de verificacion. Intentalo de nuevo en unos minutos.', true);
                }

                return redirect()->to('/m')
                    ->withInput()
                    ->with('otp_required', true)
                    ->with('info', 'Se ha enviado un codigo de verificacion a tu email.');
            }

            $pending = $session->get(self::OTP_SESSION_KEY);
            $esPendienteValido = is_array($pending)
                && isset($pending['dni'], $pending['hash'], $pending['expires_at'])
                && (string) $pending['dni'] === $dni
                && is_string($pending['hash'])
                && (int) $pending['expires_at'] >= time()
                && password_verify($codigo, (string) $pending['hash']);

            if (! $esPendienteValido) {
                $session->remove(self::OTP_SESSION_KEY);
                log_message('warning', 'OTP validation failed for DNI {dni}.', [
                    'dni' => $dni,
                ]);

                return $this->rejectVerification('Código OTP inválido o caducado.', true, true);
            }

            $session->remove(self::OTP_SESSION_KEY);
        }

        return redirect()->to('/c/' . rawurlencode($dni));
    }

    /**
     * Muestra el carnet digital de un socio por ID o por DNI.
     *
     * @param string $ref ID o DNI del socio
     */
    public function ver(string $ref): string
    {
        $socioModel    = new SocioModel();
        $plantillaModel = new PlantillaModel();
        $preferenciaModel = new CarnetPreferenciaModel();

        $ref = trim($ref);

        $socio = ctype_digit($ref)
            ? $socioModel->find((int) $ref)
            : $socioModel->where('dni', strtoupper($ref))->first();

        if (! is_object($socio)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                'El carnet solicitado no existe.'
            );
        }

        $plantilla  = $plantillaModel->getActiva();
        $posiciones = [
            '_canvas'     => ['width' => '360', 'height' => '540'],
            'foto'        => ['top' => '120', 'left' => '30',  'width' => '80',  'height' => '80'],
            'nombre'      => ['top' => '220', 'left' => '30', 'max_width' => '220', 'align' => 'left', 'font_family' => "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif", 'font_scale' => '1.00'],
            'tipo_socio'  => ['top' => '235', 'left' => '30', 'max_width' => '220', 'align' => 'left', 'font_family' => "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif", 'font_scale' => '1.00'],
            'num_socio'   => ['top' => '250', 'left' => '30', 'max_width' => '220', 'align' => 'left', 'font_family' => "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif", 'font_scale' => '1.00'],
            'valido_hasta'=> ['top' => '280', 'left' => '30', 'max_width' => '220', 'align' => 'left', 'font_family' => "'Noto Sans Armenian', 'Segoe UI', system-ui, sans-serif", 'font_scale' => '1.00'],
        ];

        if ($plantilla && ! empty($plantilla->config_posiciones)) {
            $guardadas = json_decode($plantilla->config_posiciones, true);

            if (is_array($guardadas)) {
                $posiciones = array_replace_recursive($posiciones, $guardadas);
            }
        }

        $fondoAncho = (int) ($posiciones['_canvas']['width'] ?? 360);
        $fondoAlto = (int) ($posiciones['_canvas']['height'] ?? 540);

        if (($fondoAncho <= 0 || $fondoAlto <= 0) && $plantilla && ! empty($plantilla->url_fondo)) {
            $path = parse_url((string) $plantilla->url_fondo, PHP_URL_PATH);
            $path = is_string($path) ? ltrim($path, '/\\') : '';
            $localPath = $path !== '' ? FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path) : '';

            if ($localPath !== '' && is_file($localPath)) {
                $imgInfo = @getimagesize($localPath);
                if (is_array($imgInfo)) {
                    $fondoAncho = (int) ($imgInfo[0] ?? 360);
                    $fondoAlto = (int) ($imgInfo[1] ?? 540);
                }
            }
        }

        if ($fondoAncho <= 0 || $fondoAlto <= 0) {
            $fondoAncho = 360;
            $fondoAlto = 540;
        }

        $rotacionDescarga = $this->sanitizeDownloadRotation((string) ($posiciones['_download']['rotation'] ?? 'keep'));

        $numSocioFormateado = str_pad((string) $socio->num_socio, 6, '0', STR_PAD_LEFT);
        $preferencias = $preferenciaModel->getBySocioId((int) $socio->id);
        $configTipografia = [];

        if ($preferencias && ! empty($preferencias->config_tipografia)) {
            $guardadas = json_decode((string) $preferencias->config_tipografia, true);
            if (is_array($guardadas)) {
                $configTipografia = $this->sanitizeTypographyConfig($guardadas);
            }
        }

        return view('carnet/solo', [
            'nombre_completo' => $socio->nombre_completo,
            'tipo_socio'      => $socio->tipo_socio ?? 'Socio/a',
            'num_socio'       => $numSocioFormateado,
            'valido_hasta'    => $socio->valido_hasta,
            'url_foto'        => $socio->url_foto,
            'url_fondo'       => $plantilla && ! empty($plantilla->url_fondo) ? (string) $plantilla->url_fondo : '/carnet/base.png',
            'posiciones'      => $posiciones,
            'fondo_ancho'     => $fondoAncho,
            'fondo_alto'      => $fondoAlto,
            'download_rotation' => $rotacionDescarga,
            'socio_id'        => $socio->id,
            'tipografia_config' => $configTipografia,
        ]);
    }

    /**
     * Endpoint directo para obtener el carnet por DNI desde móvil.
     */
    public function verPorDni(string $dni): string
    {
        return $this->ver($dni);
    }

    /**
     * Devuelve preferencias tipograficas por socio en formato JSON.
     */
    public function preferencias(string $ref)
    {
        $socioModel = new SocioModel();
        $preferenciaModel = new CarnetPreferenciaModel();

        $socio = ctype_digit($ref)
            ? $socioModel->find((int) $ref)
            : $socioModel->where('dni', strtoupper(trim($ref)))->first();

        if (! is_object($socio)) {
            return $this->response->setStatusCode(404)->setJSON([
                'ok' => false,
                'message' => 'Socio no encontrado.',
            ]);
        }

        $preferencias = $preferenciaModel->getBySocioId((int) $socio->id);
        $config = [];

        if ($preferencias && ! empty($preferencias->config_tipografia)) {
            $guardadas = json_decode((string) $preferencias->config_tipografia, true);
            if (is_array($guardadas)) {
                $config = $this->sanitizeTypographyConfig($guardadas);
            }
        }

        return $this->response->setJSON([
            'ok' => true,
            'socio_id' => (int) $socio->id,
            'config' => $config,
        ]);
    }

    /**
     * Guarda preferencias tipograficas por socio.
     */
    public function guardarPreferencias(string $ref)
    {
        $socioModel = new SocioModel();
        $preferenciaModel = new CarnetPreferenciaModel();

        $socio = ctype_digit($ref)
            ? $socioModel->find((int) $ref)
            : $socioModel->where('dni', strtoupper(trim($ref)))->first();

        if (! is_object($socio)) {
            return $this->response->setStatusCode(404)->setJSON([
                'ok' => false,
                'message' => 'Socio no encontrado.',
            ]);
        }

        $payload = $this->request->getJSON(true);
        if (! is_array($payload)) {
            $payload = $this->request->getRawInput();
        }

        $incoming = is_array($payload) && isset($payload['config']) && is_array($payload['config'])
            ? $payload['config']
            : [];

        $config = $this->sanitizeTypographyConfig($incoming);

        $existente = $preferenciaModel->getBySocioId((int) $socio->id);
        $datos = [
            'socio_id' => (int) $socio->id,
            'config_tipografia' => json_encode($config),
        ];

        if ($existente) {
            $preferenciaModel->update((int) $existente->id, $datos);
        } else {
            $preferenciaModel->insert($datos);
        }

        return $this->response->setJSON([
            'ok' => true,
            'config' => $config,
        ]);
    }

    /**
     * Valida y normaliza estructura de tipografias por elemento.
     *
     * @param array<string, mixed> $config
     * @return array<string, array<string, string>>
     */
    private function sanitizeTypographyConfig(array $config): array
    {
        $normalized = [];

        foreach (self::FONT_TARGETS as $target) {
            $item = $config[$target] ?? null;
            if (! is_array($item)) {
                continue;
            }

            $font = isset($item['font']) && is_string($item['font']) ? trim($item['font']) : '';
            $sizeRaw = isset($item['size']) ? (string) $item['size'] : '';
            $colorRaw = isset($item['color']) ? (string) $item['color'] : '';
            $size = (float) $sizeRaw;
            $color = $this->sanitizeHexColor($colorRaw);

            if (! in_array($font, self::FONT_OPTIONS, true)) {
                $font = self::FONT_OPTIONS[0];
            }

            if ($size < 0.8) {
                $size = 0.8;
            }

            if ($size > 1.4) {
                $size = 1.4;
            }

            $normalized[$target] = [
                'font' => $font,
                'size' => number_format($size, 2, '.', ''),
                'color' => $color,
            ];
        }

        return $normalized;
    }

    /**
     * Normaliza color hexadecimal en formato #rrggbb.
     */
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

    private function hasMissingSocioFields(object $socio): bool
    {
        $required = [
            'dni',
            'nombre_completo',
            'num_socio',
            'tipo_socio',
            'valido_hasta',
        ];

        foreach ($required as $field) {
            $value = $socio->{$field} ?? null;
            if ($value === null || trim((string) $value) === '') {
                log_message('warning', 'OTP hasMissingSocioFields: campo [{field}] vacío o nulo para socio {dni}.', [
                    'field' => $field,
                    'dni'   => $socio->dni ?? '?',
                ]);
                return true;
            }
        }

        return false;
    }

    /**
     * Envía el código OTP por email.
     * Devuelve true en éxito, o string con el error en fallo.
     */
    private function sendOtpCode(string $email, string $code, string $name): string|true
    {
        $email = trim($email);
        $name = trim($name);

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Email de destino inválido: ' . $email;
        }

        $mailer = service('email');
        $mailer->clear(true);

        $emailConfig = config('Email');
        $fromEmail = trim((string) ($emailConfig->fromEmail ?? ''));
        $fromName = trim((string) ($emailConfig->fromName ?? 'Carnet Digital'));

        if ($fromEmail === '') {
            $fromEmail = trim((string) ($emailConfig->SMTPUser ?? ''));
        }

        if ($fromEmail === '' || ! filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            return 'Email remitente no configurado.';
        }

        $mailer->setFrom($fromEmail, $fromName !== '' ? $fromName : 'Carnet Digital');
        $mailer->setTo($email);
        $mailer->setSubject('Codigo de verificacion Carnet Digital');
        $mailer->setMessage(
            '<p>Hola ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ',</p>'
            . '<p>Tu código de verificación es: <strong>' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '</strong></p>'
            . '<p>Este código caduca en 10 minutos.</p>'
            . '<p>Si no solicitaste este acceso, ignora este mensaje.</p>'
        );

        $sent = $mailer->send();

        if (! $sent) {
            $smtpDebug = strip_tags((string) $mailer->printDebugger(['headers']));
            log_message('error', 'OTP email send error: {debug}', ['debug' => $smtpDebug]);
            return 'SMTP error: ' . mb_substr(trim($smtpDebug), 0, 300);
        }

        return true;
    }

    private function findSocioByDni(string $dni): ?object
    {
        $model = new SocioModel();
        $socio = $model->where('dni', $dni)->first();

        if (is_object($socio)) {
            return $socio;
        }

        $db = \Config\Database::connect();
        $row = $db->query('SELECT * FROM socios WHERE UPPER(TRIM(dni)) = ? LIMIT 1', [$dni])->getRow();

        return is_object($row) ? $row : null;
    }

    private function rejectVerification(string $reason, bool $withInput = false, bool $otpRequired = false)
    {
        $redirect = $otpRequired ? redirect()->to('/m') : redirect()->back();

        if ($withInput) {
            $redirect = $redirect->withInput();
        }

        $message = trim($reason) !== '' ? $reason : self::VERIFICATION_ERROR_MESSAGE;
        if ($otpRequired) {
            return $redirect->with('otp_required', true)->with('error', $message);
        }

        return $redirect->with('error', $message);
    }
}
