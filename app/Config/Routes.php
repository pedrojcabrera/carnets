<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ─────────────────────────────────────────
//  RUTAS PÚBLICAS
// ─────────────────────────────────────────

// Redirigir raíz según dispositivo: escritorio a login, móvil al acceso público del carnet
$routes->get('/', static function () {
    $agent = service('request')->getUserAgent();

    if ($agent !== null && $agent->isMobile()) {
        return redirect()->to('/m');
    }

    return redirect()->to('/login');
});

// Autenticación
$routes->get('login',  'Auth::index');
$routes->post('login', 'Auth::doLogin');
$routes->get('logout', 'Auth::logout');

// PWA – Carnet digital (público)
$routes->get('m', 'Carnet::inicio');
$routes->post('m/abrir', 'Carnet::abrir');
$routes->get('c/(:segment)', 'Carnet::verPorDni/$1');
$routes->get('carnet', 'Carnet::inicio');
$routes->post('carnet/abrir', 'Carnet::abrir');
$routes->get('carnet/ver/(:segment)', 'Carnet::ver/$1');
$routes->get('carnet/dni/(:segment)', 'Carnet::verPorDni/$1');
$routes->get('carnet/preferencias/(:segment)', 'Carnet::preferencias/$1');
$routes->post('carnet/preferencias/(:segment)', 'Carnet::guardarPreferencias/$1');

// ─────────────────────────────────────────
//  RUTAS PROTEGIDAS (backoffice)
// ─────────────────────────────────────────

$routes->group('', ['filter' => 'auth'], static function ($routes) {

    // Dashboard
    $routes->get('dashboard', 'Dashboard::index');

    // Socios – CRUD completo
    $routes->get ('socios',                   'Socios::index');
    $routes->get ('socios/crear',             'Socios::crear');
    $routes->post('socios/guardar',           'Socios::guardar');
    $routes->get ('socios/editar/(:num)',      'Socios::editar/$1');
    $routes->post('socios/actualizar/(:num)', 'Socios::actualizar/$1');
    $routes->get ('socios/eliminar/(:num)',    'Socios::eliminar/$1');

    // Plantilla del carnet
    $routes->get ('plantilla',         'Plantilla::index');
    $routes->post('plantilla/guardar', 'Plantilla::guardar');

    // Manual de uso
    $routes->get('manual', 'Manual::index');
});
