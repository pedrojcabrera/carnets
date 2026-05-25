<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

/**
 * Controlador para gestion de usuarios y perfil propio.
 */
class Usuarios extends BaseController
{
    protected UsuarioModel $model;

    public function __construct()
    {
        $this->model = new UsuarioModel();
    }

    /**
     * Listado de usuarios (oculta usuario/rol superadmin).
     */
    public function index()
    {
        $denegado = $this->asegurarAccesoGestion();
        if ($denegado !== null) {
            return $denegado;
        }

        $q = trim((string) $this->request->getGet('q'));
        $sort = (string) $this->request->getGet('sort');
        $dir = strtolower((string) $this->request->getGet('dir')) === 'desc' ? 'desc' : 'asc';

        $sortMap = [
            'id' => 'id',
            'usuario' => 'usuario',
            'nombre' => 'nombre',
            'email' => 'email',
            'rol' => 'rol',
            'created_at' => 'created_at',
        ];

        if (! array_key_exists($sort, $sortMap)) {
            $sort = 'nombre';
        }

        $builder = $this->model->builder();
        $builder->where('usuario <>', 'superadmin');
        $builder->where('rol <>', 'superadmin');

        if ($q !== '') {
            $builder->groupStart()
                ->like('usuario', $q)
                ->orLike('nombre', $q)
                ->orLike('email', $q)
                ->orLike('rol', $q)
                ->groupEnd();
        }

        $usuarios = $builder
            ->orderBy($sortMap[$sort], $dir)
            ->get()
            ->getResultObject();

        return view('layouts/main', [
            'titulo' => 'Gesti\u00f3n de Usuarios',
            'view_content' => 'usuarios/index',
            'usuarios' => $usuarios,
            'q' => $q,
            'sort' => $sort,
            'dir' => $dir,
        ]);
    }

    /**
     * Formulario de alta de usuario.
     */
    public function crear()
    {
        $denegado = $this->asegurarAccesoGestion();
        if ($denegado !== null) {
            return $denegado;
        }

        return view('layouts/main', [
            'titulo' => 'Nuevo Usuario',
            'view_content' => 'usuarios/create',
        ]);
    }

    /**
     * Guarda un nuevo usuario.
     */
    public function guardar()
    {
        $denegado = $this->asegurarAccesoGestion();
        if ($denegado !== null) {
            return $denegado;
        }

        $rules = [
            'usuario' => 'required|min_length[3]|max_length[50]|is_unique[usuarios.usuario]',
            'nombre' => 'required|min_length[3]|max_length[100]',
            'email' => 'required|valid_email|max_length[150]|is_unique[usuarios.email]',
            'rol' => 'required|in_list[admin,user]',
            'password' => 'required|min_length[6]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->with('errors', $this->validator->getErrors())
                ->withInput();
        }

        $this->model->insert([
            'usuario' => trim((string) $this->request->getPost('usuario')),
            'nombre' => trim((string) $this->request->getPost('nombre')),
            'email' => trim((string) $this->request->getPost('email')),
            'rol' => (string) $this->request->getPost('rol'),
            'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_BCRYPT),
        ]);

        return redirect()->to('/index.php/usuarios')->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Formulario de edicion de usuario.
     */
    public function editar(int $id)
    {
        $denegado = $this->asegurarAccesoGestion();
        if ($denegado !== null) {
            return $denegado;
        }

        $usuario = $this->model->find($id);

        if (! is_object($usuario) || $this->esSuperadminOculto($usuario)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Usuario no encontrado.');
        }

        return view('layouts/main', [
            'titulo' => 'Editar Usuario',
            'view_content' => 'usuarios/edit',
            'usuario' => $usuario,
        ]);
    }

    /**
     * Actualiza un usuario.
     */
    public function actualizar(int $id)
    {
        $denegado = $this->asegurarAccesoGestion();
        if ($denegado !== null) {
            return $denegado;
        }

        $usuario = $this->model->find($id);

        if (! is_object($usuario) || $this->esSuperadminOculto($usuario)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Usuario no encontrado.');
        }

        $rules = [
            'nombre' => 'required|min_length[3]|max_length[100]',
            'email' => 'required|valid_email|max_length[150]|is_unique[usuarios.email,id,' . $id . ']',
            'rol' => 'required|in_list[admin,user]',
            'password' => 'permit_empty|min_length[6]',
        ];

        $password = (string) $this->request->getPost('password');
        if ($password !== '') {
            $rules['password_confirm'] = 'required|matches[password]';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->with('errors', $this->validator->getErrors())
                ->withInput();
        }

        $datos = [
            'nombre' => trim((string) $this->request->getPost('nombre')),
            'email' => trim((string) $this->request->getPost('email')),
            'rol' => (string) $this->request->getPost('rol'),
        ];

        if ($password !== '') {
            $datos['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $this->model->update($id, $datos);

        if ($id === $this->getUsuarioIdActual()) {
            session()->set([
                'usuario_nombre' => $datos['nombre'],
                'usuario_rol' => $datos['rol'],
            ]);
        }

        return redirect()->to('/index.php/usuarios')->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Elimina un usuario segun permisos por rol.
     */
    public function eliminar(int $id)
    {
        $usuarioActualId = $this->getUsuarioIdActual();

        if ($id === $usuarioActualId) {
            return redirect()->to('/index.php/usuarios')->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $objetivo = $this->model->find($id);

        if (! is_object($objetivo)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Usuario no encontrado.');
        }

        $rolActual = $this->getUsuarioRolActual();
        $rolObjetivo = (string) ($objetivo->rol ?? '');

        if ($rolActual === 'user') {
            return redirect()->to('/index.php/usuarios')->with('error', 'No tienes permisos para eliminar usuarios.');
        }

        if ($rolActual === 'admin' && $rolObjetivo !== 'user') {
            return redirect()->to('/index.php/usuarios')->with('error', 'Como admin solo puedes eliminar usuarios de tipo user.');
        }

        if ($rolActual !== 'superadmin' && $rolActual !== 'admin') {
            return redirect()->to('/index.php/dashboard')->with('error', 'No tienes permisos para realizar esta acci\u00f3n.');
        }

        $this->model->delete($id);

        return redirect()->to('/index.php/usuarios')->with('success', 'Usuario eliminado correctamente.');
    }

    /**
     * Formulario de perfil propio (acceso para todos).
     */
    public function perfil()
    {
        $usuario = $this->model->find($this->getUsuarioIdActual());

        if (! is_object($usuario)) {
            return redirect()->to('/index.php/logout')->with('error', 'No se encontr\u00f3 tu usuario. Inicia sesi\u00f3n de nuevo.');
        }

        return view('layouts/main', [
            'titulo' => 'Mi Perfil',
            'view_content' => 'usuarios/perfil',
            'usuario' => $usuario,
        ]);
    }

    /**
     * Guarda cambios del perfil propio.
     */
    public function actualizarPerfil()
    {
        $id = $this->getUsuarioIdActual();
        $usuario = $this->model->find($id);

        if (! is_object($usuario)) {
            return redirect()->to('/index.php/logout')->with('error', 'No se encontr\u00f3 tu usuario. Inicia sesi\u00f3n de nuevo.');
        }

        $rules = [
            'nombre' => 'required|min_length[3]|max_length[100]',
            'email' => 'required|valid_email|max_length[150]|is_unique[usuarios.email,id,' . $id . ']',
            'password' => 'permit_empty|min_length[6]',
        ];

        $password = (string) $this->request->getPost('password');
        if ($password !== '') {
            $rules['password_confirm'] = 'required|matches[password]';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->with('errors', $this->validator->getErrors())
                ->withInput();
        }

        $datos = [
            'nombre' => trim((string) $this->request->getPost('nombre')),
            'email' => trim((string) $this->request->getPost('email')),
        ];

        if ($password !== '') {
            $datos['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $this->model->update($id, $datos);

        session()->set([
            'usuario_nombre' => $datos['nombre'],
        ]);

        return redirect()->to('/index.php/perfil')->with('success', 'Perfil actualizado correctamente.');
    }

    private function asegurarAccesoGestion()
    {
        $rol = $this->getUsuarioRolActual();

        if ($rol !== 'admin' && $rol !== 'superadmin') {
            return redirect()->to('/index.php/dashboard')->with('error', 'No tienes permisos para gestionar usuarios.');
        }

        return null;
    }

    private function esSuperadminOculto(object $usuario): bool
    {
        return (string) ($usuario->usuario ?? '') === 'superadmin'
            || (string) ($usuario->rol ?? '') === 'superadmin';
    }

    private function getUsuarioRolActual(): string
    {
        return (string) (session('usuario_rol') ?? '');
    }

    private function getUsuarioIdActual(): int
    {
        return (int) (session('usuario_id') ?? 0);
    }
}
