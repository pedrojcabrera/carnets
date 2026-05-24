<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para la tabla "usuarios".
 * Gestiona los usuarios del backoffice con roles: superadmin, admin y user.
 */
class UsuarioModel extends Model
{
    protected $table      = 'usuarios';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'usuario',
        'nombre',
        'email',
        'password',
        'rol',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'usuario' => 'required|min_length[3]|max_length[50]|is_unique[usuarios.usuario,id,{id}]',
        'nombre' => 'required|min_length[3]',
        'email'  => 'required|valid_email|is_unique[usuarios.email,id,{id}]',
        'rol'    => 'required|in_list[superadmin,admin,user]',
    ];

    /**
     * Busca un usuario por su campo "usuario" y verifica la contraseña.
     * Devuelve el registro si las credenciales son válidas, null si no.
     */
    public function verificarCredenciales(string $usuario, string $password): ?object
    {
        $usuario = $this->where('usuario', $usuario)->first();

        if ($usuario && password_verify($password, $usuario->password)) {
            return $usuario;
        }

        return null;
    }
}
