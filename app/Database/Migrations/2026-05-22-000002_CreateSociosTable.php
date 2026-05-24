<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migración: crea la tabla "socios" si todavía no existe.
 */
class CreateSociosTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nombre_completo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'num_socio' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'dni' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'tipo_socio' => [
                'type'       => 'ENUM',
                'constraint' => ['Socio/a', 'Colaborador/a'],
                'default'    => 'Socio/a',
            ],
            'valido_hasta' => [
                'type' => 'DATE',
            ],
            'url_foto' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('dni');
        $this->forge->addUniqueKey('num_socio');
        $this->forge->createTable('socios', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('socios', true);
    }
}
