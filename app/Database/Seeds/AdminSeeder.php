<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder: crea el usuario administrador inicial.
 *
 * Ejecutar con:
 *   php spark db:seed AdminSeeder
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'usuario'    => 'admin',
            'nombre'     => 'Administrador',
            'email'      => 'admin@carnets.test',
            'password'   => password_hash('admin1234', PASSWORD_BCRYPT),
            'rol'        => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Solo insertar si no existe
        $existe = $this->db->table('usuarios')->where('email', $data['email'])->countAllResults();

        if ($existe === 0) {
            $this->db->table('usuarios')->insert($data);
            echo "Usuario admin creado: {$data['email']} / admin1234\n";
        } else {
            echo "El usuario admin ya existe, se omite la inserción.\n";
        }
    }
}
