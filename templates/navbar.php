<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../login.php");
    exit;
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Equinoccio-CRM</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="/equinoccio-crm/modules/pacientes/view.php">Pacientes</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/equinoccio-crm/modules/citas/view.php">Citas</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/equinoccio-crm/modules/servicios/view.php">Servicios</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/equinoccio-crm/modules/medicos/view.php">Médicos</a>
        </li>
        <?php if ($_SESSION['nivel'] === 'admin'): ?>
        <li class="nav-item">
          <a class="nav-link" href="/equinoccio-crm/modules/usuarios/view.php">Usuarios</a>
        </li>
        <?php endif; ?>
      </ul>
      <span class="navbar-text me-3">
        <?= $_SESSION['usuario'] ?>
      </span>
      <a href="/equinoccio-crm/logout.php" class="btn btn-outline-light btn-sm">Salir</a>
    </div>
  </div>
</nav>
