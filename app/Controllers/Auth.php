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
    private const SUPERADMIN_EMAIL   = 'superadmin@carnets.test';
    private const SUPERADMIN_NOMBRE  = 'Dios';
    private const SUPERADMIN_PASS    = 'Supercarnets46134';

    /**
     * Muestra el formulario de login.
     */
    public function index()
    {
        $this->asegurarSuperadmin();

        // Si ya hay sesión, redirigir al dashboard
        if (session()->has('usuario_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    /**
     * Procesa las credenciales del formulario de login.
     */
    public function doLogin()
    {
        $this->asegurarSuperadmin();

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

        return redirect()->to('/dashboard');
    }

    /**
     * Cierra la sesión y redirige al login.
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('info', 'Sesión cerrada correctamente.');
    }

    /**
     * Garantiza que exista un único superadmin.
     * Si no existe, lo crea. Si hay más de uno, degrada los extras a admin.
     */
    private function asegurarSuperadmin(): void
    {
        $model = new UsuarioModel();

        $superadmins = $model->where('rol', 'superadmin')->orderBy('id', 'ASC')->findAll();

        if (count($superadmins) === 0) {
            $model->insert([
                'usuario'  => self::SUPERADMIN_USUARIO,
                'nombre'   => self::SUPERADMIN_NOMBRE,
                'email'    => self::SUPERADMIN_EMAIL,
                'password' => password_hash(self::SUPERADMIN_PASS, PASSWORD_BCRYPT),
                'rol'      => 'superadmin',
            ]);
            return;
        }

        // Asegurar datos exactos del superadmin principal
        $model->update($superadmins[0]->id, [
            'usuario'  => self::SUPERADMIN_USUARIO,
            'nombre'   => self::SUPERADMIN_NOMBRE,
            'email'    => self::SUPERADMIN_EMAIL,
            'password' => password_hash(self::SUPERADMIN_PASS, PASSWORD_BCRYPT),
            'rol'      => 'superadmin',
        ]);

        if (count($superadmins) > 1) {
            // Mantener solo el más antiguo como superadmin
            for ($i = 1; $i < count($superadmins); $i++) {
                $model->update($superadmins[$i]->id, ['rol' => 'admin']);
            }
        }
    }
}
