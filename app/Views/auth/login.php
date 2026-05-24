<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso — Carnets</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border-radius: 1rem;
            border: none;
            box-shadow: 0 8px 32px rgba(0,0,0,.35);
        }
        .login-brand {
            text-align: center;
            margin-bottom: 2rem;
            color: #1e293b;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .login-brand i { font-size: 2.5rem; color: #3b82f6; display: block; margin-bottom: .5rem; }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<?php $h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>

<div class="card login-card p-4">
    <div class="card-body">
        <div class="login-brand">
            <i class="bi bi-credit-card-2-front-fill"></i>
            Carnets
        </div>

        <?php if (session()->has('error')): ?>
            <div class="alert alert-danger py-2">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <?= $h(session('error') ?? '') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->has('errors')): ?>
            <div class="alert alert-danger py-2">
                <ul class="mb-0">
                    <?php foreach ((array) (session('errors') ?? []) as $err): ?>
                        <li><?= $h($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="/login">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="usuario" class="form-label fw-semibold">Usuario</label>
                <input
                    type="text"
                    id="usuario"
                    name="usuario"
                    class="form-control"
                    value="<?= $h(old('usuario')) ?>"
                    required
                    autofocus
                    autocomplete="username"
                >
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-semibold">Contraseña</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    required
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
