<?php
require '../../config/db.php';
?>
<?php include '../../templates/navbar.php'; ?>
<?php include '../../templates/header.php'; ?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

<!-- jQuery 1.º -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>





<div class="container mt-4">
    <h2>Gestión de Pacientes</h2>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalPaciente">➕ Nuevo Paciente</button>

    <table id="tablaPacientesDT" class="table table-bordered table-striped" style="width:100%">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Cédula</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Sexo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Modal para crear/editar paciente -->
<div class="modal fade" id="modalPaciente" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Nuevo Paciente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="formPaciente">
            <input type="hidden" name="id" id="id">
            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" class="form-control" name="nombre" id="nombre" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Cédula</label>
                <input type="text" class="form-control" name="cedula" id="cedula" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Correo</label>
                <input type="email" class="form-control" name="correo" id="correo">
            </div>
            <div class="mb-3">
                <label class="form-label">Teléfono</label>
                <input type="text" class="form-control" name="telefono" id="telefono">
            </div>
            <div class="mb-3">
                <label class="form-label">Sexo</label>
                <select class="form-select" name="sexo" id="sexo">
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Guardar</button>
        </form>
      </div>
    </div>
  </div>
</div>


<?php include '../../templates/footer.php'; ?>

<!-- jQuery y DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.7.1/jszip.min.js"></script>

<script>
let tablaPacientes;
/* ------------- recargar DataTable sin perder la página ------------- */
const recargarPacientes = () => tablaPacientes.ajax.reload(null, false);

document.addEventListener('DOMContentLoaded', () => {

  /* ====== DataTable ====== */
  tablaPacientes = $('#tablaPacientesDT').DataTable({
    ajax : {
      url : 'controller.php',
      type: 'POST',                    // ← DataTables usa jQuery internamente
      data: { accion: 'listar' },
      dataSrc: ''
    },
    columns : [
      { data:'nombre'   },
      { data:'cedula'   },
      { data:'correo',   defaultContent:'' },
      { data:'telefono', defaultContent:'' },
      { data:'sexo'     },
      { data:'estado'   },
      {
        data: null, orderable:false,
        render : d => `
          <button class="btn btn-sm btn-primary me-1"
                  onclick="editarPaciente(${d.id}, '${d.nombre.replace(/'/g,"\\'")}',
                                           '${d.cedula}','${d.correo}','${d.telefono}','${d.sexo}')">✏️</button>
          <button class="btn btn-sm btn-${d.estado==='activo'?'danger':'success'}"
                  onclick="cambiarEstado(${d.id}, '${d.estado==='activo'?'inactivo':'activo'}')">
                  ${d.estado==='activo'?'Desactivar':'Activar'}
          </button>`
      }
    ],
    dom:'Bfrtip',
    buttons:[
      { extend:'excelHtml5', text:'Exportar Excel' },
      { extend:'csvHtml5'  , text:'Exportar CSV'   }
    ],
    lengthMenu:[10,25,50,100]
  });

  /* ====== Guardar / actualizar ====== */
  document.getElementById('formPaciente').addEventListener('submit', e=>{
    e.preventDefault();

    const data = new FormData(e.target);
    data.append('accion','guardar');

    fetch('controller.php', { method:'POST', body:data })
      .then(r => r.json())
      .then(res => {
        if(res.success){
          bootstrap.Modal.getInstance(document.getElementById('modalPaciente')).hide();
          e.target.reset();
          recargarPacientes();
        }else{
          alert('⚠️ No se pudo guardar');
        }
      })
      .catch(err => console.error(err));
  });
});

/* ----------- helpers globales ----------- */
function editarPaciente(id,nombre,cedula,correo,telefono,sexo){
  document.getElementById('id').value       = id;
  document.getElementById('nombre').value   = nombre;
  document.getElementById('cedula').value   = cedula;
  document.getElementById('correo').value   = correo;
  document.getElementById('telefono').value = telefono;
  document.getElementById('sexo').value     = sexo;
  new bootstrap.Modal(document.getElementById('modalPaciente')).show();
}

function cambiarEstado(id,nuevoEstado){
  const body = new URLSearchParams({ accion:'cambiar_estado', id, estado:nuevoEstado });

  fetch('controller.php', { method:'POST', body })
    .then(r => r.json())
    .then(res => { if(res.success) recargarPacientes(); })
    .catch(err => console.error(err));
}
</script>
