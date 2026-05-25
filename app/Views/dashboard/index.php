<!-- Vista: dashboard/index.php  (cargada dentro de layouts/main.php) -->
<?php /** @var int|string $total_socios */ ?>
<?php $h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h4 mb-0 fw-bold">
        <i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard
    </h1>
</div>

<div class="row g-3 mb-4">
    <!-- Tarjeta: total socios -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-people-fill fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="text-muted small">Total socios</div>
                    <div class="fs-3 fw-bold"><?= $h($total_socios) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-title fw-semibold mb-3">Accesos rápidos</h6>
                <div class="d-flex flex-wrap gap-2">
                    <a href="/index.php/socios/crear" class="btn btn-sm btn-primary">
                        <i class="bi bi-person-plus-fill me-1"></i> Nuevo socio
                    </a>
                    <a href="/index.php/socios" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-list me-1"></i> Ver socios
                    </a>
                    <a href="/index.php/plantilla" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-card-image me-1"></i> Configurar plantilla
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
