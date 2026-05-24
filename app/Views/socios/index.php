<!-- Vista: socios/index.php  (cargada dentro de layouts/main.php) -->
<?php /** @var list<object> $socios */ ?>
<?php $h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h4 mb-0 fw-bold">
        <i class="bi bi-people-fill me-2 text-primary"></i>Socios
    </h1>
    <a href="/socios/crear" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus-fill me-1"></i> Nuevo socio
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Foto</th>
                        <th>Nombre completo</th>
                        <th>Tipo</th>
                        <th>DNI</th>
                        <th>Email</th>
                        <th>Nº Socio</th>
                        <th>Válido hasta</th>
                        <th>Alta</th>
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
                            <tr>
                                <td class="text-muted small"><?= $h($socio->id) ?></td>
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
                                <td><?= $h($socio->nombre_completo) ?></td>
                                <td><span class="badge text-bg-light border"><?= $h($socio->tipo_socio ?? 'Socio/a') ?></span></td>
                                <td><code><?= $h($socio->dni ?? '') ?></code></td>
                                <td><?= $h($socio->email ?? '') ?></td>
                                <td><code><?= $h($socio->num_socio) ?></code></td>
                                <td><?= $h($socio->valido_hasta) ?></td>
                                <td class="text-muted small"><?= $h(date('d/m/Y', strtotime((string) $socio->created_at))) ?></td>
                                <td class="text-end">
                                    <!-- Ver carnet -->
                                    <a href="/carnet/ver/<?= $socio->id ?>"
                                       class="btn btn-sm btn-outline-info"
                                       target="_blank"
                                       title="Ver carnet">
                                        <i class="bi bi-credit-card"></i>
                                    </a>
                                    <!-- Editar -->
                                    <a href="/socios/editar/<?= $socio->id ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <!-- Eliminar -->
                                    <a href="/socios/eliminar/<?= $socio->id ?>"
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

    <?php if (isset($pager)): ?>
        <div class="card-footer bg-transparent d-flex justify-content-end">
            <?= $pager->links() ?>
        </div>
    <?php endif; ?>
</div>
