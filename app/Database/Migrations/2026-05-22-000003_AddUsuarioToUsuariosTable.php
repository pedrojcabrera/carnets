<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Añade el campo usuario a la tabla usuarios en instalaciones existentes.
 */
class AddUsuarioToUsuariosTable extends Migration
{
    public function up(): void
    {
        if (! $this->columnaExiste('usuarios', 'usuario')) {
            $this->forge->addColumn('usuarios', [
                'usuario' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'id',
                ],
            ]);
        }

        // Completar usuario en filas existentes (si faltan)
        $rows = $this->db->table('usuarios')->select('id, email, rol, usuario')->get()->getResultArray();
        foreach ($rows as $row) {
            if (! empty($row['usuario'])) {
                continue;
            }

            $baseUsuario = 'user' . (int) $row['id'];
            if (($row['rol'] ?? '') === 'superadmin') {
                $baseUsuario = 'superadmin';
            } elseif (! empty($row['email']) && str_contains($row['email'], '@')) {
                $baseUsuario = strtolower((string) strstr((string) $row['email'], '@', true));
            }

            // Asegurar unicidad
            $usuarioFinal = $baseUsuario;
            $sufijo       = 1;
            while ($this->db->table('usuarios')->where('usuario', $usuarioFinal)->where('id <>', (int) $row['id'])->countAllResults() > 0) {
                $usuarioFinal = $baseUsuario . $sufijo;
                $sufijo++;
            }

            $this->db->table('usuarios')->where('id', (int) $row['id'])->update(['usuario' => $usuarioFinal]);
        }

        // Dejar columna NOT NULL y única
        $this->forge->modifyColumn('usuarios', [
            'usuario' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
        ]);

        $dbName = $this->db->getDatabase();
        $idx    = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM information_schema.statistics
             WHERE table_schema = ?
               AND table_name = 'usuarios'
               AND index_name = 'usuarios_usuario_unique'",
            [$dbName]
        )->getRowArray();

        if ((int) ($idx['total'] ?? 0) === 0) {
            $this->db->query('ALTER TABLE usuarios ADD UNIQUE KEY usuarios_usuario_unique (usuario)');
        }
    }

    public function down(): void
    {
        // En rollback solo quitamos el índice/columna si existe.
        if ($this->columnaExiste('usuarios', 'usuario')) {
            $dbName = $this->db->getDatabase();
            $idx    = $this->db->query(
                "SELECT COUNT(*) AS total
                 FROM information_schema.statistics
                 WHERE table_schema = ?
                   AND table_name = 'usuarios'
                   AND index_name = 'usuarios_usuario_unique'",
                [$dbName]
            )->getRowArray();

            if ((int) ($idx['total'] ?? 0) > 0) {
                $this->db->query('ALTER TABLE usuarios DROP INDEX usuarios_usuario_unique');
            }
            $this->forge->dropColumn('usuarios', 'usuario');
        }
    }

    private function columnaExiste(string $tabla, string $columna): bool
    {
        $row = $this->db->query("SHOW COLUMNS FROM {$tabla} LIKE ?", [$columna])->getRowArray();
        return ! empty($row);
    }
}
