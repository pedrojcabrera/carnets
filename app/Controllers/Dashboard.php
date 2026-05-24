<?php

namespace App\Controllers;

use App\Models\SocioModel;

/**
 * Controlador del dashboard principal del backoffice.
 */
class Dashboard extends BaseController
{
    public function index(): string
    {
        $socioModel = new SocioModel();

        $data = [
            'titulo'       => 'Dashboard',
            'total_socios' => $socioModel->countAll(),
        ];

        return view('layouts/main', $data + ['view_content' => 'dashboard/index']);
    }
}
