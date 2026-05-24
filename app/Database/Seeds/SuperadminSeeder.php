<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Crea el superadmin si no existe y garantiza que solo haya uno.
 */
class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        $usuario = 'superadmin';
        $email = 'superadmin@carnets.test';
        $nombre = 'Dios';
        $passwordPlano = 'Supercarnets46134';

        // Si no existe ningún superadmin, crear el principal
        $existeSuperadmin = $this->db->table('usuarios')
            ->where('rol', 'superadmin')
            ->countAllResults();

        if ($existeSuperadmin === 0) {
            $this->db->table('usuarios')->insert([
                'usuario'    => $usuario,
                'nombre'     => $nombre,
                'email'      => $email,
                'password'   => password_hash($passwordPlano, PASSWORD_BCRYPT),
                'rol'        => 'superadmin',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            echo "Superadmin creado: {$email}\n";
        }

        // Forzar datos exactos del superadmin principal
        $principal = $this->db->table('usuarios')
            ->select('id')
            ->where('rol', 'superadmin')
            ->orderBy('id', 'ASC')
            ->get()
            ->getFirstRow('array');

        if (! empty($principal['id'])) {
            $this->db->table('usuarios')
                ->where('id', (int) $principal['id'])
                ->update([
                    'usuario'    => $usuario,
                    'nombre'     => $nombre,
                    'email'      => $email,
                    'password'   => password_hash($passwordPlano, PASSWORD_BCRYPT),
                    'rol'        => 'superadmin',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        }

        // Asegurar unicidad: mantener el más antiguo como superadmin
        $rows = $this->db->table('usuarios')
            ->select('id')
            ->where('rol', 'superadmin')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        if (count($rows) > 1) {
            for ($i = 1; $i < count($rows); $i++) {
                $this->db->table('usuarios')
                    ->where('id', (int) $rows[$i]['id'])
                    ->update(['rol' => 'admin', 'updated_at' => date('Y-m-d H:i:s')]);
            }
            echo "Se normalizaron superadmins duplicados a rol admin.\n";
        }
    }
}
