<!-- Vista: socios/edit.php  (cargada dentro de layouts/main.php) -->
<?php /** @var object $socio */ ?>
<?php $h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="/socios" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h1 class="h4 mb-0 fw-bold">
        <i class="bi bi-pencil-square me-2 text-primary"></i>Editar Socio
    </h1>
</div>

<div class="card border-0 shadow-sm" style="max-width: 680px;">
    <div class="card-body">
        <form method="post" action="/socios/actualizar/<?= $h($socio->id) ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <!-- Nombre completo -->
            <div class="mb-3">
                <label for="nombre_completo" class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                <input
                    type="text"
                    id="nombre_completo"
                    name="nombre_completo"
                    class="form-control"
                    value="<?= $h(old('nombre_completo', (string) $socio->nombre_completo)) ?>"
                    required
                    autofocus
                >
            </div>

            <!-- Número de socio + DNI + Email (misma línea, alineados) -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label for="num_socio" class="form-label fw-semibold">Nº Socio <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="num_socio"
                        name="num_socio"
                        class="form-control"
                        value="<?= $h(old('num_socio', (string) $socio->num_socio)) ?>"
                        required
                    >
                </div>
                <div class="col-md-4">
                    <label for="dni" class="form-label fw-semibold">DNI <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="dni"
                        name="dni"
                        class="form-control"
                        value="<?= $h(old('dni', (string) ($socio->dni ?? ''))) ?>"
                        required
                    >
                </div>
                <div class="col-md-4">
                    <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        value="<?= $h(old('email', (string) ($socio->email ?? ''))) ?>"
                        required
                    >
                </div>
            </div>

            <!-- Tipo + Válido hasta (misma línea, alineados) -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="tipo_socio" class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                    <select id="tipo_socio" name="tipo_socio" class="form-select" required>
                        <?php $tipoActual = (string) old('tipo_socio', (string) ($socio->tipo_socio ?? 'Socio/a')); ?>
                        <option value="Socio/a" <?= $tipoActual === 'Socio/a' ? 'selected' : '' ?>>Socio/a</option>
                        <option value="Colaborador/a" <?= $tipoActual === 'Colaborador/a' ? 'selected' : '' ?>>Colaborador/a</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="valido_hasta" class="form-label fw-semibold">Válido hasta <span class="text-danger">*</span></label>
                    <input
                        type="date"
                        id="valido_hasta"
                        name="valido_hasta"
                        class="form-control"
                        value="<?= $h(old('valido_hasta', (string) $socio->valido_hasta)) ?>"
                        required
                    >
                </div>
            </div>

            <!-- Foto actual + nueva -->
            <div class="mb-4">
                <label class="form-label fw-semibold">Foto del socio</label>

                <div class="mt-2">
                    <div id="foto-preview-frame" class="socio-foto-preview-frame<?= empty($socio->url_foto) ? ' d-none' : '' ?>">
                        <img
                            id="foto-preview-img"
                            src="<?= ! empty($socio->url_foto) ? $h($socio->url_foto) : '' ?>"
                            data-default-src="<?= ! empty($socio->url_foto) ? $h($socio->url_foto) : '' ?>"
                            class="socio-foto-preview-img<?= empty($socio->url_foto) ? ' d-none' : '' ?>"
                            alt="Foto del socio"
                        >
                    </div>
                    <div id="foto-preview-status" class="form-text mt-2">
                        <?= empty($socio->url_foto) ? 'Aun no hay foto seleccionada.' : 'Foto actual.' ?>
                    </div>
                </div>

                <input
                    type="file"
                    id="foto"
                    name="foto"
                    class="form-control"
                    accept="image/jpeg,image/png,image/webp"
                >
                <div class="form-text">Dejar vacío para mantener la foto actual. JPG, PNG o WebP · Máx. 2 MB</div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy-fill me-1"></i> Actualizar
                </button>
                <a href="/socios" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script src="/js/socio-foto-preview.js"></script>
