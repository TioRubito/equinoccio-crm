<?php
require '../../config/db.php';
?>
<?php include '../../templates/navbar.php'; ?>
<?php include '../../templates/header.php'; ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
<h2>Registrar Médico</h2>
<form method="post" action="registrar_medico.php">
    <label>Nombre del médico:</label><br>
    <input type="text" name="nombre" required><br><br>

    <label>Nombre de usuario:</label><br>
    <input type="text" name="usuario" required><br><br>

    <label>Servicio / Especialidad:</label><br>
    <select name="servicio_id" required>
        <option value="">-- Selecciona --</option>
        <?php
        $stmt = $pdo->query("SELECT id, nombre FROM servicios ORDER BY nombre");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
        }
        ?>
    </select><br><br>

    <button type="submit">Registrar Médico</button>
</form>
