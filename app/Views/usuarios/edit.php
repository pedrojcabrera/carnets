<!-- Vista: usuarios/edit.php  (cargada dentro de layouts/main.php) -->
<?php /** @var object $usuario */ ?>
<?php $h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="/index.php/usuarios" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h1 class="h4 mb-0 fw-bold">
        <i class="bi bi-pencil-square me-2 text-primary"></i>Editar Usuario
    </h1>
</div>

<div class="card border-0 shadow-sm" style="max-width: 760px;">
    <div class="card-body">
        <form method="post" action="/index.php/usuarios/actualizar/<?= (int) ($usuario->id ?? 0) ?>">
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
                    <label for="rol" class="form-label fw-semibold">Rol <span class="text-danger">*</span></label>
                    <select id="rol" name="rol" class="form-select" required>
                        <?php $rolActual = (string) old('rol', (string) ($usuario->rol ?? 'user')); ?>
                        <option value="user" <?= $rolActual === 'user' ? 'selected' : '' ?>>user</option>
                        <option value="admin" <?= $rolActual === 'admin' ? 'selected' : '' ?>>admin</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
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
                    <div class="form-text">Déjalo en blanco para mantener la contraseña actual.</div>
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
                    <i class="bi bi-floppy-fill me-1"></i> Actualizar
                </button>
                <a href="/index.php/usuarios" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
