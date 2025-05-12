<?php
session_start();
require 'config/db.php';
echo password_hash('osito', PASSWORD_DEFAULT);
echo "<br>";
echo password_hash('pupi', PASSWORD_DEFAULT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $clave = $_POST['clave'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch();

    if ($user && password_verify($clave, $user['clave'])) {
        $_SESSION['usuario'] = $user['usuario'];
        $_SESSION['nivel'] = $user['nivel'];
        $_SESSION['id_usuario'] = $user['id'];
        header("Location: index.php");
        exit;
    } else {
        $error = "❌ Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <h3 class="text-center mb-3">Login CRM</h3>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="usuario" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Clave</label>
                    <input type="password" name="clave" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100">Ingresar</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
