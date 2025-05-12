document.addEventListener('DOMContentLoaded', ()=>{
  const tablaDiv = $('#tablaMedicosDT');
  const form     = document.getElementById('formMedico');
  const modal    = new bootstrap.Modal(document.getElementById('modalMedico'));

  const inputClave = document.getElementById('clave');
  const btnEditarClave = document.getElementById('btnEditarClave');
  
  const selServ  = document.getElementById('servicio_id');
  let dt;

  // Desbloquear campo clave al hacer clic
  btnEditarClave.addEventListener('click', () => {
    if (inputClave.disabled) {
      inputClave.disabled = false;
      inputClave.focus();
      btnEditarClave.textContent = '✏️';
      btnEditarClave.title = 'Campo desbloqueado';
    } else {
      inputClave.disabled = true;
      inputClave.value = ''; // opcional: limpia si se vuelve a bloquear
      btnEditarClave.textContent = '🔒';
      btnEditarClave.title = 'Editar contraseña';
    }
  });

  /* ───── servicios en combo ───── */
  fetch('../servicios/controller_servicios.php', {
    method:'POST',
    body:new URLSearchParams({accion:'listar'})
  })
  .then(r=>r.json())
  .then(s=>{
     selServ.innerHTML='<option value="">Seleccione…</option>';
     s.forEach(x=> selServ.innerHTML += `<option value="${x.id}">${x.nombre}</option>`);
  });

  /* ───── dataTable ───── */
   dt = tablaDiv.DataTable({
    ajax:{ url:'controller.php', type:'POST', data:{accion:'listar'}, dataSrc:'' },
    columns:[
      {data:'medico'},
      {data:'servicio'},
      {data:'usuario'},
      {
        data: null,
        orderable: false,
        render: function(data, type, row) {
          return `
            <button class="btn btn-sm btn-primary me-1" onclick='editarMedico(${JSON.stringify(row)})'>✏️</button>
            <button class="btn btn-sm btn-danger" onclick="eliminarMedico(${row.id})">🗑️</button>
          `;
        }
      }

    ],
    dom:'Bfrtip',
    buttons:[{extend:'excelHtml5',text:'Exportar Excel'},{extend:'csvHtml5',text:'Exportar CSV'}],
    lengthMenu:[10,25,50,100]
  });

  /* ───── helper para recargar sin perder página ───── */
  const recargar = ()=> dt.ajax.reload(null,false);

  /* ───── guardar ───── */
  /*form.addEventListener('submit',e=>{
    e.preventDefault();
    const data=new FormData(form); data.append('accion','guardar');
    fetch('controller.php',{method:'POST',body:data})
    .then(r=>r.json()).then(res=>{ if(res.success){ modal.hide(); form.reset(); recargar(); }});
  });*/
  form.addEventListener('submit', e => {
      e.preventDefault();
      const data = new FormData(form);
      data.append('accion', 'guardar');

      fetch('controller.php', { method: 'POST', body: data })
          .then(r => r.json())
          .then(res => {
              if (res.success) {
                  /*modal.hide();*/
                  bootstrap.Modal.getInstance(document.getElementById('modalMedico')).hide();
                  form.reset();
                  recargar();
                  document.getElementById('mensajeError').textContent = "✅ Médico registrado correctamente.";              
                  setTimeout(() => {
                      document.getElementById('mensajeError').textContent = '';
                  }, 3000);
              } else {
                  //alert(res.error || "Ocurrió un error al guardar.");
                  document.getElementById('mensajeError').textContent = res.error;
              }
      });
      return false; // Extra capa de protección
  });


  /* ───── helpers globales ───── */
  window.editarMedico = (medico) => {
    form.id.value = medico.id;
    form.nombre.value = medico.medico;
    form.servicio_id.value = medico.servicio_id;
    form.usuario.value = medico.usuario;
    form.clave.value = '';
    form.clave.disabled = true;
    modal.show();
  };


  window.eliminarMedico=id=>{
    if(!confirm('¿Eliminar este médico?')) return;
    fetch('controller.php',{method:'POST',body:new URLSearchParams({accion:'borrar',id})})
      .then(r=>r.json()).then(()=>recargar());
  };

  document.getElementById('btnNuevo').addEventListener('click',()=>{
      form.reset();
      form.id.value = '';
      form.clave.disabled = false;
      form.clave.value = 'claveTemporal123'; // valor por defecto
  });

});