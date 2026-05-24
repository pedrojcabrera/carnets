<!DOCTYPE html>
<html lang="es">
<head>
    <?php
    $view_content = isset($view_content) && is_string($view_content) ? $view_content : '';
    $h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $h($titulo ?? 'Backoffice') ?> — Carnets</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1e293b">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Carnets">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* ── Layout general ─────────────────────────────── */
        body { display: flex; flex-direction: column; min-height: 100vh; background: #f4f6f9; }

        /* Cabecera */
        .app-header {
            background: #1e293b;
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
        .app-header .user-info { margin-left: auto; font-size: .875rem; color: #94a3b8; }

        /* Contenedor principal */
        .app-body { display: flex; flex: 1; overflow: hidden; }

        /* Aside/sidebar */
        .app-sidebar {
            width: 220px;
            background: #fff;
            border-right: 1px solid #e2e8f0;
            padding: 1rem 0;
            position: sticky;
            top: 56px;
            height: calc(100vh - 56px);
            overflow-y: auto;
            flex-shrink: 0;
        }
        .app-sidebar .nav-link {
            color: #475569;
            padding: .5rem 1.25rem;
            border-radius: 0;
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .9rem;
        }
        .app-sidebar .nav-link:hover,
        .app-sidebar .nav-link.active { background: #f1f5f9; color: #1e293b; }
        .app-sidebar .nav-section {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #94a3b8;
            padding: 1rem 1.25rem .25rem;
        }

        /* Contenido principal */
        .app-content { flex: 1; padding: 1.5rem; overflow-y: auto; }

        /* Alertas */
        .alert { border-radius: .5rem; }

        /* Tablas */
        .table th { font-size: .8rem; text-transform: uppercase; letter-spacing: .05em; color: #64748b; }
        .table td { vertical-align: middle; }

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
    <a href="/dashboard" class="brand">
        <i class="bi bi-credit-card-2-front-fill me-1"></i> Carnets
    </a>
    <span class="user-info">
        <i class="bi bi-person-circle me-1"></i>
        <?= $h(session('usuario_nombre') ?? '') ?>
        <span class="badge bg-secondary ms-1"><?= $h(session('usuario_rol') ?? '') ?></span>
    </span>
    <a href="/logout" class="btn btn-sm btn-outline-light ms-3">
        <i class="bi bi-box-arrow-right"></i> Salir
    </a>
</header>

<div class="app-body">

<!-- ════════════════════════════════════════
     ASIDE LATERAL
════════════════════════════════════════ -->
<aside class="app-sidebar">
    <nav>
        <div class="nav-section">Principal</div>
        <a href="/dashboard" class="nav-link <?= (uri_string() === 'dashboard') ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="nav-section">Gestión</div>
        <a href="/socios" class="nav-link <?= str_starts_with(uri_string(), 'socios') ? 'active' : '' ?>">
            <i class="bi bi-people-fill"></i> Socios
        </a>
        <a href="/plantilla" class="nav-link <?= str_starts_with(uri_string(), 'plantilla') ? 'active' : '' ?>">
            <i class="bi bi-card-image"></i> Plantilla Carnet
        </a>

        <div class="nav-section">Ayuda</div>
        <a href="/manual" class="nav-link <?= (uri_string() === 'manual') ? 'active' : '' ?>">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Banner de actualización PWA -->
<div id="sw-update-banner" style="display:none;position:fixed;bottom:0;left:0;right:0;background:#1e293b;color:#e2e8f0;padding:.75rem 1.25rem;align-items:center;justify-content:space-between;gap:1rem;z-index:9999;border-top:2px solid #38bdf8;">
    <span><i class="bi bi-arrow-clockwise me-1"></i>Hay una nueva versión disponible.</span>
    <button id="sw-update-btn" style="background:#38bdf8;color:#0f172a;border:0;padding:.4rem 1rem;border-radius:.5rem;font-weight:700;cursor:pointer;flex-shrink:0;">Actualizar ahora</button>
</div>
<script>
    (function () {
        if (!('serviceWorker' in navigator)) return;
        var refreshing = false;
        navigator.serviceWorker.addEventListener('controllerchange', function () {
            if (!refreshing) { refreshing = true; window.location.reload(); }
        });
        navigator.serviceWorker.register('/service-worker.js').then(function (reg) {
            reg.addEventListener('updatefound', function () {
                var newWorker = reg.installing;
                if (!newWorker) return;
                newWorker.addEventListener('statechange', function () {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        var banner = document.getElementById('sw-update-banner');
                        if (banner) banner.style.display = 'flex';
                        var btn = document.getElementById('sw-update-btn');
                        if (btn) btn.addEventListener('click', function () {
                            newWorker.postMessage({ type: 'SKIP_WAITING' });
                        });
                    }
                });
            });
        }).catch(function (err) {
            console.warn('[SW] Registro fallido:', err);
        });
    }());
</script>
</body>
</html>
