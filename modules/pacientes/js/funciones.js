document.addEventListener('DOMContentLoaded', function () {
    cargarPacientes();

    document.getElementById('formPaciente').addEventListener('submit', function (e) {
        e.preventDefault();
        let formData = new FormData(this);
        formData.append('accion', 'guardar');

        fetch('controller.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('✅ Paciente guardado correctamente');
                this.reset();
                cargarPacientes();
                let modal = bootstrap.Modal.getInstance(document.getElementById('modalPaciente'));
                modal.hide();
            }
        });
    });
});

function cargarPacientes() {
    const tabla = document.getElementById('tablaPacientes');
    const formData = new FormData();
    formData.append('accion', 'listar');

    fetch('controller.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(pacientes => {
        let html = '<table class="table table-bordered table-striped">';
        html += '<thead><tr><th>Nombre</th><th>Cédula</th><th>Correo</th><th>Teléfono</th><th>Sexo</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>';

        pacientes.forEach(p => {
            html += `<tr>
                <td>${p.nombre}</td>
                <td>${p.cedula}</td>
                <td>${p.correo ?? ''}</td>
                <td>${p.telefono ?? ''}</td>
                <td>${p.sexo}</td>
                <td>${p.estado}</td>
                <td>
                    <button class="btn btn-sm btn-primary me-1" onclick="editarPaciente(${p.id}, '${p.nombre}', '${p.cedula}', '${p.correo}', '${p.telefono}', '${p.sexo}')">✏️</button>
                    <button class="btn btn-sm btn-${p.estado === 'activo' ? 'danger' : 'success'}" onclick="cambiarEstado(${p.id}, '${p.estado === 'activo' ? 'inactivo' : 'activo'}')">
                        ${p.estado === 'activo' ? 'Desactivar' : 'Activar'}
                    </button>
                </td>
            </tr>`;
        });

        html += '</tbody></table>';
        tabla.innerHTML = html;
    });
}

function editarPaciente(id, nombre, cedula, correo, telefono, sexo) {
    document.getElementById('id').value = id;
    document.getElementById('nombre').value = nombre;
    document.getElementById('cedula').value = cedula;
    document.getElementById('correo').value = correo;
    document.getElementById('telefono').value = telefono;
    document.getElementById('sexo').value = sexo;
    new bootstrap.Modal(document.getElementById('modalPaciente')).show();
}

function cambiarEstado(id, nuevo_estado) {
    const formData = new FormData();
    formData.append('accion', 'cambiar_estado');
    formData.append('id', id);
    formData.append('estado', nuevo_estado);

    fetch('controller.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            cargarPacientes();
        }
    });
}