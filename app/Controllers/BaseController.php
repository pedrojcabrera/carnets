<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        $path = trim((string) $request->getUri()->getPath(), '/');

        $isPwaRoute = $path === 'm'
            || str_starts_with($path, 'm/')
            || $path === 'carnet'
            || str_starts_with($path, 'carnet/')
            || str_starts_with($path, 'c/');

        // Backoffice/auth: nunca cachear HTML dinámico (evita datos viejos en navegación normal).
        if (! $isPwaRoute) {
            $response->setHeader('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0');
            $response->setHeader('Pragma', 'no-cache');
            $response->setHeader('Expires', '0');
            $response->setHeader('Surrogate-Control', 'no-store');
            $response->setHeader('Vary', 'Cookie,Authorization');
        }

        // Marca visible en cabeceras para confirmar que remoto sirve la versión actual.
        $response->setHeader('X-App-Revision', '2026-05-24-r3');

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }
}
