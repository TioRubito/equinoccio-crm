<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../../config/db.php';
?>
<?php include '../../templates/navbar.php'; ?>
<?php include '../../templates/header.php'; ?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

<!-- jQuery (requerido por DataTables) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="container mt-4">
  <h2>Gestión de Servicios</h2>
  <button id="btnNuevo" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalServicio">➕ Nuevo Servicio</button>

  <table id="tablaServiciosDT" class="table table-bordered table-striped" style="width:100%">
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Estado</th>
        <th>Color</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<!-- Modal Servicio -->
<div class="modal fade" id="modalServicio" tabindex="-1" aria-labelledby="lblServicio" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="lblServicio">Servicio</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formServicio">
          <input type="hidden" name="id" id="id">

          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" class="form-control" name="nombre" id="nombre" required>
            <label class="form-label">Color</label>
            <input type="color" class="form-control form-control-color"
             name="color" id="color" value="#0d6efd">

          </div>
          
          <div class="mb-3">
            <label class="form-label">Estado</label>
            <select class="form-select" name="activo" id="activo">
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
          <button type="submit" class="btn btn-success">Guardar</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include '../../templates/footer.php'; ?>

<!-- DataTables & JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.7.1/jszip.min.js"></script>
<!-- nuestro JS -->
<script defer src="js/funciones.js"></script>