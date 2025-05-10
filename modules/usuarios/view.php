<?php
require '../../config/db.php';
?>
<?php include '../../templates/navbar.php'; ?>
<?php include '../../templates/header.php'; ?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

<div class="container mt-4">
    <h2>Gestión de Usuarios</h2>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalUsuario">➕ Nuevo Usuario</button>

    <table id="tablaUsuariosDT" class="table table-bordered table-striped" style="width:100%">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Nivel</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Modal para crear/editar usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Nuevo Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="formUsuario">
            <input type="hidden" name="id" id="id">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" id="usuario" name="usuario" required>
            </div>
            <div class="mb-3">
                <label for="clave" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="clave" name="clave">
            </div>
            <div class="mb-3">
                <label for="nivel" class="form-label">Nivel</label>
                <select class="form-select" id="nivel" name="nivel">
                    <option value="admin">Admin</option>
                    <option value="operador">Operador</option>
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
let tablaUsuarios;
function recargarUsuarios() {
    tablaUsuarios.ajax.reload(null, false);
}

$(document).ready(function() {
    tablaUsuarios = $('#tablaUsuariosDT').DataTable({
        ajax: {
            url: 'controller.php',
            type: 'POST',
            data: { accion: 'listar' },
            dataSrc: ''
        },
        columns: [
            { data: 'nombre' },
            { data: 'usuario' },
            { data: 'nivel' },
            { data: 'estado' },
            {
                data: null,
                orderable: false,
                render: function(d) {
                    const editBtn = `<button class="btn btn-sm btn-primary me-1" onclick="editarUsuario(${d.id}, '${d.nombre}', '${d.usuario}', '${d.nivel}')">✏️</button>`;
                    const stateBtn = `<button class="btn btn-sm btn-${d.estado === 'activo' ? 'danger' : 'success'}" onclick="cambiarEstado(${d.id}, '${d.estado === 'activo' ? 'inactivo' : 'activo'}')">${d.estado === 'activo' ? 'Desactivar' : 'Activar'}</button>`;
                    return editBtn + stateBtn;
                }
            }
        ],
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excelHtml5', text: 'Exportar Excel' },
            { extend: 'csvHtml5', text: 'Exportar CSV' }
        ]
    });

    // Manejo del formulario de usuarios
    $('#formUsuario').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('accion', 'guardar');
        $.ajax({
            url: 'controller.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    $('#formUsuario')[0].reset();
                    $('#modalUsuario').modal('hide');
                    recargarUsuarios();
                }
            }
        });
    });
});

function editarUsuario(id, nombre, usuario, nivel) {
    $('#id').val(id);
    $('#nombre').val(nombre);
    $('#usuario').val(usuario);
    $('#nivel').val(nivel);
    $('#clave').val('');
    new bootstrap.Modal(document.getElementById('modalUsuario')).show();
}

function cambiarEstado(id, nuevo) {
    $.post('controller.php', { accion: 'cambiar_estado', id: id, estado: nuevo }, function(res) {
        if (res.success) recargarUsuarios();
    }, 'json');
}
</script>
