<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ─────────────────────────────────────────
//  RUTAS PÚBLICAS
// ─────────────────────────────────────────

// Ruta raíz directa para minimizar trabajo del router.
$routes->get('/', 'Auth::index');

// Autenticación
$routes->get('login',  'Auth::index');
$routes->post('login', 'Auth::doLogin');
$routes->get('logout', 'Auth::logout');

// PWA – Carnet digital (público)
$routes->get('m', 'Carnet::inicio');
$routes->post('m/abrir', 'Carnet::abrir');
$routes->get('c/(:segment)', 'Carnet::verPorDni/$1');
// Compatibilidad con URLs antiguas.
$routes->get('carnet', static function () {
    return redirect()->to('/index.php/m', 302);
});
$routes->post('carnet/abrir', 'Carnet::abrir');
$routes->get('carnet/ver/(:segment)', static function (string $dni) {
    return redirect()->to('/index.php/c/' . rawurlencode($dni), 302);
});
$routes->get('carnet/dni/(:segment)', static function (string $dni) {
    return redirect()->to('/index.php/c/' . rawurlencode($dni), 302);
});
$routes->get('carnet/preferencias/(:segment)', 'Carnet::preferencias/$1');
$routes->post('carnet/preferencias/(:segment)', 'Carnet::guardarPreferencias/$1');

// ─────────────────────────────────────────
//  RUTAS PROTEGIDAS (backoffice)
// ─────────────────────────────────────────

$routes->group('', ['filter' => 'auth'], static function ($routes) {

    // Dashboard
    $routes->get('dashboard', 'Dashboard::index');

    // Perfil de usuario (todos los usuarios autenticados)
    $routes->get ('perfil',          'Usuarios::perfil');
    $routes->post('perfil/actualizar', 'Usuarios::actualizarPerfil');

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

    // Usuarios – CRUD (solo admin y superadmin)
    $routes->get ('usuarios',                    'Usuarios::index');
    $routes->get ('usuarios/crear',              'Usuarios::crear');
    $routes->post('usuarios/guardar',            'Usuarios::guardar');
    $routes->get ('usuarios/editar/(:num)',      'Usuarios::editar/$1');
    $routes->post('usuarios/actualizar/(:num)',  'Usuarios::actualizar/$1');
    $routes->get ('usuarios/eliminar/(:num)',    'Usuarios::eliminar/$1');

    // Manual de uso
    $routes->get('manual', 'Manual::index');
});
