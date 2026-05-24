<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailToSociosTable extends Migration
{
    public function up(): void
    {
        $columnaExiste = $this->db->query("SHOW COLUMNS FROM socios LIKE 'email'")->getRowArray();

        if (! is_array($columnaExiste)) {
            $this->forge->addColumn('socios', [
                'email' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'dni',
                ],
            ]);

            $this->forge->addUniqueKey('email');
            $this->forge->processIndexes('socios');
        }
    }

    public function down(): void
    {
        $columnaExiste = $this->db->query("SHOW COLUMNS FROM socios LIKE 'email'")->getRowArray();

        if (is_array($columnaExiste)) {
            $this->forge->dropColumn('socios', 'email');
        }
    }
}
