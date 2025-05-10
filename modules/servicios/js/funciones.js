document.addEventListener('DOMContentLoaded', () => {
  const tablaEl = $('#tablaServiciosDT');
  const form    = document.getElementById('formServicio');
  const modal   = new bootstrap.Modal(document.getElementById('modalServicio'));
  let tabla;

  /* ---------- DataTable ---------- */
  tabla = tablaEl.DataTable({
    ajax : {
      url : '../servicios/controller_servicios.php',
      type: 'POST',
      data: { accion:'listar' },
      dataSrc: ''
    },
    columns:[
      { data:'nombre' },
      { data:'activo', render:d=> d==1?'Activo':'Inactivo' },
      { data:'color',  render: c => `
        <span title="${c}"
              style="display:inline-block;width:1.2rem;height:1.2rem;
                     border-radius:50%;background:${c};
                     border:1px solid #ccc;vertical-align:middle"></span>
        <small class="text-muted ms-1">${c}</small>` },
      /*{ data:'color' },      */
      { 
        data:null, orderable:false,
        render:d=>`
          <button class="btn btn-sm btn-primary me-1" onclick="editarServicio(${d.id}, '${d.nombre.replace(/'/g,"\\'")}', '${d.color}',${d.activo})">✏️</button>
          <button class="btn btn-sm btn-danger" onclick="borrarServicio(${d.id})">🗑️</button>` 
      }
    ],
    dom:'Bfrtip',
    buttons:[
      { extend:'excelHtml5', text:'Exportar Excel' },
      { extend:'csvHtml5'  , text:'Exportar CSV'   }
    ],
    lengthMenu:[10,25,50,100]
  });

  /* ---------- reload helper ---------- */
  const recargar = ()=> tabla.ajax.reload(null,false);

  /* ---------- formulario guardar ---------- */
  form.addEventListener('submit', e=>{
    e.preventDefault();
    const data = new FormData(form);
    data.append('accion','guardar');
    fetch('../servicios/controller_servicios.php',{ method:'POST', body:data })
      .then(r=>r.json())
      .then(res=>{ if(res.success){ modal.hide(); form.reset(); recargar(); } });
  });

  /* ---------- helpers global ---------- */
  window.editarServicio = (id,nombre,color,activo)=>{
    form.id.value    = id;
    form.nombre.value= nombre;
    form.color.value= color;
    form.activo.value= activo;
    modal.show();
  };

  window.borrarServicio = id=>{
    if(!confirm('¿Eliminar este servicio?')) return;
    const body = new URLSearchParams({ accion:'borrar', id });
    fetch('../servicios/controller_servicios.php',{ method:'POST', body })
      .then(r=>r.json())
      .then(res=>{ if(res.success) recargar(); });
  };

  /* ---------- nuevo ---------- */
  document.getElementById('btnNuevo').addEventListener('click',()=>{
    form.reset(); form.id.value='';
  });
});
