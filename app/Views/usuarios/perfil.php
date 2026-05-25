<!-- Vista: usuarios/perfil.php  (cargada dentro de layouts/main.php) -->
<?php /** @var object $usuario */ ?>
<?php $h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h4 mb-0 fw-bold">
        <i class="bi bi-person-circle me-2 text-primary"></i>Mi Perfil
    </h1>
</div>

<div class="card border-0 shadow-sm" style="max-width: 760px;">
    <div class="card-body">
        <form method="post" action="/index.php/perfil/actualizar">
            <?= csrf_field() ?>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label for="usuario" class="form-label fw-semibold">Usuario <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="usuario"
                        name="usuario"
                        class="form-control"
                        value="<?= $h((string) ($usuario->usuario ?? '')) ?>"
                        disabled
                    >
                    <div class="form-text">El nombre de usuario no se puede modificar.</div>
                </div>
                <div class="col-md-4">
                    <label for="nombre" class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        class="form-control"
                        value="<?= $h(old('nombre', (string) ($usuario->nombre ?? ''))) ?>"
                        required
                    >
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Rol</label>
                    <input
                        type="text"
                        class="form-control"
                        value="<?= $h((string) ($usuario->rol ?? '')) ?>"
                        disabled
                    >
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        value="<?= $h(old('email', (string) ($usuario->email ?? ''))) ?>"
                        required
                    >
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="password" class="form-label fw-semibold">Nueva contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                    >
                    <div class="form-text">Mínimo 6 caracteres. Déjalo en blanco para mantener la actual.</div>
                </div>
                <div class="col-md-6">
                    <label for="password_confirm" class="form-label fw-semibold">Confirmar nueva contraseña</label>
                    <input
                        type="password"
                        id="password_confirm"
                        name="password_confirm"
                        class="form-control"
                    >
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy-fill me-1"></i> Guardar cambios
                </button>
                <a href="/index.php/dashboard" class="btn btn-outline-secondary">Volver</a>
            </div>
        </form>
    </div>
</div>
