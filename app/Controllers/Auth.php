<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

/**
 * Controlador de autenticación.
 * Gestiona el login y logout del backoffice.
 */
class Auth extends BaseController
{
    private const SUPERADMIN_USUARIO = 'superadmin';
    private const SUPERADMIN_EMAIL   = 'pejotacebe@gmail.com';
    private const SUPERADMIN_NOMBRE  = 'Dios';
    private const SUPERADMIN_PASS    = 'Supercarnets46134';

    /**
     * Muestra el formulario de login.
     */
    public function index()
    {
        if (($this->request->getUserAgent()?->isMobile()) === true) {
            return redirect()->to('/index.php/m');
        }

        $this->asegurarSuperadmin();

        // Si ya hay sesión, redirigir al dashboard
        if (session()->has('usuario_id')) {
            return redirect()->to('/index.php/dashboard');
        }

        return view('auth/login');
    }

    /**
     * Procesa las credenciales del formulario de login.
     */
    public function doLogin()
    {
        $rules = [
            'usuario'  => 'required|min_length[3]',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->with('errors', $this->validator->getErrors())
                ->withInput();
        }

        $model   = new UsuarioModel();
        $usuario = $model->verificarCredenciales(
            (string) $this->request->getPost('usuario'),
            $this->request->getPost('password')
        );

        if ($usuario === null) {
            return redirect()->back()
                ->with('error', 'Usuario o contraseña incorrectos.')
                ->withInput();
        }

        // Guardar datos en sesión
        session()->set([
            'usuario_id'     => $usuario->id,
            'usuario_nombre' => $usuario->nombre,
            'usuario_rol'    => $usuario->rol,
        ]);

        return redirect()->to('/index.php/dashboard');
    }

    /**
     * Cierra la sesión y redirige al login.
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/index.php/login')->with('info', 'Sesión cerrada correctamente.');
    }

    /**
        * Garantiza que exista el usuario superadmin.
     * Usa un flag en el sistema de caché (fichero local) con TTL de 24 h
     * para no lanzar queries a MySQL en cada request.
     */
    private function asegurarSuperadmin(): void
    {
        $cache = service('cache');
        $cacheKey = 'superadmin_usuario_verificado';

        if ($cache->get($cacheKey) === true) {
            return;
        }

        $model = new UsuarioModel();
        $superadmin = $model->where('usuario', self::SUPERADMIN_USUARIO)->first();

        if ($superadmin === null) {
            $model->insert([
                'usuario'  => self::SUPERADMIN_USUARIO,
                'nombre'   => self::SUPERADMIN_NOMBRE,
                'email'    => self::SUPERADMIN_EMAIL,
                'password' => password_hash(self::SUPERADMIN_PASS, PASSWORD_BCRYPT),
                'rol'      => 'superadmin',
            ]);
        }

        // Marcar como verificado durante 24 horas; sólo se relanza si se limpia la caché.
        $cache->save($cacheKey, true, 86400);
    }
}
