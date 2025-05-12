document.addEventListener('DOMContentLoaded', () => {
  const tabla = document.getElementById('tablaAtencion');
  let dt;

  function cargarCitas() {
    fetch('controller.php', {
      method: 'POST',
      body: new URLSearchParams({ accion: 'listar' })
    })
      .then(r => r.json())
      .then(data => {
        if (data.error) {
          alert(data.error);
          return;
        }

        dt?.destroy();
        tabla.querySelector('tbody').innerHTML = '';

        data.forEach(c => {
          const tr = document.createElement('tr');

          // Formato de hora amigable
          const hora = new Date(c.start).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

          // Estado visual
            let estado = 'No atendido';

            if (c.estado === 'ausente') {
            estado = '❌ Ausente';
            } else if (c.inicio && !c.fin) {
            // Calcular diferencia desde el inicio hasta ahora
            const inicio = new Date(c.inicio);
            const ahora = new Date();
            const difMs = ahora - inicio;
            const minutos = Math.floor(difMs / 60000);
            const segundos = Math.floor((difMs % 60000) / 1000);
            const tiempo = `${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;

            estado = `<span class="cronometro" data-inicio="${c.inicio}">🕒 ${tiempo}</span>`;
            } else if (c.inicio && c.fin) {
            estado = '✅ Finalizada';
            }


          // Acciones
          let acciones = '';
          if (!c.atencion_id) {
            acciones = `
              <button class="btn btn-success btn-sm" onclick="iniciarAtencion(${c.cita_id})">🟢 Iniciar</button>
              <button class="btn btn-danger btn-sm" onclick="marcarAusente(${c.cita_id})">❌ No asistió</button>
            `;
          } else if (c.inicio && !c.fin) {
            acciones = `<button class="btn btn-primary btn-sm" onclick="finalizarAtencion(${c.atencion_id})">✅ Finalizar</button>`;
          } else if (c.inicio && c.fin) {
            acciones = `<span class="text-muted">✔</span>`;
          }

          tr.innerHTML = `
            <td>${c.paciente}</td>
            <td>${hora}</td>
            <td>${estado}</td>
            <td>${acciones}</td>
          `;

          tabla.querySelector('tbody').appendChild(tr);
        });

        dt = new DataTable(tabla); // si usas DataTables
      });
  }

  // Acciones
  window.iniciarAtencion = (cita_id) => {
    if (!confirm("¿Desea iniciar la atención de este paciente?")) return;

    fetch('controller.php', {
      method: 'POST',
      body: new URLSearchParams({ accion: 'iniciar', cita_id })
    }).then(r => r.json()).then(() => cargarCitas());
  };

  window.finalizarAtencion = (atencion_id) => {
    if (!confirm("¿Está seguro de finalizar la atención?")) return;

    fetch('controller.php', {
      method: 'POST',
      body: new URLSearchParams({ accion: 'finalizar', atencion_id })
    })
      .then(r => r.json())
      .then(res => {
        if (res.duracion !== undefined) {
            const horaFin = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            alert(`✅ Atención finalizada a las ${horaFin}. Duración: ${res.duracion} minutos.`);
        }
        cargarCitas();
      });
  };

  window.marcarAusente = (cita_id) => {
    if (!confirm("¿Confirmar que el paciente no asistió?")) return;

    fetch('controller.php', {
      method: 'POST',
      body: new URLSearchParams({ accion: 'ausente', cita_id })
    }).then(() => cargarCitas());
  };

    // Inicial
    cargarCitas();

    setInterval(() => {
        document.querySelectorAll('.cronometro').forEach(span => {
            const inicio = new Date(span.dataset.inicio.replace(' ', 'T')); // 🔧 corrección clave
            const ahora = new Date();
            const difMs = ahora - inicio;
            const minutos = Math.floor(difMs / 60000);
            const segundos = Math.floor((difMs % 60000) / 1000);
            const tiempo = `${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;

            // Mostrar con estilo y color
            if (minutos >= 15) {
            span.textContent = `⚠️ 🕒 ${tiempo}`;
            span.style.color = 'red';
            } else if (minutos >= 10) {
            span.textContent = `🕒 ${tiempo}`;
            span.style.color = 'orange';
            } else {
            span.textContent = `🕒 ${tiempo}`;
            span.style.color = 'inherit';
            }

            span.style.fontWeight = 'bold';
        });
    }, 1000);


});
