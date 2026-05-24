<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Añade el campo tipo_socio en la tabla socios para instalaciones existentes.
 */
class AddTipoSocioToSociosTable extends Migration
{
    public function up(): void
    {
        $columnaExiste = $this->db->query("SHOW COLUMNS FROM socios LIKE 'tipo_socio'")->getRowArray();

        if (empty($columnaExiste)) {
            $this->forge->addColumn('socios', [
                'tipo_socio' => [
                    'type'       => 'ENUM',
                    'constraint' => ['Socio/a', 'Colaborador/a'],
                    'default'    => 'Socio/a',
                    'after'      => 'num_socio',
                ],
            ]);
        }

        // Normalizar valores nulos o vacíos
        $this->db->query("UPDATE socios SET tipo_socio = 'Socio/a' WHERE tipo_socio IS NULL OR tipo_socio = ''");
    }

    public function down(): void
    {
        $columnaExiste = $this->db->query("SHOW COLUMNS FROM socios LIKE 'tipo_socio'")->getRowArray();

        if (! empty($columnaExiste)) {
            $this->forge->dropColumn('socios', 'tipo_socio');
        }
    }
}
