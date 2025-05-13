<?php
session_start();
if(!isset($_SESSION['usuario']) || !in_array($_SESSION['nivel'], ['medico'] ) )
header('Location:../../login.php');
?>
<?php include '../../templates/navbar.php'; ?>
<?php include '../../templates/header.php'; ?>
<style>
  .spin {
    animation: girar 1s linear infinite;
  }

  @keyframes girar {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
  }
</style>

<div class="container mt-4">
  <h2>Atención de Pacientes - Hoy</h2>
  
  <div id="resumen" class="alert alert-info d-flex justify-content-between align-items-center" style="font-weight:bold;">
      <span>📅 Hoy: <span id="resFecha"></span></span>
      <span>👥 Atendidos: <span id="resAtendidos">0</span></span>
      <span>⏱ Total tiempo: <span id="resTiempo">0</span> min</span>
      <span>❌ Ausentes: <span id="resAusentes">0</span></span>
      <button id="btnRecargar" class="btn btn-outline-primary btn-sm" title="Recargar información">
        🔄
      </button>
  </div>


  <table class="table" id="tablaAtencion">
    <thead>
      <tr>
        <th>Paciente</th><th>Hora Atención</th><th>Estado/Tiempo</th><th>Acciones</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<!-- Modal seguimiento 
<div class="modal fade" id="modalAtencion" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Registrar Atención</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formAtencion">
          <input type="hidden" name="cita_id" id="cita_id">
          <div class="mb-3">
            <label>Inicio</label>
            <input type="datetime-local" id="inicio" name="inicio" class="form-control">
          </div>
          <div class="mb-3">
            <label>Fin</label>
            <input type="datetime-local" id="fin" name="fin" class="form-control">
          </div>
          <div class="mb-3">
            <label>Estado</label>
            <select id="estado" name="estado" class="form-select">
              <option value="atendido">Atendido</option>
              <option value="ausente">Ausente</option>
            </select>
          </div>
          <div class="mb-3">
            <label>Diagnóstico</label>
            <input type="text" id="diagnostico" name="diagnostico" class="form-control">
          </div>
          <div class="mb-3">
            <label>Notas Clínicas</label>
            <textarea id="notas" name="notas" class="form-control"></textarea>
          </div>
          <button class="btn btn-success">Guardar</button>
        </form>
      </div>
    </div>
  </div>
</div>-->

<script src="js/atencion.js"></script>
<?php include '../../templates/footer.php'; ?>