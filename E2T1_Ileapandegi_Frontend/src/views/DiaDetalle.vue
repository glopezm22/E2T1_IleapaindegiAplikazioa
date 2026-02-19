<template>
  <div class="content">
    <div class="content2">
      <div class="header">
        <button class="btn-volver" @click="router.push('/citas')">
          ← Volver a Citas
        </button>
        <div class="navegacion-fecha">
          <button class="btn-nav" @click="cambiarDia(-1)">←</button>
          <h1>Citas {{ diaReactivo }}/{{ mesReactivo + 1 }}/{{ anoReactivo }}</h1>
          <button class="btn-nav" @click="cambiarDia(1)">→</button>
        </div>
        <button class="btn-nueva-cita" @click="mostrarModal = true">
          + Nueva Cita
        </button>
      </div>

      <div class="contenido-centrado">
        <div class="contenedor-horario" :style="{ '--ancho-sillon': anchoSillon + 'px' }">
          <!-- scroll -->
          <div class="wrapper-scroll">
            <div class="cabecera-sillones">
              <div class="columna-horas-header"></div>
              <div class="contenedor-headers-sillones">
                <div v-for="silla in sillones" :key="silla" class="silla">
                  Silla {{ silla }}
                </div>
              </div>
            </div>

            <div class="cuerpo-horario">
              <div class="columna-horas">
                <div v-for="hora in horasTurno" :key="hora" class="slot-hora">
                  {{ formatHora(hora) }}
                </div>
              </div>

              <div class="contenedor-sillones">
                <div v-for="silla in sillones" :key="silla" class="columna-sillon">
                  <div v-for="hora in horasTurno" :key="hora" class="slot-fondo"></div>
                  
                  <div v-for="cita in citasDeSillon(silla)" :key="cita.id"
                    class="bloque-cita"
                    :style="{
                      top: cita.top + 'px',
                      height: cita.height + 'px'
                    }">
                    <div @click.stop="openVerCita(cita)" style="cursor:pointer; height: 100%; overflow-y: auto;">
                      <p class="cita-hora">{{ formatHora(cita.inicio) }} - {{ formatHora(cita.fin) }}</p>
                      
                      <!-- Enseña servicios en el slot -->
                      <div v-if="cita.servicios && cita.servicios.length > 0" class="cita-servicios">
                        <div v-for="servicio in cita.servicios" :key="servicio.id" class="servicio-tag">
                          {{ servicio.name }}
                        </div>
                      </div>
                      
                      <div v-if="cita.comentario" class="cita-comentario">
                        {{ cita.comentario }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
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

    <!-- Modal Nueva Cita -->
    <ModalNuevaCita
      v-if="mostrarModal"
      :fecha="fechaStr"
      :sillones="sillones"
      :citas-existentes="citasPosicion"
      :initial-cita="citaParaEditar"
      @guardar="handleNuevaCita"
      @cerrar="() => { mostrarModal = false; citaParaEditar = null }"
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
import { computed, ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ModalNuevaCita from '../components/ModalNuevaCita.vue'
import ModalVerCita from '../components/ModalVerCita.vue'
import ModalConfirmParam from '../components/ModalConfirmParam.vue'

const route = useRoute()
const router = useRouter()

const diaReactivo = ref(Number(route.params.dia))
const mesReactivo = ref(Number(route.params.mes))
const anoReactivo = ref(Number(route.params.ano))

const fechaSeleccionada = computed(() => new Date(anoReactivo.value, mesReactivo.value, diaReactivo.value))
const esMiercoles = computed(() => fechaSeleccionada.value.getDay() === 3)

const fechaStr = computed(() => 
  `${anoReactivo.value}-${(mesReactivo.value+1).toString().padStart(2,'0')}-${diaReactivo.value.toString().padStart(2,'0')}`
)

const slotHeight = 40
const anchoSillon = 150 
const mostrarModal = ref(false)
const mostrarVerCita = ref(false)
const mostrarConfirmEliminar = ref(false)
const citaSeleccionadaId = ref(null)
const citaParaEditar = ref(null)
const citaIdAEliminar = ref(null)

// sillones desde front tmb FALTA CAMBIAR POR ESTUDIANTES DEL DÍA
const sillonesDisponibles = ref([1, 2, 3, 4, 5])

const citas = ref([])
const cargando = ref(false)
const error = ref(null)

const sillones = computed(() => {
  if (!Array.isArray(citas.value)) return sillonesDisponibles.value
  
  const sillonesConCitas = citas.value.map(c => c.silla)
  const todosSillones = [...new Set([...sillonesDisponibles.value, ...sillonesConCitas])]
  return todosSillones.sort((a, b) => a - b)
})

const horaInicio = computed(() => esMiercoles.value ? 9 : 10)
const horaFin = computed(() => esMiercoles.value ? 12.5 : 14.5)

const horasTurno = computed(() => {
  const slots = []
  for (let h = horaInicio.value; h < horaFin.value; h += 0.25) {
    slots.push(h)
  }
  return slots
})

function formatHora(hora) {
  const h = Math.floor(hora)
  const m = Math.round((hora - h) * 60)
  return `${h}:${m.toString().padStart(2,'0')}`
}

function horaToDecimal(time) {
  const [h, m] = time.split(':').map(Number)
  return h + m / 60
}

const obtenerCitas = async () => {
  cargando.value = true
  error.value = null

  const token = localStorage.getItem('token')

  try {
    const res = await fetch(
      `http://100.25.200.198:8000/api/appointments/by-date?date=${fechaStr.value}`,
      {
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        }
      }
    )

    if (!res.ok) {
      throw new Error('Error al obtener citas')
    }

    const data = await res.json()

    const appointments = Array.isArray(data) ? data : []
    citas.value = appointments.map(c => {
      // coge los nombres del servicio
      const servicios = (c.services || []).map(appointmentService => ({
        id: appointmentService.service?.id || appointmentService.service_id,
        name: appointmentService.service?.name || 'Servicio'
      }))

      return {
        id: c.id,
        silla: c.seat,
        comentario: c.comments || '',
        inicio: horaToDecimal(c.start_time),
        fin: horaToDecimal(c.end_time),
        servicios: servicios
      }
    })


  } catch (err) {
    error.value = err.message
    console.error('Error:', err)
  } finally {
    cargando.value = false
  }
}

const citasPosicion = computed(() => {
  if (!Array.isArray(citas.value)) return []
  return citas.value.map(cita => ({
    ...cita,
    top: (cita.inicio - horaInicio.value) * slotHeight / 0.25,
    height: (cita.fin - cita.inicio) * slotHeight / 0.25
  }))
})

function citasDeSillon(silla) {
  return citasPosicion.value.filter(c => c.silla === silla)
}

function cambiarDia(incremento) {
  const nuevaFecha = new Date(anoReactivo.value, mesReactivo.value, diaReactivo.value)
  nuevaFecha.setDate(nuevaFecha.getDate() + incremento)
  
  router.push({
    name: route.name,
    params: {
      dia: nuevaFecha.getDate(),
      mes: nuevaFecha.getMonth(),
      ano: nuevaFecha.getFullYear()
    }
  })
}

function handleNuevaCita(nuevaCita) {
  obtenerCitas()
}

function openVerCita(cita) {
  
  const resolvedId = cita && cita.id !== undefined ? cita.id : cita
  citaSeleccionadaId.value = resolvedId
  mostrarVerCita.value = true
}

function handleEditarCita(citaDetalle) {
  mostrarVerCita.value = false
  citaParaEditar.value = citaDetalle
  mostrarModal.value = true
}

function handleSolicitarEliminar(citaId) {
  mostrarVerCita.value = false
  citaIdAEliminar.value = citaId
  mostrarConfirmEliminar.value = true
}

async function confirmarEliminar() {
  const token = localStorage.getItem('token')
  
  try {
    const res = await fetch(`http://100.25.200.198:8000/api/appointments/${citaIdAEliminar.value}`, {
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
    await obtenerCitas()
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

// Para cambiar cada vez que cambian los params de la ruta (al cambiar dia)
watch(() => route.params, (newParams) => {
  diaReactivo.value = Number(newParams.dia)
  mesReactivo.value = Number(newParams.mes)
  anoReactivo.value = Number(newParams.ano)
  obtenerCitas()
}, { immediate: true })

onMounted(() => {
  obtenerCitas()
})
</script>

<style scoped>
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.navegacion-fecha {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.btn-nav {
  background-color: #e0e0e0;
  border: none;
  border-radius: 8px;
  padding: 8px 16px;
  font-weight: 600;
  cursor: pointer;
  font-size: 18px;
  transition: background-color 0.2s;
}

.btn-nav:hover {
  background-color: #d0d0d0;
}
.btn-volver {
  background-color: #3b9e89;
  border: none;
  border-radius: 8px;
  padding: 8px 16px;
  font-weight: 600;
  cursor: pointer;
  font-size: 14px;
  color: white;
  transition: background-color 0.2s;
}

.btn-volver:hover {
  background-color: #7f8c8d;
}


.btn-nueva-cita {
  background-color: #82d8d8;
  border: none;
  border-radius: 8px;
  padding: 10px 20px;
  font-weight: 600;
  cursor: pointer;
  font-size: 16px;
}

.btn-nueva-cita:hover {
  background-color: #6bc5c5;
}

.contenedor-horario {
  width: 100%;
  max-width: 100%;
}

.contenido-centrado{
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 3rem;
  width: 100%;
  padding: 0 2rem;
  box-sizing: border-box;
}

.wrapper-scroll {
  width: 100%;
  overflow-x: auto;
  overflow-y: hidden;
}

.cabecera-sillones {
  display: flex;
  margin-bottom: 5px;
  min-width: fit-content;
}

.columna-horas-header {
  width: 100px;
  flex-shrink: 0;
  position: sticky;
  left: 0;
  background: white;
  z-index: 10;
}

.contenedor-headers-sillones {
  display: flex;
}

.silla {
  width: var(--ancho-sillon);
  flex-shrink: 0;
  text-align: center;
  font-weight: bold;
  border-left: 1px solid #ddd;
}

.cuerpo-horario {
  display: flex;
  border: 1px solid #ddd;
  border-radius: 20px;
  min-width: fit-content;
}

.columna-horas {
  width: 100px;
  flex-shrink: 0;
  border-right: 2px solid #999;
  background: white;
  position: sticky;
  left: 0;
  z-index: 10;
}

.slot-hora {
  height: 40px;
  border-bottom: 1px solid #eee;
  font-size: 12px;
  padding: 2px 5px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
}

.contenedor-sillones {
  display: flex;
}

.columna-sillon {
  width: var(--ancho-sillon);
  flex-shrink: 0;
  position: relative;
  border-left: 1px solid #ddd;
}

.slot-fondo {
  height: 40px;
  border-bottom: 1px solid #eee;
  box-sizing: border-box;
}

.bloque-cita {
  position: absolute;
  width: calc(100% - 8px);
  left: 4px;
  background-color: #9ddacc;
  color: #000;
  padding: 0.5rem;
  border-radius: 8px;
  font-size: 11px;
  box-sizing: border-box;
  overflow: hidden;
  font-weight: 500;
  border: 1px solid #4e615461;
  transition: transform 0.2s, box-shadow 0.2s;
}

.bloque-cita:hover {
  transform: scale(1.02);
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  z-index: 5;
}

.cita-hora {
  margin: 0 0 4px 0;
  font-weight: 700;
  font-size: 11px;
  text-align: center;
}

.cita-servicios {
  display: flex;
  flex-direction: column;
  gap: 2px;
  margin-bottom: 4px;
}

.servicio-tag {
  background: rgba(255, 255, 255, 0.7);
  padding: 2px 4px;
  border-radius: 4px;
  font-size: 9px;
  font-weight: 600;
  text-align: center;
  border: 1px solid rgba(0, 0, 0, 0.1);
}

.cita-comentario {
  font-size: 9px;
  font-style: italic;
  color: #333;
  margin-top: 4px;
  text-align: center;
}
</style>