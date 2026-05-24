# CodeIgniter 4 Application Starter

## What is CodeIgniter?

CodeIgniter is a PHP full-stack web framework that is light, fast, flexible and secure.
More information can be found at the [official site](https://codeigniter.com).

This repository holds a composer-installable app starter.
It has been built from the
[development repository](https://github.com/codeigniter4/CodeIgniter4).

More information about the plans for version 4 can be found in [CodeIgniter 4](https://forum.codeigniter.com/forumdisplay.php?fid=28) on the forums.

You can read the [user guide](https://codeigniter.com/user_guide/)
corresponding to the latest version of the framework.

## Installation & updates

`composer create-project codeigniter4/appstarter` then `composer update` whenever
there is a new release of the framework.

When updating, check the release notes to see if there are any changes you might need to apply
to your `app` folder. The affected files can be copied or merged from
`vendor/codeigniter4/framework/app`.

## Setup

Copy `env` to `.env` and tailor for your app, specifically the baseURL
and any database settings.

## Manual rápido del proyecto

### 1. Qué hace esta parte

El sistema tiene dos zonas principales:

- Backoffice privado: para administrar socios y configurar la plantilla del carnet.
- Carnet público: para consultar el carnet digital desde una URL pública.

#### Backoffice

Se entra por `/login` y, una vez autenticado, se accede a:

- `/dashboard`: resumen general.
- `/socios`: listado, alta, edición y eliminación de socios.
- `/plantilla`: configuración visual del carnet.

En la gestión de socios se guardan datos como nombre completo, número de socio, DNI, tipo de socio, fecha de validez y foto. Las fotos se almacenan en `public/uploads/socios`.

#### Plantilla del carnet

La pantalla de plantilla permite elegir la imagen de fondo del carnet y ajustar la posición de cada elemento superpuesto:

- Foto
- Nombre completo
- Tipo de socio
- Número de socio
- Fecha de validez

Los cambios se guardan en la base de datos como una plantilla activa. Esa configuración se reutiliza después al mostrar cualquier carnet público.

### 2. Cómo se genera el carnet público

El carnet se muestra con estas rutas:

- `/carnet/ver/{id_o_dni}`
- `/carnet/dni/{dni}`

El controlador busca al socio por ID numérico o por DNI, carga la plantilla activa y aplica las posiciones guardadas. Si no hay plantilla configurada, usa valores por defecto.

La vista pública del carnet está pensada para móvil y muestra:

- fondo del carnet
- foto del socio
- nombre
- tipo de socio
- número de socio
- fecha de validez

### 3. Parte PWA

La parte PWA está pensada para que el carnet funcione como una app instalable y con soporte básico sin conexión.

#### Manifest

El archivo `public/manifest.json` define:

- nombre de la app
- nombre corto
- pantalla de inicio
- modo `standalone`
- orientación vertical
- iconos de 192 y 512 px

#### Service Worker

El archivo `public/service-worker.js` maneja la caché con dos estrategias:

- `Cache First` para recursos estáticos como fondo, iconos y manifiesto.
- `Network First` para la ruta del carnet, para intentar mostrar datos actualizados primero.

Eso significa que:

- si hay conexión, el carnet intenta cargar la versión más reciente;
- si no hay conexión, puede mostrar lo último que quedó en caché;
- los recursos visuales principales se precargan al instalar la PWA.

#### Registro en el navegador

La vista pública del carnet registra automáticamente el service worker en el navegador. Por eso, al abrir el carnet desde un móvil compatible, el usuario puede instalar la app desde el navegador.

### 4. Flujo resumido

1. El administrador crea o edita socios.
2. El administrador ajusta la plantilla desde `/plantilla`.
3. El usuario abre el carnet público por ID o DNI.
4. La vista carga el fondo, la foto y los textos con la configuración activa.
5. El service worker permite instalar la app y reutilizar recursos si hay poca o ninguna conexión.

## Important Change with index.php

`index.php` is no longer in the root of the project! It has been moved inside the *public* folder,
for better security and separation of components.

This means that you should configure your web server to "point" to your project's *public* folder, and
not to the project root. A better practice would be to configure a virtual host to point there. A poor practice would be to point your web server to the project root and expect to enter *public/...*, as the rest of your logic and the
framework are exposed.

**Please** read the user guide for a better explanation of how CI4 works!

## Repository Management

We use GitHub issues, in our main repository, to track **BUGS** and to track approved **DEVELOPMENT** work packages.
We use our [forum](http://forum.codeigniter.com) to provide SUPPORT and to discuss
FEATURE REQUESTS.

This repository is a "distribution" one, built by our release preparation script.
Problems with it can be raised on our forum, or as issues in the main repository.

## Server Requirements

PHP version 8.2 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

> [!WARNING]
> - The end of life date for PHP 7.4 was November 28, 2022.
> - The end of life date for PHP 8.0 was November 26, 2023.
> - The end of life date for PHP 8.1 was December 31, 2025.
> - If you are still using below PHP 8.2, you should upgrade immediately.
> - The end of life date for PHP 8.2 will be December 31, 2026.

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) if you plan to use MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library
