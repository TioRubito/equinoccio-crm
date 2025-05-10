<?php
session_start();
if (!isset($_SESSION['usuario']) || !in_array($_SESSION['nivel'], ['admin','operador'])) {
  header('Location: ../../login.php'); exit;
}
require '../../config/db.php';
?>
<?php include '../../templates/navbar.php'; ?>
<?php include '../../templates/header.php'; ?>

<!-- ── CSS ─────────────────────────────────────────────── -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

<div class="container mt-4">
  <h2>Gestión de Citas</h2>
  <div id="calendar"></div>
</div>

<!-- ── MODAL ────────────────────────────────────────────── -->
<div class="modal fade" id="modalCita" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">Cita</h5>
      <button class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <form id="formCita">
        <input type="hidden" id="cita_id"   name="id">
        <input type="hidden" id="paciente_id" name="paciente_id">
        <input type="hidden" id="medico_id"   name="medico_id">
        <input type="hidden" name="servicio" id="servicio_nombre">


        <div class="mb-3">
          <label class="form-label">Paciente</label>
          <select id="paciente" style="width:100%"></select>
        </div>

        <div class="mb-3">
          <label class="form-label">Médico</label>
          <select id="medico" style="width:100%"></select>
        </div>
      
        <div class="mb-3">
          <label class="form-label">Inicio</label>
          <input type="datetime-local" class="form-control" id="start" name="start" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Fin</label>
          <input type="datetime-local" class="form-control" id="end" name="end" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Descripción</label>
          <textarea class="form-control" id="title" name="title"></textarea>
        </div>

        <button class="btn btn-success">Guardar</button>
        <button type="button" class="btn btn-danger" id="btnEliminar">Eliminar</button>
      </form>
    </div>
  </div></div>
</div>

<?php include '../../templates/footer.php'; ?>

<!-- ── JS ──────────────────────────────────────────────── -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales-all.min.js"></script>

<script>
  
document.addEventListener('DOMContentLoaded', () => {
  const modal    = new bootstrap.Modal(document.getElementById('modalCita'));

  const toLocalDatetime = date =>
  new Date(date.getTime() - date.getTimezoneOffset() * 60000)
    .toISOString()
    .slice(0, 16);

  const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {

    locale : 'es',
    /*initialDate: new Date(),*/
    initialView : 'dayGridMonth',
    timeZone: 'local',
    nowIndicator : true, 
    headerToolbar:{ left:'prev,next today', center:'title', right:'dayGridMonth,timeGridWeek,timeGridDay,listWeek'},
    /*events : { url:'controller_citas.php', method:'POST', extraParams:{ accion:'listar' } },*/
    
    eventOverlap(still, moving){
      if (!still?.extendedProps || !moving?.extendedProps) return true;
      return still.extendedProps.medico_id !== moving.extendedProps.medico_id;
    },

    selectOverlap(info){
      // si info.event es undefined => no hay intersección previa
      if (!info.event?.extendedProps) return true;
      return info.event.extendedProps.medico_id !== $('#medico_id').val();
    },

    events: function(info, success, failure) {
      // info.startStr e info.endStr traen el rango que muestra el calendario
      fetch('controller_citas.php', {
        method: 'POST',
        body: new URLSearchParams({
          accion : 'listar',
          start  : info.startStr,
          end    : info.endStr
        })
      })
      .then(r => r.json())
      .then(success)      // pinta los eventos
      .catch(failure);    // si algo falla
    },


    selectable:true,

    select(info){
      const now = new Date();

      /* ── 1) Bloquea si es pasado ──────────────── */
      if (info.start < now){
        alert('No se pueden crear citas en el pasado');
        calendar.unselect();
        return;
      }

      /* ── 2) Bloquea horas YA PASADAS de hoy ───── */
      if ( info.start.toDateString() === now.toDateString() && info.start < now ){
        alert('No se puede crear una cita en horas pasadas de hoy');
        calendar.unselect();
        return;
      }

      /* ── 3) Muestra el formulario ─────────────── */
      resetForm();
      $('#start').val( toLocalDatetime(info.start) );
      $('#end')  .val( toLocalDatetime(info.end)   );
      modal.show();
    },


    eventAllow(dropInfo, draggedEvent){
      const ahora = new Date();
      // impedir que el nuevo rango quede antes de "ahora"
      return dropInfo.start >= ahora;
    },


    eventClick({event:e}){
      
      const ahora = new Date();
      // si ya terminó → sólo lectura
      if (e.end < ahora){
        alert('Esta cita ya está en el pasado; no se puede modificar.');
        return;
      }


      resetForm();
      $('#cita_id').val(e.id);

      /* ---------- pinta Paciente ---------- */
      const optP = new Option(`${e.extendedProps.paciente_nombre} — ${e.extendedProps.paciente_id}`,
                              e.extendedProps.paciente_id, true, true);
      $('#paciente').append(optP).trigger('change');
      $('#paciente_id').val(e.extendedProps.paciente_id);

      /* ---------- pinta Médico ---------- */
      const optM = new Option(e.extendedProps.medico_nombre, e.extendedProps.medico_id, true, true);
      $('#medico').append(optM).trigger('change');
      $('#medico_id').val(e.extendedProps.medico_id);


      $('#start').val( toLocalDatetime(e.start) );
      $('#end')  .val( toLocalDatetime(e.end)   );


     /* $('#start').val(e.start.toISOString().slice(0,16));
      $('#end')  .val(e.end  .toISOString().slice(0,16));*/
      $('#title').val(e.title);
      modal.show();
    },    
    eventDidMount(info){
      // hex guardado en extendedProps
      const c = info.event.extendedProps.servicio_colorx;
      if (c){
        info.el.style.backgroundColor = c;
        info.el.style.borderColor     = c;
      }

      // tooltip
      info.el.title =
        `${info.event.extendedProps.paciente_nombre} — ` +
        `${info.event.extendedProps.paciente_cedula}\n` +
        `${info.event.extendedProps.medico_nombre} · ` +
        `${info.event.extendedProps.servicio}`;
    }



  });
  calendar.render();

  /* ---------- Select2 Paciente ---------- */
  $('#paciente').select2({
    dropdownParent: $('#modalCita'),
    placeholder:'Busca paciente',
    ajax:{
      url:'/equinoccio-crm/modules/pacientes/search.php',
      dataType:'json', delay:250,
      data: p=>({ q:p.term }),
      processResults:d=>({results:d.map(p=>({id:p.id,text:`${p.nombre} — ${p.cedula}`}))})
    }
  }).on('select2:select', e=> $('#paciente_id').val(e.params.data.id));

  /* ---------- Select2 Médico ---------- */
  /*$('#medico').select2({
    dropdownParent: $('#modalCita'),
    placeholder:'Busca médico',
    ajax:{
      url:'/equinoccio-crm/modules/medicos/search.php',
      dataType:'json', delay:250,
      data: p=>({ q:p.term }),
      processResults:d=>({results:d.map(m=>({id:m.id,text:m.nombre}))})
    }
  }).on('select2:select', e=> $('#medico_id').val(e.params.data.id));*/

  $('#medico').select2({
    dropdownParent: $('#modalCita'),
    placeholder: 'Busca médico',
    minimumInputLength: 1,
    ajax:{
      url : '/equinoccio-crm/modules/medicos/search.php',
      dataType: 'json',
      delay    : 250,
      data     : p => ({ q: p.term }),
      processResults: d => ({
        results: d.map(m => ({
          id  : m.id,
          // Ej.: “Dra. López — Oncología Clínica”
          text: `${m.medico} — ${m.servicio}`,
          servicio: m.servicio          // ← guardamos también el servicio
        }))
      })
    }
  }).on('select2:select', e => {
    $('#medico_id').val(e.params.data.id);
    // Guarda el servicio en un <input hidden> si lo necesitas:
    $('#servicio_nombre').val(e.params.data.servicio);
  });



  /* ---------- Guardar ---------- */
  /*$('#formCita').on('submit', e=>{
    e.preventDefault();
    const fd = new FormData(e.target);
    const inicio = new Date($('#start').val());
    if (inicio < new Date()){
      return alert('La fecha de inicio no puede estar en el pasado');
    }
    fd.append('accion','guardar');
    fetch('controller_citas.php',{ method:'POST', body:fd })
      .then(r=>r.json())
      .then(res=>{
        if(res.error) return alert(res.error);
        modal.hide(); calendar.refetchEvents();
      });
  });*/

  $('#formCita').on('submit', e => {
    e.preventDefault();
    const btn = $(e.target).find('button[type=submit]');
    btn.prop('disabled', true).text('Guardando…');

    const fd = new FormData(e.target);
    fd.append('accion','guardar');

    fetch('controller_citas.php', {method:'POST', body:fd})
      .then(r=>r.json())
      .then(res=>{
        btn.prop('disabled', false).text('Guardar');
        if(res.error) return alert(res.error);
        modal.hide(); calendar.refetchEvents();
      })
      .catch(()=>{ btn.prop('disabled',false).text('Guardar'); });
  });



  /* ---------- Borrar ---------- */
  $('#btnEliminar').on('click', ()=>{
    const id = $('#cita_id').val();
    if(!id) return;
    if(!confirm('¿Eliminar la cita?')) return;
    fetch('controller_citas.php',{ method:'POST',
      body:new URLSearchParams({accion:'borrar', id}) })
    .then(r=>r.json()).then(()=>{ modal.hide(); calendar.refetchEvents();});
  });
  

  function resetForm(){
    $('#formCita')[0].reset();
    $('#paciente, #medico').val(null).trigger('change').empty();
    $('#paciente_id, #medico_id').val('');
    $('#cita_id').val('');
  }
});
</script>
