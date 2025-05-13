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

  <!-- 1. Las pestañas -->
  <ul class="nav nav-tabs" id="tabsAtencion">
    <li class="nav-item">
      <a class="nav-link active" id="tab-turnos" data-bs-toggle="tab" href="#pane-turnos">Turnos</a>
    </li>
    <li class="nav-item">
      <a class="nav-link disabled" id="tab-dx" data-bs-toggle="tab" href="#pane-dx">Diagnóstico</a>
    </li>
    <li class="nav-item">
      <a class="nav-link disabled" id="tab-evo" data-bs-toggle="tab" href="#pane-evo">Evolución</a>
    </li>
    <li class="nav-item">
      <a class="nav-link disabled" id="tab-rec" data-bs-toggle="tab" href="#pane-rec">Recetas</a>
    </li>
    <li class="nav-item">
      <a class="nav-link disabled" id="tab-exa" data-bs-toggle="tab" href="#pane-exa">Exámenes</a>
    </li>
  </ul>

  <!-- 2. Contenido de cada pestaña -->
  <div class="tab-content p-3 border border-top-0">
    <!-- Pestaña: Turnos -->
    <div class="tab-pane fade show active" id="pane-turnos">
      <!-- Tu resumen actual -->
      <div id="resumen" class="alert alert-info d-flex justify-content-between align-items-center" style="font-weight:bold;">
        <span>📅 Hoy: <span id="resFecha"></span></span>
        <span>👥 Atendidos: <span id="resAtendidos">0</span></span>
        <span>⏱ Total tiempo: <span id="resTiempo">0</span> min</span>
        <span>❌ Ausentes: <span id="resAusentes">0</span></span>
        <button id="btnRecargar" class="btn btn-outline-primary btn-sm" title="Recargar información">🔄</button>
      </div>

      <!-- Tu tabla actual -->
      <table class="table" id="tablaAtencion">
        <thead>
          <tr>
            <th>Paciente</th><th>Hora Atención</th><th>Estado/Tiempo</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

    <!-- Pestaña: Diagnóstico -->
    <div class="tab-pane fade" id="pane-dx">
      <p>Aquí irá el formulario de diagnóstico.</p>
    </div>

    <!-- Pestaña: Evolución -->
    <div class="tab-pane fade" id="pane-evo">
      <p>Aquí irá la evolución del paciente.</p>
    </div>

    <!-- Pestaña: Recetas -->
    <div class="tab-pane fade" id="pane-rec">
      <p>Aquí irá la receta médica.</p>
    </div>

    <!-- Pestaña: Exámenes -->
    <div class="tab-pane fade" id="pane-exa">
      <p>Aquí se podrán pedir exámenes complementarios.</p>
    </div>
  </div>
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