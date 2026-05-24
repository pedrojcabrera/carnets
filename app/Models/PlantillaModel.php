<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para la tabla "plantillas".
 * Gestiona la imagen de fondo del carnet y las posiciones
 * de los elementos que se superponen (foto, nombre, etc.).
 */
class PlantillaModel extends Model
{
    protected $table      = 'plantillas';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'url_fondo',
        'config_posiciones',
        'activa',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Devuelve la plantilla activa o null si no hay ninguna.
     */
    public function getActiva(): ?object
    {
        return $this->where('activa', 1)->first();
    }
}
