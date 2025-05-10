document.addEventListener('DOMContentLoaded', ()=>{
  const tablaDiv = $('#tablaMedicosDT');
  const form     = document.getElementById('formMedico');
  const modal    = new bootstrap.Modal(document.getElementById('modalMedico'));
  const selServ  = document.getElementById('servicio_id');

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
  const dt = tablaDiv.DataTable({
    ajax:{ url:'controller.php', type:'POST', data:{accion:'listar'}, dataSrc:'' },
    columns:[
      {data:'medico'},
      {data:'servicio'},
      {data:null,orderable:false,render:d=>`
        <button class="btn btn-sm btn-primary me-1" onclick="editarMedico(${d.id}, '${d.medico.replace(/'/g,"\\'")}', ${d.servicio_id})">✏️</button>
        <button class="btn btn-sm btn-danger" onclick="eliminarMedico(${d.id})">🗑️</button>`}
    ],
    dom:'Bfrtip',
    buttons:[{extend:'excelHtml5',text:'Exportar Excel'},{extend:'csvHtml5',text:'Exportar CSV'}],
    lengthMenu:[10,25,50,100]
  });

  /* ───── helper para recargar sin perder página ───── */
  const recargar = ()=> dt.ajax.reload(null,false);

  /* ───── guardar ───── */
  form.addEventListener('submit',e=>{
    e.preventDefault();
    const data=new FormData(form); data.append('accion','guardar');
    fetch('controller.php',{method:'POST',body:data})
    .then(r=>r.json()).then(res=>{ if(res.success){ modal.hide(); form.reset(); recargar(); }});
  });

  /* ───── helpers globales ───── */
  window.editarMedico=(id,nombre,servicio_id)=>{
    form.id.value=id; form.nombre.value=nombre; form.servicio_id.value=servicio_id; modal.show();
  };
  window.eliminarMedico=id=>{
    if(!confirm('¿Eliminar este médico?')) return;
    fetch('controller.php',{method:'POST',body:new URLSearchParams({accion:'borrar',id})})
      .then(r=>r.json()).then(()=>recargar());
  };

  document.getElementById('btnNuevo').addEventListener('click',()=>{form.reset();form.id.value='';});
});