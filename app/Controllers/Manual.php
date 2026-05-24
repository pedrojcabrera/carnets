<?php

namespace App\Controllers;

/**
 * Muestra el manual de uso dentro del backoffice.
 */
class Manual extends BaseController
{
    public function index(): string
    {
        return view('layouts/main', [
            'titulo'       => 'Manual de uso',
            'view_content' => 'manual/index',
        ]);
    }
}