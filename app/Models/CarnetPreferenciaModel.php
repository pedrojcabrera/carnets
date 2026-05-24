<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para preferencias visuales del carnet por socio.
 */
class CarnetPreferenciaModel extends Model
{
    protected $table      = 'carnet_preferencias';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'socio_id',
        'config_tipografia',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getBySocioId(int $socioId): ?object
    {
        return $this->where('socio_id', $socioId)->first();
    }
}
