<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Añade el campo dni a la tabla socios para instalaciones existentes.
 */
class AddDniToSociosTable extends Migration
{
    public function up(): void
    {
        $columnaExiste = $this->db->query("SHOW COLUMNS FROM socios LIKE 'dni'")->getRowArray();

        if (empty($columnaExiste)) {
            $this->forge->addColumn('socios', [
                'dni' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => true,
                    'after'      => 'num_socio',
                ],
            ]);
        }

        // Completar DNI en filas existentes a partir de num_socio temporalmente
        $this->db->query("UPDATE socios SET dni = num_socio WHERE dni IS NULL OR dni = ''");

        // Dejar el campo obligatorio
        $this->forge->modifyColumn('socios', [
            'dni' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
        ]);

        $dbName = $this->db->getDatabase();
        $idx = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM information_schema.statistics
             WHERE table_schema = ?
               AND table_name = 'socios'
               AND index_name = 'socios_dni_unique'",
            [$dbName]
        )->getRowArray();

        if ((int) ($idx['total'] ?? 0) === 0) {
            $this->db->query('ALTER TABLE socios ADD UNIQUE KEY socios_dni_unique (dni)');
        }
    }

    public function down(): void
    {
        $columnaExiste = $this->db->query("SHOW COLUMNS FROM socios LIKE 'dni'")->getRowArray();

        if (empty($columnaExiste)) {
            return;
        }

        $dbName = $this->db->getDatabase();
        $idx = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM information_schema.statistics
             WHERE table_schema = ?
               AND table_name = 'socios'
               AND index_name = 'socios_dni_unique'",
            [$dbName]
        )->getRowArray();

        if ((int) ($idx['total'] ?? 0) > 0) {
            $this->db->query('ALTER TABLE socios DROP INDEX socios_dni_unique');
        }

        $this->forge->dropColumn('socios', 'dni');
    }
}
