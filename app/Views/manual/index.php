<?php
$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h4 mb-0 fw-bold">
        <i class="bi bi-book-half me-2 text-primary"></i>Manual de uso
    </h1>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3">Resumen general</h2>
                <p class="mb-2">Este sistema tiene dos partes:</p>
                <ul class="mb-0">
                    <li>Backoffice privado para administrar socios y la plantilla del carnet.</li>
                    <li>Carnet público para consultar el carnet digital desde una URL.</li>
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3">Flujo de trabajo</h2>
                <ol class="mb-0">
                    <li>Crear o editar socios desde la sección <strong>Socios</strong>.</li>
                    <li>Ajustar el fondo y las posiciones desde <strong>Plantilla Carnet</strong>.</li>
                    <li>Abrir el carnet público por ID o DNI.</li>
                    <li>El sistema aplica la plantilla activa y muestra los datos del socio.</li>
                </ol>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3">Parte PWA</h2>
                <p class="mb-2">La vista pública del carnet registra un service worker y usa el manifiesto PWA para permitir instalación en móvil.</p>
                <ul class="mb-0">
                    <li><strong>Manifest:</strong> define nombre, iconos, orientación y modo standalone.</li>
                    <li><strong>Service worker:</strong> guarda recursos estáticos en caché y prioriza red para el carnet.</li>
                    <li><strong>Uso sin conexión:</strong> si ya se visitó el carnet, puede mostrar contenido desde la caché.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3">Rutas útiles</h2>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><span class="text-muted">/dashboard</span> - resumen general.</li>
                    <li class="mb-2"><span class="text-muted">/socios</span> - gestión de socios.</li>
                    <li class="mb-2"><span class="text-muted">/plantilla</span> - diseño del carnet.</li>
                    <li class="mb-0"><span class="text-muted">/carnet/ver/{id}</span> - carnet público.</li>
                </ul>
            </div>
        </div>
    </div>
</div>