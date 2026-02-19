<template>
  <div class="modal-backdrop" @click.self="cerrarModal">
    <div class="modal">
      <button class="cerrar-btn" @click="cerrarModal">X</button>
      <h2>{{ cita.id ? 'Editar Cita' : 'Nueva Cita' }}</h2>

      <div class="form-group">
        <label for="cliente">Cliente:</label>
        <select id="cliente" v-model="cita.client_id">
          <option value="">Selecciona un cliente</option>
          <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
            {{ cliente.name }} {{ cliente.surnames }}
          </option>
        </select>
      </div>

      <div class="form-group">
        <label for="estudiante">Estudiante:</label>
        <select id="estudiante" v-model="cita.student_id">
          <option value="">Selecciona un estudiante</option>
          <option v-for="estudiante in estudiantes" :key="estudiante.id" :value="estudiante.id">
            {{ estudiante.name }} {{ estudiante.surnames }}
          </option>
        </select>
      </div>

      <div class="form-group">
        <label for="sillon">Sillón:</label>
        <select id="sillon" v-model="cita.seat" @change="actualizarHorasDisponibles">
          <option value="">Selecciona un sillón</option>
          <option v-for="sillon in sillones" :key="sillon" :value="sillon">
            Silla {{ sillon }}
          </option>
        </select>
      </div>

      <div class="form-group">
        <label for="servicios">Servicios:</label>
        <div class="servicios-lista">
          <div v-for="servicio in servicios" :key="servicio.id" class="servicio-item">
            <input 
              type="checkbox" 
              :id="'servicio-' + servicio.id"
              :value="servicio.id"
              v-model="serviciosSeleccionados"
              @change="calcularTotales"
            />
            <label :for="'servicio-' + servicio.id">
              {{ servicio.name }} - {{ servicio.duration }}min - {{ servicio.price }}€
            </label>
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="hora-inicio">Hora Inicio:</label>
          <select id="hora-inicio" v-model="cita.start_time" @change="calcularHoraFin">
            <option value="">Selecciona hora</option>
            <option
              v-for="hora in horasDisponibles"
              :key="hora"
              :value="hora"
              :disabled="!horasValidas.includes(hora)"
              :class="{ ocupada: !horasValidas.includes(hora) }"
              :style="!horasValidas.includes(hora) ? { color: '#c0392b' } : {}"
              :title="!horasValidas.includes(hora) ? 'No disponible' : ''"
            >
              {{ hora }}
            </option>
          </select>
        </div>

        <div class="form-group">
          <label for="duracion">Duración (min):</label>
          <input 
            id="duracion" 
            v-model.number="duracionTotal" 
            type="number" 
            step="15"
            @input="calcularHoraFin"
          />
        </div>

        <div class="form-group">
          <label for="hora-fin">Hora Fin:</label>
          <input id="hora-fin" v-model="cita.end_time" type="text" />
        </div>
      </div>

      <div class="form-group">
        <label for="comentarios">Comentarios:</label>
        <textarea id="comentarios" v-model="cita.comments" rows="3"></textarea>
      </div>

      <div class="precio-total">
        <strong>Precio Total: {{ precioTotal.toFixed(2) }}€</strong>
      </div>

      <button class="guardar-btn" @click="validarYEnviar" :disabled="!formularioValido">
        {{ cita.id ? 'Actualizar Cita' : 'Guardar Cita' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted, watch } from 'vue'

const props = defineProps({
  fecha: String,
  sillones: Array,
  citasExistentes: Array,
  initialCita: Object
})

const emit = defineEmits(['guardar', 'cerrar'])

const cita = reactive({
  id: null,
  client_id: '',
  student_id: '',
  seat: '',
  date: props.fecha,
  start_time: '',
  end_time: '',
  comments: ''
})

const clientes = ref([])
const estudiantes = ref([])
const servicios = ref([])
const serviciosSeleccionados = ref([])
const duracionTotal = ref(0)
const precioTotal = ref(0)

const horasDisponibles = ref([])

// mira que no existan citas en esa hora
const horasValidas = computed(() => {
  if (!cita.seat) return []

  const citasSillon = props.citasExistentes.filter(c => c.silla === cita.seat && c.id !== cita.id)

  // mira que las horas esten en el horario del dia
  if (!horasDisponibles.value.length) actualizarHorasDisponibles()

  return horasDisponibles.value.filter(hora => {

    if (!duracionTotal.value) return true

    const inicioDecimal = horaStringToDecimal(hora)
    const finDecimal = inicioDecimal + (duracionTotal.value / 60)

    const fechaObj = props.fecha ? new Date(props.fecha) : new Date()
    const esMiercoles = fechaObj.getDay() === 3
    const finTurno = esMiercoles ? 12.5 : 14.5
    if (finDecimal > finTurno) return false

    const solapa = citasSillon.some(c => inicioDecimal < c.fin && finDecimal > c.inicio)
    return !solapa
  })
})

function actualizarHorasDisponibles() {
  if (!cita.seat) {
    horasDisponibles.value = []
    return
  }

  // mira si es miercoles
  const fechaObj = props.fecha ? new Date(props.fecha) : new Date()
  const esMiercoles = fechaObj.getDay() === 3

  const inicio = esMiercoles ? 9 : 10
  const fin = esMiercoles ? 12.5 : 14.5

  const todasLasHoras = []
  for (let h = inicio; h < fin; h += 0.25) {
    const hora = Math.floor(h)
    const min = Math.round((h - hora) * 60)
    todasLasHoras.push(`${hora.toString().padStart(2, '0')}:${min.toString().padStart(2, '0')}`)
  }

  horasDisponibles.value = todasLasHoras
}

function horaStringToDecimal(hora) {
  const [h, m] = hora.split(':').map(Number)
  return h + m / 60
}

function horaDecimalToString(decimal) {
  const h = Math.floor(decimal)
  const m = Math.round((decimal - h) * 60)
  return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}`
}

function normalizarHora(hora) {
  if (!hora) return ''
  return hora.slice(0, 5)
}

function calcularTotales() {
  const serviciosSel = servicios.value.filter(s => 
    serviciosSeleccionados.value.includes(s.id)
  )
  
  duracionTotal.value = serviciosSel.reduce((sum, s) => sum + s.duration, 0)
  precioTotal.value = serviciosSel.reduce((sum, s) => sum + parseFloat(s.price), 0)
  
  if (cita.start_time) {
    calcularHoraFin()
  }
}

function calcularHoraFin() {
  if (!cita.start_time || !duracionTotal.value) {
    cita.end_time = ''
    return
  }

  const inicioDecimal = horaStringToDecimal(cita.start_time)
  const finDecimal = inicioDecimal + (duracionTotal.value / 60)
  cita.end_time = horaDecimalToString(finDecimal)
}

// VALIDACIONES
function validarYEnviar() {
  if (!cita.start_time) {
    alert('Selecciona una hora de inicio válida')
    return
  }

  
  if (!horasValidas.value.includes(cita.start_time)) {
    alert('La hora seleccionada no es válida')
    return
  }

  // ENVIAR
  guardarCita()
}

const formularioValido = computed(() => {
  return cita.client_id && 
         cita.seat && 
         cita.start_time && 
         cita.end_time && 
         serviciosSeleccionados.value.length > 0
})

async function cargarDatos() {
  const token = localStorage.getItem('token')
  
  try {
    // Cargar clientes
    const resClientes = await fetch('http://100.25.200.198:8000/api/clients', {
      headers: { 'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}` }
    })
    clientes.value = await resClientes.json()

    // Cargar estudiantes
    const resEstudiantes = await fetch('http://100.25.200.198:8000/api/students', {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    const estudiantesJson = await resEstudiantes.json()
    estudiantes.value = estudiantesJson.data

    // Cargar servicios de pelu
    const resServicios = await fetch('http://100.25.200.198:8000/api/services', {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    servicios.value = await resServicios.json()

    // se cargan los servicios primero 
    await new Promise(resolve => setTimeout(resolve, 50))

    // si viene desde una cita para ditarm carga los datos que ya tiene
    if (props.initialCita) {
      const ic = props.initialCita
      cita.id = ic.id || null
      cita.client_id = ic.client?.id || ic.client_id || ''
      cita.student_id = ic.student?.id || ic.student_id || ''
      cita.seat = ic.seat || ''
      cita.date = ic.date || props.fecha || cita.date
      cita.start_time = normalizarHora(ic.start_time) || cita.start_time
      cita.end_time = normalizarHora(ic.end_time) || cita.end_time
      cita.comments = ic.comments || cita.comments

      // Servicios
      if (ic.services && Array.isArray(ic.services)) {
        if (ic.services.length > 0 && typeof ic.services[0] === 'object') {
          serviciosSeleccionados.value = ic.services.map(s => s.service_id || s.service?.id || s.id)
        } else {
          serviciosSeleccionados.value = ic.services
        }
      }

      actualizarHorasDisponibles()
      calcularTotales()
    }

  } catch (error) {
    console.error('Error al cargar datos:', error)
  }
}

async function guardarCita() {
  const token = localStorage.getItem('token')
  
  const payload = {
    client_id: cita.client_id,
    student_id: cita.student_id || null,
    seat: cita.seat,
    date: cita.date,
    start_time: cita.start_time,
    end_time: cita.end_time,
    comments: cita.comments,
    services: serviciosSeleccionados.value
  }

  try {
    const isEdit = !!cita.id
    const url = isEdit ? `http://100.25.200.198:8000/api/appointments/${cita.id}` : 'http://100.25.200.198:8000/api/appointments'
    const method = isEdit ? 'PUT' : 'POST'

    const res = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify(payload)
    })

    if (!res.ok) throw new Error('Error al guardar cita')

    const data = await res.json()
    emit('guardar', data)
    cerrarModal()
  } catch (error) {
    console.error('Error:', error)
    alert('Error al guardar la cita')
  }
}

function cerrarModal() {
  emit('cerrar')
}

onMounted(() => {
  cargarDatos()
  actualizarHorasDisponibles()
})

// AL CAMBIAR DE  dia se actualizan las horas x si cambia el horario
watch(() => props.fecha, (n) => {
  cita.date = n
  actualizarHorasDisponibles()
})
// sincroniza si viene desde una citaa editar
watch(() => props.initialCita, (ic) => {
  if (!ic) return 
  cita.id = ic.id || null
  cita.client_id = ic.client?.id || ic.client_id || ''
  cita.student_id = ic.student?.id || ic.student_id || ''
  cita.seat = ic.seat || ''
  cita.date = ic.date || cita.date
  cita.start_time = normalizarHora(ic.start_time) || cita.start_time
  cita.end_time = normalizarHora(ic.end_time) || cita.end_time
  cita.comments = ic.comments || cita.comments
  
  if (ic.services && Array.isArray(ic.services)) {
    if (ic.services.length > 0 && typeof ic.services[0] === 'object') {
      serviciosSeleccionados.value = ic.services.map(s => s.service_id || s.service?.id || s.id)
    } else {
      serviciosSeleccionados.value = ic.services
    }
  }
  
  actualizarHorasDisponibles()
  calcularTotales()
})
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999998;
}

.modal {
  background: white;
  border-radius: 8px;
  padding: 20px 25px;
  width: 600px;
  max-width: 95vw;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  box-shadow: 0 4px 12px rgb(0 0 0 / 0.15);
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.cerrar-btn {
  position: absolute;
  top: 10px;
  right: 12px;
  border: none;
  background: transparent;
  font-weight: bold;
  font-size: 16px;
  cursor: pointer;
}

.cerrar-btn:hover {
  color: #c0392b;
}

h2 {
  margin: 0 0 10px 0;
  padding-right: 30px;
  font-size: 24px;
  color: #2c3e50;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 10px;
}

label {
  font-weight: 600;
  font-size: 14px;
  color: #555;
}

input, select, textarea {
  padding: 8px 10px;
  border-radius: 5px;
  border: 1px solid #ccc;
  font-size: 14px;
  font-family: inherit;
}

input[readonly] {
  background-color: #f5f5f5;
}

.servicios-lista {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 200px;
  overflow-y: auto;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.servicio-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.servicio-item input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.servicio-item label {
  cursor: pointer;
  font-weight: normal;
  flex: 1;
}

.precio-total {
  text-align: right;
  font-size: 18px;
  color: #2c3e50;
  padding: 10px 0;
  border-top: 2px solid #ddd;
}

.guardar-btn {
  margin-top: 10px;
  background-color: #82d8d8;
  border: none;
  border-radius: 8px;
  padding: 10px 20px;
  font-weight: 600;
  cursor: pointer;
  font-size: 16px;
  transition: background-color 0.2s;
}

.guardar-btn:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.guardar-btn:hover:not(:disabled) {
  background-color: #6bc5c5;
}

option.ocupada {
  color: #c0392b;
  font-weight: 600;
}
</style>