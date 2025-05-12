document.addEventListener('DOMContentLoaded', ()=>{
  const tabla = document.querySelector('#tablaAtencion tbody');
  const modal = new bootstrap.Modal('#modalAtencion');

  function cargar(){
    fetch('controller.php', { method:'POST', body:new URLSearchParams({accion:'listar'}) })
      .then(r=>r.json()).then(data=>{
        tabla.innerHTML = '';
        data.forEach(r=>{
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${new Date(r.start).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})}</td>
            <td>${r.paciente}</td>
            <td>${r.estado||'pendiente'}</td>
            <td>
              <button class="btn btn-sm btn-primary" onclick="editar(${r.cita_id}, '${r.estado}', '${r.inicio||''}', '${r.fin||''}', '${r.diagnostico||''}', '${r.notas||''}')">✏️</button>
            </td>
          `;
          tabla.appendChild(tr);
        });
      });
  }

  window.editar = (cita_id, est, ini, fin, diag, notas) => {
    document.getElementById('cita_id').value = cita_id;
    document.getElementById('estado').value   = est;
    document.getElementById('inicio').value   = ini;
    document.getElementById('fin').value      = fin;
    document.getElementById('diagnostico').value = diag;
    document.getElementById('notas').value    = notas;
    modal.show();
  };

  document.getElementById('formAtencion').addEventListener('submit', e=>{
    e.preventDefault();
    const data = new FormData(e.target);
    data.append('accion','guardar');
    fetch('controller.php',{method:'POST',body:data})
      .then(r=>r.json()).then(res=>{
        if(res.success){
          modal.hide(); cargar();
        } else alert(res.error);
      });
  });

  cargar();
});