<!-- Vista: socios/index.php  (cargada dentro de layouts/main.php) -->
<?php /** @var list<object> $socios */ ?>
<?php
$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$q = isset($q) && is_string($q) ? $q : '';
$sort = isset($sort) && is_string($sort) ? $sort : 'nombre_completo';
$dir = isset($dir) && strtolower((string) $dir) === 'desc' ? 'desc' : 'asc';

$sortUrl = static function (string $column) use ($q, $sort, $dir): string {
    $nextDir = ($sort === $column && $dir === 'asc') ? 'desc' : 'asc';

    return '/index.php/socios?' . http_build_query([
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
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h4 mb-0 fw-bold">
        <i class="bi bi-people-fill me-2 text-primary"></i>Socios
    </h1>
    <a href="/index.php/socios/crear" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus-fill me-1"></i> Nuevo socio
    </a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="get" action="/index.php/socios" class="row g-2 align-items-end">
            <div class="col-12 col-lg-8">
                <label for="q" class="form-label mb-1 small text-uppercase fw-bold text-muted">Búsqueda general</label>
                <input
                    type="search"
                    id="q"
                    name="q"
                    class="form-control"
                    value="<?= $h($q) ?>"
                    placeholder="Nombre, DNI, email, número, tipo o fecha..."
                >
            </div>
            <input type="hidden" name="sort" value="<?= $h($sort) ?>">
            <input type="hidden" name="dir" value="<?= $h($dir) ?>">
            <div class="col-12 col-lg-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i> Buscar
                </button>
                <a href="/index.php/socios" class="btn btn-outline-secondary">
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
                        <th>Foto</th>
                        <th><a href="<?= $sortUrl('nombre_completo') ?>" class="text-decoration-none text-reset">Nombre completo<?= $sortIcon('nombre_completo') ?></a></th>
                        <th><a href="<?= $sortUrl('tipo_socio') ?>" class="text-decoration-none text-reset">Tipo<?= $sortIcon('tipo_socio') ?></a></th>
                        <th><a href="<?= $sortUrl('dni') ?>" class="text-decoration-none text-reset">DNI<?= $sortIcon('dni') ?></a></th>
                        <th><a href="<?= $sortUrl('email') ?>" class="text-decoration-none text-reset">Email<?= $sortIcon('email') ?></a></th>
                        <th><a href="<?= $sortUrl('num_socio') ?>" class="text-decoration-none text-reset">Nº Socio<?= $sortIcon('num_socio') ?></a></th>
                        <th><a href="<?= $sortUrl('valido_hasta') ?>" class="text-decoration-none text-reset">Válido hasta<?= $sortIcon('valido_hasta') ?></a></th>
                        <th><a href="<?= $sortUrl('created_at') ?>" class="text-decoration-none text-reset">Alta<?= $sortIcon('created_at') ?></a></th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($socios)): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                No hay socios registrados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($socios as $socio): ?>
                            <?php
                            $esIncompletoOSinFoto = empty($socio->carnet_disponible) || empty($socio->url_foto);
                            $numSocioRaw = trim((string) ($socio->num_socio ?? ''));
                            $numSocioVista = $numSocioRaw === ''
                                ? ''
                                : (is_numeric($numSocioRaw)
                                    ? str_pad((string) ((int) $numSocioRaw), 3, '0', STR_PAD_LEFT)
                                    : str_pad($numSocioRaw, 3, '0', STR_PAD_LEFT));
                            ?>
                            <tr>
                                <td class="small<?= $esIncompletoOSinFoto ? ' text-danger fw-semibold' : ' text-muted' ?>"><?= $h($socio->id) ?></td>
                                <td>
                                    <?php if (! empty($socio->url_foto)): ?>
                                        <img
                                            src="<?= $h($socio->url_foto) ?>"
                                            alt="Foto"
                                            class="foto-socio"
                                        >
                                    <?php else: ?>
                                        <span class="foto-socio d-inline-flex align-items-center justify-content-center bg-light text-secondary">
                                            <i class="bi bi-person-fill"></i>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="<?= $esIncompletoOSinFoto ? 'text-muted fw-semibold' : '' ?>"><?= $h($socio->nombre_completo) ?></td>
                                <td><span class="badge text-bg-light border"><?= $h($socio->tipo_socio ?? 'Socio/a') ?></span></td>
                                <td><code><?= $h($socio->dni ?? '') ?></code></td>
                                <td><?= $h($socio->email ?? '') ?></td>
                                <td><code class="text-primary fw-semibold"><?= $h($numSocioVista) ?></code></td>
                                <td>
                                    <?= ! empty($socio->valido_hasta) ? $h(date('d-m-Y', strtotime((string) $socio->valido_hasta))) : '' ?>
                                </td>
                                <td class="text-muted small"><?= $h(date('d/m/Y', strtotime((string) $socio->created_at))) ?></td>
                                <td class="text-end text-nowrap">
                                    <!-- Ver carnet -->
                                    <?php if (! empty($socio->carnet_disponible)): ?>
                                        <a href="/index.php/carnet/ver/<?= $socio->id ?>"
                                           class="btn btn-sm btn-outline-info"
                                           target="_blank"
                                           title="Ver carnet">
                                            <i class="bi bi-credit-card"></i>
                                        </a>
                                    <?php else: ?>
                                        <?php $faltantes = isset($socio->carnet_faltantes) && is_array($socio->carnet_faltantes) ? $socio->carnet_faltantes : []; ?>
                                        <?php $msgFaltantes = empty($faltantes) ? 'Faltan datos en su ficha de socio.' : 'Faltan: ' . implode(', ', $faltantes) . '.'; ?>
                                        <span
                                            class="btn btn-sm btn-outline-secondary disabled"
                                            tabindex="-1"
                                            aria-disabled="true"
                                            title="<?= $h($msgFaltantes) ?>"
                                        >
                                            <i class="bi bi-credit-card"></i>
                                        </span>
                                    <?php endif; ?>
                                    <!-- Editar -->
                                                <a href="/index.php/socios/editar/<?= $socio->id ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <!-- Eliminar -->
                                                <a href="/index.php/socios/eliminar/<?= $socio->id ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       title="Eliminar"
                                       onclick="return confirm('¿Eliminar al socio <?= $h($socio->nombre_completo) ?>?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
