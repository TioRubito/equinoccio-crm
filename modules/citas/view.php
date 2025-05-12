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
        <input type="hidden" id="cita_id"     name="id">
        <input type="hidden" id="paciente_id" name="paciente_id">
        <input type="hidden" id="medico_id"   name="medico_id">
        <input type="hidden" id="servicio_nombre" name="servicio">

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

        <button class="btn btn-success" type="submit">Guardar</button>
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
<style>
  /* día del mes (grid month) */
    #calendar .fc-daygrid-day-frame {
      min-height: 120px;
      display: flex;
      flex-direction: column;
    }

    /* contenedor de eventos: que haga scroll cuando haya muchos */
    #calendar .fc-daygrid-day-events {
      flex: 1;
      overflow-y: auto;
    }

</style>
<script>
$(function(){

  // helper: convierte Date → "YYYY-MM-DDThh:mm" en tu zona
  function toLocalDatetime(d){
    const pad = n => String(n).padStart(2,'0');
    return [
      d.getFullYear(),
      pad(d.getMonth()+1),
      pad(d.getDate())
    ].join('-') + 'T' +
    [ pad(d.getHours()), pad(d.getMinutes()) ].join(':');
  }

  // inicializa el modal de Bootstrap
  const modal = new bootstrap.Modal($('#modalCita')[0]);

  // instancia FullCalendar
  const calendar = new FullCalendar.Calendar(
    document.getElementById('calendar'),
    {
      locale       : 'es',
      timeZone     : 'local',
      nowIndicator : true,
      initialView  : 'dayGridMonth',
      headerToolbar: {
        left  : 'prev,next today',
        center: 'title',
        right : 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
      },
      // máximo de filas de evento por celda
      dayMaxEventRows: 3,   // si hay más, aparecerá "+ X more"
      dayMaxEvents: true,   // lo mismo para la vista list

      // opcional: para que muestre siempre "+ más"
      moreLinkClick: 'popover',

      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
      },

      /******************************************************************************* */
        /*eventSources: [

          // ——— Tus citas normales ———
          {
            id        : 'citas',
            url       : 'controller_citas.php',
            method    : 'POST',
            extraParams: () => ({
              accion: 'listar',
              start : calendar.view.activeStart.toISOString().slice(0,10),
              end   : calendar.view.activeEnd  .toISOString().slice(0,10)
            })
          },

          // ——— Horario de atención (fondo verde claro) ———
          {
            id      : 'horarioTrabajo',
            events  : [],                // lo llenamos luego
            display : 'background',
            color   : '#d4edda'          // verde claro
          },

          // ——— Bloqueos (fondo gris oscuro) ———
          {
            id      : 'bloqueos',
            events  : [],                // lo llenamos luego
            display : 'background',
            color   : '#6c757d'          // gris oscuro
          }
        ],*/

      /********************************************************************************* */

      // 1) carga las citas
      events(info, success, failure){
        fetch('controller_citas.php', {
          method: 'POST',
          body: new URLSearchParams({
            accion: 'listar',
            start : info.startStr,
            end   : info.endStr
          })
        })
        .then(r => r.json())
        .then(success)
        .catch(failure);
      },

      // 2) no permite solapar citas del mismo médico (drag / resize)
      eventOverlap(still, moving){
        return still.extendedProps.medico_id !== moving.extendedProps.medico_id;
      },

      // 3) no permite solapar al crear con drag–&–drop
      selectAllow(info){
        const medSel = $('#medico_id').val();
        if (!medSel) return true; // aún no eligió médico
        return !calendar.getEvents().some(ev =>
          ev.extendedProps.medico_id == medSel &&
          info.start < ev.end &&
          info.end   > ev.start
        );
      },

      // 4) no deja mover/resize al pasado
      eventAllow(dropInfo){
        return dropInfo.start >= new Date();
      },

      // 5) selección con click–&–drag
      selectable: true,
      select(info){
        const ahora  = new Date();
        const inicio = info.start, fin = info.end;

        // bloquea cualquier rango totalmente en el pasado
        if (fin <= ahora || (inicio < ahora && inicio.toDateString() === ahora.toDateString())){
          alert('No se pueden crear citas en el pasado');
          calendar.unselect();
          return;
        }

        // abre el modal con las fechas precargadas
        $('#cita_id').val('');
        $('#paciente, #medico').val(null).trigger('change').empty();
        $('#start').val(toLocalDatetime(inicio));
        $('#end')  .val(toLocalDatetime(fin));
        $('#title').val('');
        modal.show();
      },

      // 6) clic sobre evento existente
      eventClick({event: e}){
        if (e.end < new Date()){
          alert('La cita ya está en el pasado; no se puede editar.');
          return;
        }
        // carga datos en el formulario
        $('#cita_id').val(e.id);
        $('#start').val(toLocalDatetime(e.start));
        $('#end')  .val(toLocalDatetime(e.end));
        $('#title').val(e.title);

        // pinta paciente
        $('#paciente')
          .append(new Option(
            `${e.extendedProps.paciente_nombre} — ${e.extendedProps.paciente_id}`,
            e.extendedProps.paciente_id,
            true, true
          )).trigger('change');
        $('#paciente_id').val(e.extendedProps.paciente_id);

        // pinta médico
        $('#medico')
          .append(new Option(
            e.extendedProps.medico_nombre,
            e.extendedProps.medico_id,
            true, true
          )).trigger('change');
        $('#medico_id').val(e.extendedProps.medico_id);

        modal.show();
      },

      // 7) color y tooltip
      eventDidMount(info){
        const c = info.event.extendedProps.servicio_colorx;
        if (c){
          info.el.style.backgroundColor = c;
          info.el.style.borderColor     = c;
        }
        info.el.title =
          `${info.event.extendedProps.paciente_nombre} — ${info.event.extendedProps.paciente_cedula}\n` +
          `${info.event.extendedProps.medico_nombre} · ${info.event.extendedProps.servicio}`;
      }
    }
  );

  calendar.render();

  /********************************************************************************** */

    // obtenemos las fuentes para poder actualizarlas
   /* const srcHorario   = calendar.getEventSourceById('horarioTrabajo');
    const srcBloqueos  = calendar.getEventSourceById('bloqueos');

    $('#medico').on('select2:select', e => {
      const medID = e.params.data.id;
      $('#medico_id').val(medID);

      // 1) Horario de atención → crea “eventos” recurrentes de fondo
      fetch('../horarios/controller.php', {
        method: 'POST',
        body: new URLSearchParams({ accion:'listar', medico_id: medID })
      })
      .then(r => r.json())
      .then(horas => {
        srcHorario.removeAllEvents();
        const evs = horas.map(h => ({
          daysOfWeek: [ Number(h.dia_semana) ], // 0=domingo … 6=sábado
          startTime : h.hora_inicio.slice(0,5),
          endTime   : h.hora_fin    .slice(0,5),
          display   : 'background'
        }));
        srcHorario.addEvents(evs);
      });

      // 2) Bloqueos → rangos puntuales de fondo
      fetch('../bloqueos/controller.php', {
        method: 'POST',
        body: new URLSearchParams({
          accion   : 'listar',
          medico_id: medID,
          start    : calendar.view.activeStart.toISOString(),
          end      : calendar.view.activeEnd  .toISOString()
        })
      })
      .then(r => r.json())
      .then(bloqueos => {
        srcBloqueos.removeAllEvents();
        srcBloqueos.addEvents(bloqueos);
      });
    });*/

  /********************************************************************************** */

  // obtén la fuente de bloqueos para poder actualizarla luego
    const srcBloqueosx = calendar.getEventSourceById('bloqueos');

    // cada vez que elijan un médico…
    $('#medico').on('select2:select', e => {
      const medID = e.params.data.id;
      $('#medico_id').val(medID);

      // 1) Horario de atención
      fetch('../horarios/controller.php', {
        method: 'POST',
        body: new URLSearchParams({ accion:'listar', medico_id: medID })
      })
      .then(r=>r.json())
      .then(horas => {
        // prepara el array de businessHours
        const bh = horas.map(h => ({
          daysOfWeek: [ Number(h.dia_semana) ],       // 0=domingo … 6=sábado
          startTime : h.hora_inicio.slice(0,5),      // "HH:MM"
          endTime   : h.hora_fin    .slice(0,5)
        }));
        calendar.setOption('businessHours', bh);
      });

      // 2) Bloqueos (almuerzos, reuniones…)
      fetch('../bloqueos/controller.php', {
        method: 'POST',
        body: new URLSearchParams({
          accion   : 'listar',
          medico_id: medID,
          start    : calendar.view.activeStart.toISOString(),
          end      : calendar.view.activeEnd  .toISOString()
        })
      })
      .then(r=>r.json())
      .then(bloqueos => {
        srcBloqueosx.removeAllEvents();
        srcBloqueosx.addEvents(bloqueos);
      });
    });


  // ── CRUD del formulario ──
  $('#formCita').on('submit', function(e){
    e.preventDefault();
    const $btn = $(this).find('button[type=submit]');
    $btn.prop('disabled',true).text('Guardando…');

    const fd = new FormData(this);
    fd.append('accion','guardar');

    fetch('controller_citas.php',{method:'POST',body:fd})
      .then(r=>r.json())
      .then(res=>{
        $btn.prop('disabled',false).text('Guardar');
        if (res.error) return alert(res.error);
        modal.hide();
        calendar.refetchEvents();
      })
      .catch(()=>{ $btn.prop('disabled',false).text('Guardar'); });
  });

  $('#btnEliminar').on('click', ()=>{
    const id = $('#cita_id').val();
    if (!id || !confirm('¿Eliminar esta cita?')) return;
    fetch('controller_citas.php',{
      method:'POST',
      body:new URLSearchParams({accion:'borrar',id})
    }).then(()=>{
      modal.hide();
      calendar.refetchEvents();
    });
  });

  // ── Select2 Paciente ──
  $('#paciente').select2({
    dropdownParent: $('#modalCita'),
    placeholder   : 'Busca paciente',
    minimumInputLength: 1,
    ajax:{
      url : '/equinoccio-crm/modules/pacientes/search.php',
      dataType:'json', delay:250,
      data: params => ({ q: params.term||'' }),
      processResults: d => ({
        results: d.map(p=>({
          id  : p.id,
          text: `${p.nombre} — ${p.cedula}`
        }))
      })
    }
  }).on('select2:select', e=>{
    $('#paciente_id').val(e.params.data.id);
  });

  // ── Select2 Médico ──
  $('#medico').select2({
    dropdownParent: $('#modalCita'),
    placeholder   : 'Busca médico',
    minimumInputLength: 1,
    ajax:{
      url : '/equinoccio-crm/modules/medicos/search.php',
      dataType:'json', delay:250,
      data: params => ({ q: params.term||'' }),
      processResults: d => ({
        results: d.map(m=>({
          id       : m.id,
          text     : `${m.medico} — ${m.servicio}`,
          servicio : m.servicio
        }))
      })
    }
  }).on('select2:select', e=>{
    $('#medico_id').val(e.params.data.id);
    $('#servicio_nombre').val(e.params.data.servicio);
  });

});
</script>

