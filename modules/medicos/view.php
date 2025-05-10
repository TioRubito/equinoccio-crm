<?php include '../../templates/navbar.php'; ?>
<?php include '../../templates/header.php'; ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<div class="container mt-4">
  <h2>Gestión de Médicos</h2>
  <button id="btnNuevo" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalMedico">➕ Nuevo Médico</button>
  <table id="tablaMedicosDT" class="table table-bordered table-striped" style="width:100%">
      <thead>
        <tr><th>Nombre</th><th>Servicio</th><th>Acciones</th></tr>
      </thead>
      <tbody></tbody>
  </table>
</div>

<!-- Modal -->
<div class="modal fade" id="modalMedico" tabindex="-1" aria-labelledby="labelMedico">
 <div class="modal-dialog"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title" id="labelMedico">Médico</h5>
       <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
     <form id="formMedico">
        <input type="hidden" name="id" id="id">
        <div class="mb-3"><label class="form-label">Nombre</label>
             <input type="text" class="form-control" name="nombre" id="nombre" required></div>
        <div class="mb-3"><label class="form-label">Servicio</label>
             <select class="form-select" name="servicio_id" id="servicio_id" required></select></div>
        <button type="submit" class="btn btn-success">Guardar</button>
     </form>
  </div>
 </div></div>
</div>
<?php include '../../templates/footer.php'; ?>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.7.1/jszip.min.js"></script>
<script defer src="js/funciones.js"></script>