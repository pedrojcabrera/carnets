<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para la tabla "socios".
 * Gestiona el CRUD completo de socios incluyendo foto.
 */
class SocioModel extends Model
{
    protected $table      = 'socios';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'nombre_completo',
        'num_socio',
        'dni',
        'email',
        'tipo_socio',
        'valido_hasta',
        'url_foto',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'nombre_completo' => 'required|min_length[3]|max_length[255]',
        'num_socio'       => 'required|max_length[50]',
        'dni'             => 'required|min_length[7]|max_length[20]',
        'email'           => 'required|valid_email|max_length[255]',
        'tipo_socio'      => 'required|in_list[Socio/a,Colaborador/a]',
        'valido_hasta'    => 'required|valid_date',
    ];

    protected $validationMessages = [
        'nombre_completo' => [
            'required'   => 'El nombre completo es obligatorio.',
            'min_length' => 'El nombre debe tener al menos 3 caracteres.',
        ],
        'num_socio' => [
            'required' => 'El número de socio es obligatorio.',
        ],
        'dni' => [
            'required'   => 'El DNI es obligatorio.',
            'min_length' => 'El DNI debe tener al menos 7 caracteres.',
        ],
        'email' => [
            'required' => 'El email es obligatorio.',
            'valid_email' => 'El email no es válido.',
        ],
        'tipo_socio' => [
            'required' => 'El tipo de socio es obligatorio.',
            'in_list'  => 'El tipo de socio debe ser Socio/a o Colaborador/a.',
        ],
        'valido_hasta' => [
            'required'   => 'La fecha de validez es obligatoria.',
            'valid_date' => 'La fecha no tiene un formato válido.',
        ],
    ];
}
