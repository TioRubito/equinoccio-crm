document.addEventListener('DOMContentLoaded', function () {
    console.log("🔃 Cargando funciones.js");
    cargarUsuarios();

    const form = document.getElementById('formUsuario');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('accion', 'guardar');

            fetch('/equinoccio-crm/modules/usuarios/controller.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Usuario guardado correctamente');
                    this.reset();
                    cargarUsuarios();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalUsuario'));
                    modal.hide();
                } else {
                    console.error('❌ Error al guardar:', data);
                }
            })
            .catch(error => console.error('❌ Error en fetch guardar:', error));
        });
    }
});

function cargarUsuarios() {
    const tabla = document.getElementById('tablaUsuarios');
    const formData = new FormData();
    formData.append('accion', 'listar');

    fetch('/equinoccio-crm/modules/usuarios/controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(usuarios => {
        console.log("📦 Texto recibido del backend:", usuarios);

        if (!Array.isArray(usuarios)) {
            console.error("❌ No se recibió un array de usuarios:", usuarios);
            return;
        }

        let html = '<table class="table table-bordered table-striped">';
        html += '<thead><tr><th>Nombre</th><th>Usuario</th><th>Nivel</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>';

        usuarios.forEach(u => {
            html += `<tr>
                <td>${u.nombre}</td>
                <td>${u.usuario}</td>
                <td>${u.nivel}</td>
                <td>${u.estado}</td>
                <td>
                    <button class="btn btn-sm btn-primary me-1" onclick="editarUsuario(${u.id}, '${u.nombre}', '${u.usuario}', '${u.nivel}')">✏️</button>
                    <button class="btn btn-sm btn-${u.estado === 'activo' ? 'danger' : 'success'}" onclick="cambiarEstado(${u.id}, '${u.estado === 'activo' ? 'inactivo' : 'activo'}')">
                        ${u.estado === 'activo' ? 'Desactivar' : 'Activar'}
                    </button>
                </td>
            </tr>`;
        });

        html += '</tbody></table>';
        tabla.innerHTML = html;
    })
    .catch(error => console.error('❌ Error en fetch listar:', error));
}

function editarUsuario(id, nombre, usuario, nivel) {
    document.getElementById('id').value = id;
    document.getElementById('nombre').value = nombre;
    document.getElementById('usuario').value = usuario;
    document.getElementById('nivel').value = nivel;
    document.getElementById('clave').value = '';
    new bootstrap.Modal(document.getElementById('modalUsuario')).show();
}

function cambiarEstado(id, nuevo_estado) {
    const formData = new FormData();
    formData.append('accion', 'cambiar_estado');
    formData.append('id', id);
    formData.append('estado', nuevo_estado);

    fetch('/equinoccio-crm/modules/usuarios/controller.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cargarUsuarios();
        } else {
            console.error('❌ Error al cambiar estado:', data);
        }
    })
    .catch(error => console.error('❌ Error en fetch cambiar_estado:', error));
}
