<!-- Vista: usuarios/index.php  (cargada dentro de layouts/main.php) -->
<?php /** @var list<object> $usuarios */ ?>
<?php
$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$q = isset($q) && is_string($q) ? $q : '';
$sort = isset($sort) && is_string($sort) ? $sort : 'nombre';
$dir = isset($dir) && strtolower((string) $dir) === 'desc' ? 'desc' : 'asc';
$rolActual = (string) (session('usuario_rol') ?? '');
$idActual = (int) (session('usuario_id') ?? 0);

$sortUrl = static function (string $column) use ($q, $sort, $dir): string {
    $nextDir = ($sort === $column && $dir === 'asc') ? 'desc' : 'asc';

    return '/index.php/usuarios?' . http_build_query([
        'q' => $q,
        'sort' => $column,
        'dir' => $nextDir,
    ]);
};

$sortIcon = static function (string $column) use ($sort, $dir): string {
    if ($sort !== $column) {
        return '<i class="bi bi-arrow-down-up ms-1 text-muted"></i>';
    }

    return $dir === 'asc'
        ? '<i class="bi bi-caret-up-fill ms-1 text-primary"></i>'
        : '<i class="bi bi-caret-down-fill ms-1 text-primary"></i>';
};

$puedeEliminar = static function (object $usuario) use ($rolActual, $idActual): bool {
    $idObjetivo = (int) ($usuario->id ?? 0);
    $rolObjetivo = (string) ($usuario->rol ?? '');

    if ($idObjetivo === $idActual) {
        return false;
    }

    if ($rolActual === 'superadmin') {
        return true;
    }

    if ($rolActual === 'admin') {
        return $rolObjetivo === 'user';
    }

    return false;
};
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h4 mb-0 fw-bold">
        <i class="bi bi-person-gear me-2 text-primary"></i>Usuarios
    </h1>
    <a href="/index.php/usuarios/crear" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus-fill me-1"></i> Nuevo usuario
    </a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="get" action="/index.php/usuarios" class="row g-2 align-items-end">
            <div class="col-12 col-lg-8">
                <label for="q" class="form-label mb-1 small text-uppercase fw-bold text-muted">Búsqueda general</label>
                <input
                    type="search"
                    id="q"
                    name="q"
                    class="form-control"
                    value="<?= $h($q) ?>"
                    placeholder="Usuario, nombre, email o rol..."
                >
            </div>
            <input type="hidden" name="sort" value="<?= $h($sort) ?>">
            <input type="hidden" name="dir" value="<?= $h($dir) ?>">
            <div class="col-12 col-lg-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i> Buscar
                </button>
                <a href="/index.php/usuarios" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th><a href="<?= $sortUrl('id') ?>" class="text-decoration-none text-reset">#<?= $sortIcon('id') ?></a></th>
                        <th><a href="<?= $sortUrl('usuario') ?>" class="text-decoration-none text-reset">Usuario<?= $sortIcon('usuario') ?></a></th>
                        <th><a href="<?= $sortUrl('nombre') ?>" class="text-decoration-none text-reset">Nombre<?= $sortIcon('nombre') ?></a></th>
                        <th><a href="<?= $sortUrl('email') ?>" class="text-decoration-none text-reset">Email<?= $sortIcon('email') ?></a></th>
                        <th><a href="<?= $sortUrl('rol') ?>" class="text-decoration-none text-reset">Rol<?= $sortIcon('rol') ?></a></th>
                        <th><a href="<?= $sortUrl('created_at') ?>" class="text-decoration-none text-reset">Alta<?= $sortIcon('created_at') ?></a></th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                No hay usuarios para mostrar.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td class="text-muted small"><?= $h($usuario->id) ?></td>
                                <td><code><?= $h($usuario->usuario ?? '') ?></code></td>
                                <td><?= $h($usuario->nombre ?? '') ?></td>
                                <td><?= $h($usuario->email ?? '') ?></td>
                                <td><span class="badge text-bg-light border"><?= $h($usuario->rol ?? '') ?></span></td>
                                <td class="text-muted small"><?= ! empty($usuario->created_at) ? $h(date('d/m/Y', strtotime((string) $usuario->created_at))) : '-' ?></td>
                                <td class="text-end text-nowrap">
                                    <a href="/index.php/usuarios/editar/<?= (int) ($usuario->id ?? 0) ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($puedeEliminar($usuario)): ?>
                                        <a href="/index.php/usuarios/eliminar/<?= (int) ($usuario->id ?? 0) ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           title="Eliminar"
                                           onclick="return confirm('¿Eliminar al usuario <?= $h($usuario->nombre ?? '') ?>?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary" type="button" disabled title="No permitido">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
