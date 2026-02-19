<template>
  <div class="content">
    <div class="content2">
      <h1>Hola {{ capitalizar(nombre) }}, estas son tus citas!</h1>

      <div class="dashboard d-flex justify-content-center gap-5">
        <div class="miniContenedor">
          <p>Total citas:</p>
          <p class="number">{{ citasDelUsuario.length }}</p>
        </div>
        <div class="miniContenedor">
          <p>Citas de hoy:</p>
          <p class="number">{{ citasDeHoy.length }}</p>
        </div>
        <div class="miniContenedor">
          <p>Completadas:</p>
          <p class="number">{{ citasCompletadas.length }}</p>
        </div>
      </div>
      <div class="proximasCitas">
          <p>Próximas citas:</p>

          <div v-if="cargando" class="loading">
            Cargando citas...
          </div>
          <div v-else-if="error" class="error">
            {{ error }}
          </div>
          <div v-else-if="proximasCitas.length === 0" class="sin-citas">
            No hay citas próximas
          </div>
          <div v-for="cita in proximasCitas.slice(0, 5)" :key="cita.id" class="cita" @click="abrirVerCita(cita.id)">
            <div class="headerCita d-flex justify-content-between">
              <p>{{ cita.start_time }} - {{ cita.end_time }}</p>
              <p>{{ formatDate(cita.date) }}</p>
            </div>
            <div class="bodyCita mt-2">
              <p v-if="cita.servicios && cita.servicios.length > 0">
                <strong>Servicios:</strong> {{ cita.servicios.map(s => s.name).join(', ') }}
              </p>
              <p v-if="cita.comments">
                <strong>Notas:</strong> {{ cita.comments }}
              </p>
            </div>
          </div>
      </div>
    </div>

    <!-- Modal Ver Cita -->
    <ModalVerCita
      v-if="mostrarVerCita"
      :id="citaSeleccionadaId"
      @cerrar="mostrarVerCita = false"
      @editar="handleEditarCita"
      @eliminar="handleSolicitarEliminar"
    />

    <!-- Modal Editar Cita -->
    <ModalNuevaCita
      v-if="mostrarModalEditar"
      :fecha="fechaEditar"
      :sillones="sillonesDisponibles"
      :citas-existentes="citasExistentes"
      :initial-cita="citaParaEditar"
      @guardar="handleGuardarCita"
      @cerrar="cerrarModalEditar"
    />

    <!-- Modal Confirmación Eliminar -->
    <ModalConfirmParam
      v-if="mostrarConfirmEliminar"
      title="Eliminar Cita"
      message="¿Estás seguro de que quieres eliminar esta cita? Esta acción no se puede deshacer."
      @confirm="confirmarEliminar"
      @close="cancelarEliminar"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import ModalVerCita from '@/components/ModalVerCita.vue'
import ModalNuevaCita from '@/components/ModalNuevaCita.vue'
import ModalConfirmParam from '@/components/ModalConfirmParam.vue'

const nombre = localStorage.getItem('name')
const citas = ref([])
const cargando = ref(false)
const error = ref(null)
const mostrarVerCita = ref(false)
const mostrarModalEditar = ref(false)
const mostrarConfirmEliminar = ref(false)
const citaSeleccionadaId = ref(null)
const citaParaEditar = ref(null)
const citaIdAEliminar = ref(null)

const citasDelUsuario = computed(() => citas.value)

const citasDeHoy = computed(() => {
  const hoy = new Date().toISOString().split('T')[0]
  return citas.value.filter(c => c.date === hoy)
})

const citasCompletadas = computed(() => {
  const ahora = new Date()
  ahora.setHours(0, 0, 0, 0)
  return citas.value.filter(c => {
    const fechaCita = new Date(c.date)
    return fechaCita < ahora
  })
})

const proximasCitas = computed(() => {
  const ahora = new Date()
  return citas.value
    .filter(c => new Date(`${c.date}T${c.start_time}`) >= ahora)
    .sort((a, b) => new Date(`${a.date}T${a.start_time}`) - new Date(`${b.date}T${b.start_time}`))
})

const sillonesDisponibles = computed(() => {
  const sillones = citas.value
    .map(c => c.seat)
    .filter(s => s !== null && s !== undefined)

  if (!sillones.length) return [1, 2, 3, 4, 5]
  return [...new Set(sillones)].sort((a, b) => a - b)
})

const citasExistentes = computed(() => {
  return citas.value
    .filter(c => c.seat && c.start_time && c.end_time)
    .map(c => ({
      id: c.id,
      silla: c.seat,
      inicio: horaToDecimal(c.start_time),
      fin: horaToDecimal(c.end_time)
    }))
})

const fechaEditar = computed(() => {
  if (citaParaEditar.value?.date) return citaParaEditar.value.date
  return new Date().toISOString().split('T')[0]
})

function formatDate(dateStr) {
  const date = new Date(dateStr)
  return date.toLocaleDateString('es-ES', { year: 'numeric', month: '2-digit', day: '2-digit' })
}

function horaToDecimal(time) {
  const [h, m] = time.split(':').map(Number)
  return h + m / 60
}

const obtenerCitasPorUsuario = async () => {
  cargando.value = true
  error.value = null

  const token = localStorage.getItem('token')
  const idUsuario = localStorage.getItem('user_id')

  try {
    let res = await fetch(
      `http://localhost:8000/api/appointments?user_id=${idUsuario}`,
      {
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        }
      }
    )

    if (!res.ok) {
      res = await fetch(
        'http://localhost:8000/api/appointments',
        {
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          }
        }
      )
    }

    if (!res.ok) {
      throw new Error(`Error al obtener citas`)
    }

    const data = await res.json()

    citas.value = Array.isArray(data) ? data.map(c => ({
      id: c.id,
      date: c.date,
      seat: c.seat,
      start_time: c.start_time,
      end_time: c.end_time,
      status: c.status,
      comments: c.comments || '',
      servicios: (c.services || []).map(s => ({
        id: s.service?.id || s.service_id,
        name: s.service?.name || 'Servicio'
      }))
    })) : []

  } catch (err) {
    error.value = err.message
    console.error('Error:', err)
  } finally {
    cargando.value = false
  }
}

function capitalizar(texto) {
  return texto.charAt(0).toUpperCase() + texto.slice(1);
}

function abrirVerCita(citaId) {
  citaSeleccionadaId.value = citaId
  mostrarVerCita.value = true
}

function handleEditarCita(citaDetalle) {
  mostrarVerCita.value = false
  citaParaEditar.value = citaDetalle
  mostrarModalEditar.value = true
}

function handleGuardarCita() {
  obtenerCitasPorUsuario()
  cerrarModalEditar()
}

function cerrarModalEditar() {
  mostrarModalEditar.value = false
  citaParaEditar.value = null
}

function handleSolicitarEliminar(citaId) {
  mostrarVerCita.value = false
  citaIdAEliminar.value = citaId
  mostrarConfirmEliminar.value = true
}

async function confirmarEliminar() {
  const token = localStorage.getItem('token')
  
  try {
    const res = await fetch(`http://localhost:8000/api/appointments/${citaIdAEliminar.value}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    })

    if (!res.ok) {
      throw new Error('Error al eliminar la cita')
    }

    // Cerrar modal de confirmación
    mostrarConfirmEliminar.value = false
    citaIdAEliminar.value = null
    await obtenerCitasPorUsuario()
  } catch (err) {
    console.error('Error al eliminar:', err)
    alert('Error al eliminar la cita: ' + err.message)
    mostrarConfirmEliminar.value = false
  }
}

function cancelarEliminar() {
  mostrarConfirmEliminar.value = false
  citaIdAEliminar.value = null
}

onMounted(() => {
  obtenerCitasPorUsuario()
})
</script>

<style scoped>
.miniContenedor {
  background: #d3f0ec;
  border-radius: 10px;
  padding: 1.5rem;
  margin-top: 1rem;
  /*width: fit-content;*/
}

.miniContenedor p {
  margin-bottom: 1rem;
  font-size: 1rem;
  font-weight: bold;
  text-align: center;
}

.number {
  font-size: 2rem !important;
  color: #164e63;
}

.proximasCitas {
  margin-top: 2rem;
  background: #a8caca;
  border-radius: 10px;
  padding: 1.5rem;
}

.cita {
  background: #ffffff;
  padding: 1rem;
  border-radius: 10px;
  border-left: #394242 5px solid;
  margin-top: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.cita:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.headerCita p {
  font-weight: bold;
}

.loading,
.error,
.sin-citas {
  padding: 1rem;
  border-radius: 10px;
  text-align: center;
  margin-top: 1rem;
}

.loading {
  background: #e3f2fd;
  color: #1976d2;
}

.error {
  background: #ffebee;
  color: #c62828;
}

.sin-citas {
  background: #f5f5f5;
  color: #666;
}
</style>
