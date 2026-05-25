<!DOCTYPE html>
<html lang="es">
<head>
    <?php
    $view_content = isset($view_content) && is_string($view_content) ? $view_content : '';
    $h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    $rolSesion = (string) (session('usuario_rol') ?? '');
    $esAdminMenu = $rolSesion === 'admin' || $rolSesion === 'superadmin';
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?= $h($titulo ?? 'Backoffice') ?> — Carnets</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1e293b">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Carnets">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    <!-- Bootstrap 5 (local) -->
    <link rel="stylesheet" href="<?= base_url('vendor/bootstrap/css/bootstrap.min.css') ?>">
    <!-- Bootstrap Icons (local) -->
    <link rel="stylesheet" href="<?= base_url('vendor/bootstrap-icons/css/bootstrap-icons.min.css') ?>">

    <style>
        /* ── Layout general ─────────────────────────────── */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            color: #0f172a;
            background:
                radial-gradient(1200px 600px at 100% -50%, #dbeafe 0%, transparent 60%),
                radial-gradient(900px 500px at -10% 110%, #e2e8f0 0%, transparent 60%),
                #e5e7eb;
        }

        :root {
            --brand-bg: #1e293b;
            --brand-bg-2: #0f172a;
            --brand-accent: #38bdf8;
            --brand-text: #e2e8f0;
            --brand-muted: #94a3b8;
            --surface-bg: #ffffff;
            --surface-border: #cbd5e1;
            --surface-border-strong: #94a3b8;
            --text-main: #0f172a;
            --text-soft: #334155;
            --ok-bg: #dcfce7;
            --ok-border: #16a34a;
            --ok-text: #14532d;
            --danger-bg: #fee2e2;
            --danger-border: #dc2626;
            --danger-text: #7f1d1d;
        }

        /* Cabecera */
        .app-header {
            background: linear-gradient(120deg, var(--brand-bg) 0%, var(--brand-bg-2) 100%);
            color: #fff;
            height: 56px;
            display: flex;
            align-items: center;
            padding: 0 1.25rem;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 1030;
        }
        .app-header .brand { font-weight: 700; font-size: 1.1rem; color: #fff; text-decoration: none; }
        .app-header .user-info { margin-left: auto; font-size: .875rem; color: var(--brand-text); }

        .app-header .user-menu-btn {
            border-color: rgba(226, 232, 240, .35);
            color: var(--brand-text);
            background: rgba(15, 23, 42, .35);
        }

        .app-header .user-menu-btn:hover,
        .app-header .user-menu-btn:focus {
            color: #fff;
            border-color: rgba(56, 189, 248, .6);
            background: rgba(56, 189, 248, .2);
        }

        /* Contenedor principal */
        .app-body { display: block; flex: 1; }

        /* Aside/sidebar */
        .app-sidebar {
            width: 220px;
            background: linear-gradient(180deg, var(--brand-bg) 0%, var(--brand-bg-2) 100%);
            border-right: 1px solid #1f2937;
            padding: 1rem 0;
            position: fixed;
            top: 56px;
            left: 0;
            height: calc(100vh - 56px);
            overflow-y: auto;
            flex-shrink: 0;
            z-index: 1020;
        }
        .app-sidebar .nav-link {
            color: #cbd5e1;
            padding: .5rem 1.25rem;
            border-radius: 0;
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .9rem;
        }
        .app-sidebar .nav-link:hover,
        .app-sidebar .nav-link.active {
            background: rgba(56, 189, 248, .18);
            color: #e0f2fe;
            border-left: 3px solid var(--brand-accent);
            padding-left: calc(1.25rem - 3px);
        }
        .app-sidebar .nav-link:hover {
            background: rgba(148, 163, 184, .16);
            color: #f8fafc;
        }
        .app-sidebar .nav-section {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--brand-muted);
            padding: 1rem 1.25rem .25rem;
        }

        /* Contenido principal */
        .app-content {
            flex: 1;
            padding: 1.5rem;
            margin-left: 220px;
            overflow-y: auto;
            height: calc(100vh - 56px);
            color: var(--text-main);
        }

        .app-content h1,
        .app-content h2,
        .app-content h3,
        .app-content h4,
        .app-content h5,
        .app-content h6 {
            color: var(--text-main);
        }

        .app-content .text-muted {
            color: var(--text-soft) !important;
        }

        .card {
            background: var(--surface-bg);
            border: 1px solid var(--surface-border) !important;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.07) !important;
        }

        .form-control,
        .form-select {
            color: var(--text-main);
            background-color: #ffffff;
            border-color: var(--surface-border-strong);
        }

        .form-control::placeholder {
            color: #64748b;
            opacity: 1;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .2);
        }

        .btn-outline-secondary {
            color: #334155;
            border-color: #64748b;
        }

        .btn-outline-secondary:hover,
        .btn-outline-secondary:focus {
            color: #ffffff;
            background-color: #334155;
            border-color: #334155;
        }

        .btn-outline-primary {
            color: #1d4ed8;
            border-color: #2563eb;
        }

        .btn-outline-primary:hover,
        .btn-outline-primary:focus {
            color: #ffffff;
            background-color: #2563eb;
            border-color: #1d4ed8;
        }

        .btn-outline-danger {
            color: #b91c1c;
            border-color: #dc2626;
        }

        .btn-outline-danger:hover,
        .btn-outline-danger:focus {
            color: #ffffff;
            background-color: #dc2626;
            border-color: #b91c1c;
        }

        .btn-outline-info {
            color: #0f766e;
            border-color: #0d9488;
        }

        .btn-outline-info:hover,
        .btn-outline-info:focus {
            color: #ffffff;
            background-color: #0d9488;
            border-color: #0f766e;
        }

        .btn:disabled,
        .btn.disabled {
            opacity: .8;
            color: #64748b !important;
            border-color: #94a3b8 !important;
            background-color: #e2e8f0 !important;
        }

        /* Alertas */
        .alert {
            border-radius: .5rem;
            border-width: 1px;
            border-style: solid;
        }

        .alert-success {
            color: var(--ok-text);
            background: var(--ok-bg);
            border-color: var(--ok-border);
        }

        .alert-danger {
            color: var(--danger-text);
            background: var(--danger-bg);
            border-color: var(--danger-border);
        }

        .badge.text-bg-light {
            color: #0f172a !important;
            background-color: #e2e8f0 !important;
            border-color: #94a3b8 !important;
        }

        /* Tablas */
        .table {
            --bs-table-bg: #ffffff;
            --bs-table-striped-bg: #f8fafc;
            --bs-table-hover-bg: #eef2ff;
            --bs-table-border-color: #cbd5e1;
        }

        .table thead th {
            background: #e2e8f0;
            color: #0f172a;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: 1px solid #94a3b8;
        }

        .table td {
            vertical-align: middle;
            color: #1e293b;
        }

        .dropdown-menu {
            border: 1px solid var(--surface-border);
            box-shadow: 0 12px 24px rgba(15, 23, 42, .12);
        }

        .dropdown-item {
            color: #0f172a;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background: #e2e8f0;
            color: #0f172a;
        }

        /* Foto socio en tabla */
        .foto-socio {
            width: 40px; height: 40px;
            object-fit: cover;
            /* border-radius: 50%; */
        }

        /* Preview de foto en formularios de socio */
        .socio-foto-preview-frame {
            width: 140px;
            aspect-ratio: 3 / 4;
            border: 1px solid #cbd5e1;
            border-radius: .5rem;
            overflow: hidden;
            background: #f8fafc;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .socio-foto-preview-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
    </style>
 </head>
<body>
<!-- ════════════════════════════════════════
     CABECERA
════════════════════════════════════════ -->
<header class="app-header">
    <a href="/index.php/dashboard" class="brand">
        <i class="bi bi-credit-card-2-front-fill me-1"></i> Carnets
    </a>
    <span class="user-info">
        <i class="bi bi-person-circle me-1"></i>
        <?= $h(session('usuario_nombre') ?? '') ?>
    </span>
    <div class="dropdown ms-3">
        <button
            class="btn btn-sm user-menu-btn dropdown-toggle"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
        >
            <i class="bi bi-person-lines-fill me-1"></i> Cuenta
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li>
                <a class="dropdown-item" href="/index.php/perfil">
                    <i class="bi bi-person-circle me-2"></i> Mi perfil
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item text-danger" href="/index.php/logout">
                    <i class="bi bi-box-arrow-right me-2"></i> Salir
                </a>
            </li>
        </ul>
    </div>
</header>

<div class="app-body">

<!-- ════════════════════════════════════════
     ASIDE LATERAL
════════════════════════════════════════ -->
<aside class="app-sidebar">
    <nav>
        <div class="nav-section">Principal</div>
        <a href="/index.php/dashboard" class="nav-link <?= (uri_string() === 'dashboard') ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="nav-section">Gestión</div>
        <a href="/index.php/socios" class="nav-link <?= str_starts_with(uri_string(), 'socios') ? 'active' : '' ?>">
            <i class="bi bi-people-fill"></i> Socios
        </a>
        <a href="/index.php/plantilla" class="nav-link <?= str_starts_with(uri_string(), 'plantilla') ? 'active' : '' ?>">
            <i class="bi bi-card-image"></i> Plantilla Carnet
        </a>
        <?php if ($esAdminMenu): ?>
            <a href="/index.php/usuarios" class="nav-link <?= str_starts_with(uri_string(), 'usuarios') ? 'active' : '' ?>">
                <i class="bi bi-person-gear"></i> Usuarios
            </a>
        <?php endif; ?>

        <div class="nav-section">Ayuda</div>
        <a href="/index.php/manual" class="nav-link <?= (uri_string() === 'manual') ? 'active' : '' ?>">
            <i class="bi bi-book-half"></i> Manual de uso
        </a>
    </nav>
</aside>

<!-- ════════════════════════════════════════
     CONTENIDO PRINCIPAL
════════════════════════════════════════ -->
<main class="app-content">

    <!-- Mensajes flash -->
    <?php if (session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> <?= $h(session('success') ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= $h(session('error') ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->has('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <ul class="mb-0 mt-1">
                <?php foreach ((array) (session('errors') ?? []) as $err): ?>
                    <li><?= $h($err) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Vista parcial del módulo activo -->
    <?= view($view_content, get_defined_vars()) ?>

</main>
</div><!-- /app-body -->

<script src="<?= base_url('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>

<script>
    // El backoffice no registra service worker para evitar
    // interferencias con la navegacion y peticiones extra al servidor.
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(function (regs) {
            regs.forEach(function (reg) { reg.unregister(); });
        });
    }
</script>
</body>
</html>
