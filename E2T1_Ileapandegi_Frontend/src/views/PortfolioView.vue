<template>
  <div class="content">
    <div class="content2">
      <h1>{{ t('portfolio.title') }}</h1>

      <!-- Filtros -->
      <div class="filtros">
        <div class="filtro-grupo">
          <label>{{ t('portfolio.filterStudent') }}:</label>
          <select v-model="filtroAlumno" class="filtro-select">
            <option value="">{{ t('portfolio.all') }}</option>
            <option v-for="alumno in alumnos" :key="alumno.id" :value="alumno.id">
              {{ alumno.name }} {{ alumno.surnames }}
            </option>
          </select>
        </div>

        <div class="filtro-grupo">
          <label>{{ t('portfolio.filterCategory') }}:</label>
          <select v-model="filtroCategoria" class="filtro-select">
            <option value="">{{ t('portfolio.all') }}</option>
            <option v-for="cat in categorias" :key="cat.value" :value="cat.value">
              {{ cat.label }}
            </option>
          </select>
        </div>

        <button class="btn-agregar" @click="abrirModalSubida">
          + {{ t('portfolio.upload') }}
        </button>
      </div>

      <!-- Grid de entradas -->
      <div v-if="cargando" class="estado-info">{{ t('portfolio.loading') }}</div>
      <div v-else-if="entradasFiltradas.length === 0" class="estado-info">
        {{ t('portfolio.empty') }}
      </div>
      <div v-else class="portfolio-grid">
        <div
          v-for="entrada in entradasFiltradas"
          :key="entrada.id"
          class="portfolio-card"
          @click="abrirComparador(entrada)"
        >
          <div class="card-fotos">
            <div class="foto-wrapper">
              <span class="foto-label">{{ t('portfolio.before') }}</span>
              <img :src="entrada.photo_before" :alt="t('portfolio.before')" class="foto-miniatura" />
            </div>
            <div class="foto-wrapper">
              <span class="foto-label">{{ t('portfolio.after') }}</span>
              <img :src="entrada.photo_after" :alt="t('portfolio.after')" class="foto-miniatura" />
            </div>
          </div>
          <div class="card-info">
            <span class="card-categoria badge-categoria" :class="`cat-${entrada.category}`">
              {{ entrada.category }}
            </span>
            <span class="card-alumno" v-if="entrada.student">
              {{ entrada.student.name }} {{ entrada.student.surnames }}
            </span>
            <p v-if="entrada.notes" class="card-notas">{{ entrada.notes }}</p>
          </div>
          <button
            class="btn-eliminar-card"
            @click.stop="solicitarEliminar(entrada)"
            :title="t('portfolio.delete')"
          >
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Modal: Subida de fotos -->
    <div v-if="mostrarModalSubida" class="portfolio-overlay" @click.self="cerrarModalSubida">
      <div class="portfolio-modal">
        <button class="modal-cerrar" @click="cerrarModalSubida">&times;</button>
        <h3>{{ t('portfolio.uploadTitle') }}</h3>

        <div class="form-group">
          <label>{{ t('portfolio.student') }}:</label>
          <select v-model="formulario.student_id" class="form-control">
            <option value="">-- {{ t('portfolio.selectStudent') }} --</option>
            <option v-for="a in alumnos" :key="a.id" :value="a.id">
              {{ a.name }} {{ a.surnames }}
            </option>
          </select>
          <p v-if="errores.student_id" class="error-msg">{{ errores.student_id }}</p>
        </div>

        <div class="form-group">
          <label>{{ t('portfolio.category') }}:</label>
          <select v-model="formulario.category" class="form-control">
            <option value="">-- {{ t('portfolio.selectCategory') }} --</option>
            <option v-for="cat in categorias" :key="cat.value" :value="cat.value">
              {{ cat.label }}
            </option>
          </select>
          <p v-if="errores.category" class="error-msg">{{ errores.category }}</p>
        </div>

        <div class="form-group">
          <label>{{ t('portfolio.photoBefore') }}:</label>
          <input type="file" accept="image/*" @change="(e) => seleccionarFoto(e, 'before')" />
          <p v-if="errores.photo_before" class="error-msg">{{ errores.photo_before }}</p>
        </div>

        <div class="form-group">
          <label>{{ t('portfolio.photoAfter') }}:</label>
          <input type="file" accept="image/*" @change="(e) => seleccionarFoto(e, 'after')" />
          <p v-if="errores.photo_after" class="error-msg">{{ errores.photo_after }}</p>
        </div>

        <div class="form-group">
          <label>{{ t('portfolio.notes') }}:</label>
          <textarea v-model="formulario.notes" rows="3" class="form-control" maxlength="500"></textarea>
        </div>

        <button class="btn-guardar" @click="guardarEntrada" :disabled="guardando">
          {{ guardando ? t('portfolio.saving') : t('portfolio.save') }}
        </button>
      </div>
    </div>

    <!-- Modal: Comparador antes/después -->
    <div v-if="entradaComparador" class="portfolio-overlay" @click.self="entradaComparador = null">
      <div class="comparador-modal">
        <button class="modal-cerrar" @click="entradaComparador = null">&times;</button>
        <h3 class="comparador-titulo">
          {{ entradaComparador.student?.name }} — {{ entradaComparador.category }}
        </h3>

        <div class="comparador-fotos">
          <div class="comparador-foto">
            <span class="foto-label">{{ t('portfolio.before') }}</span>
            <img :src="entradaComparador.photo_before" :alt="t('portfolio.before')" />
          </div>
          <div class="comparador-separador">&#8646;</div>
          <div class="comparador-foto">
            <span class="foto-label">{{ t('portfolio.after') }}</span>
            <img :src="entradaComparador.photo_after" :alt="t('portfolio.after')" />
          </div>
        </div>

        <p v-if="entradaComparador.notes" class="comparador-notas">
          {{ entradaComparador.notes }}
        </p>
        <p class="comparador-fecha">
          {{ new Date(entradaComparador.created_at).toLocaleDateString() }}
        </p>
      </div>
    </div>

    <!-- Modal: Confirmación eliminación -->
    <div v-if="entradaAEliminar" class="portfolio-overlay" @click.self="entradaAEliminar = null">
      <div class="confirm-modal">
        <p>{{ t('portfolio.confirmDelete') }}</p>
        <div class="confirm-botones">
          <button class="btn-cancelar" @click="entradaAEliminar = null">{{ t('portfolio.cancel') }}</button>
          <button class="btn-eliminar-confirm" @click="eliminarEntrada">{{ t('portfolio.delete') }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

const entradas = ref([])
const alumnos = ref([])
const cargando = ref(false)
const guardando = ref(false)
const mostrarModalSubida = ref(false)
const entradaComparador = ref(null)
const entradaAEliminar = ref(null)

const filtroAlumno = ref('')
const filtroCategoria = ref('')

const categorias = [
  { value: 'cortes', label: 'Cortes' },
  { value: 'colores', label: 'Colores' },
  { value: 'mechas', label: 'Mechas' },
  { value: 'tratamientos', label: 'Tratamientos' },
]

const formulario = ref({
  student_id: '',
  category: '',
  photo_before: null,
  photo_after: null,
  notes: '',
})

const errores = ref({
  student_id: '',
  category: '',
  photo_before: '',
  photo_after: '',
})

const entradasFiltradas = computed(() => {
  return entradas.value.filter((e) => {
    const porAlumno = filtroAlumno.value ? e.student_id == filtroAlumno.value : true
    const porCategoria = filtroCategoria.value ? e.category === filtroCategoria.value : true
    return porAlumno && porCategoria
  })
})

function token() {
  return localStorage.getItem('token')
}

async function cargarEntradas() {
  cargando.value = true
  try {
    const res = await fetch(`${API_URL}/portfolios`, {
      headers: { Authorization: `Bearer ${token()}` },
    })
    if (!res.ok) throw new Error('Error al cargar portfolio')
    entradas.value = await res.json()
  } catch (err) {
    console.error(err)
  } finally {
    cargando.value = false
  }
}

async function cargarAlumnos() {
  try {
    const res = await fetch(`${API_URL}/students`, {
      headers: { Authorization: `Bearer ${token()}` },
    })
    if (!res.ok) throw new Error('Error al cargar alumnos')
    const data = await res.json()
    alumnos.value = data.data ?? data
  } catch (err) {
    console.error(err)
  }
}

function abrirModalSubida() {
  formulario.value = { student_id: '', category: '', photo_before: null, photo_after: null, notes: '' }
  errores.value = { student_id: '', category: '', photo_before: '', photo_after: '' }
  mostrarModalSubida.value = true
}

function cerrarModalSubida() {
  mostrarModalSubida.value = false
}

function seleccionarFoto(event, tipo) {
  const file = event.target.files[0]
  if (tipo === 'before') formulario.value.photo_before = file
  else formulario.value.photo_after = file
}

function validarFormulario() {
  errores.value = { student_id: '', category: '', photo_before: '', photo_after: '' }
  let valido = true

  if (!formulario.value.student_id) {
    errores.value.student_id = t('portfolio.errors.studentRequired')
    valido = false
  }
  if (!formulario.value.category) {
    errores.value.category = t('portfolio.errors.categoryRequired')
    valido = false
  }
  if (!formulario.value.photo_before) {
    errores.value.photo_before = t('portfolio.errors.photoRequired')
    valido = false
  }
  if (!formulario.value.photo_after) {
    errores.value.photo_after = t('portfolio.errors.photoRequired')
    valido = false
  }
  return valido
}

async function guardarEntrada() {
  if (!validarFormulario()) return
  guardando.value = true

  const body = new FormData()
  body.append('student_id', formulario.value.student_id)
  body.append('category', formulario.value.category)
  body.append('photo_before', formulario.value.photo_before)
  body.append('photo_after', formulario.value.photo_after)
  if (formulario.value.notes) body.append('notes', formulario.value.notes)

  try {
    const res = await fetch(`${API_URL}/portfolios`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token()}` },
      body,
    })
    if (!res.ok) throw new Error('Error al guardar entrada')
    const nueva = await res.json()
    entradas.value.unshift(nueva)
    cerrarModalSubida()
  } catch (err) {
    console.error(err)
  } finally {
    guardando.value = false
  }
}

function abrirComparador(entrada) {
  entradaComparador.value = entrada
}

function solicitarEliminar(entrada) {
  entradaAEliminar.value = entrada
}

async function eliminarEntrada() {
  if (!entradaAEliminar.value) return
  try {
    const res = await fetch(`${API_URL}/portfolios/${entradaAEliminar.value.id}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${token()}` },
    })
    if (!res.ok) throw new Error('Error al eliminar entrada')
    entradas.value = entradas.value.filter((e) => e.id !== entradaAEliminar.value.id)
  } catch (err) {
    console.error(err)
  } finally {
    entradaAEliminar.value = null
  }
}

onMounted(() => {
  cargarEntradas()
  cargarAlumnos()
})
</script>

<style scoped>
.content {
  flex: 1;
  padding: 2rem;
}

.content2 {
  max-width: 1200px;
}

h1 {
  font-size: 1.8rem;
  margin-bottom: 1.5rem;
}

/* Filtros */
.filtros {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  align-items: flex-end;
  margin-bottom: 1.5rem;
}

.filtro-grupo {
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 160px;
}

.filtro-select {
  padding: 6px 10px;
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 14px;
}

.btn-agregar {
  background-color: #9ce0db;
  color: black;
  border: none;
  border-radius: 10px;
  font-weight: bold;
  padding: 8px 18px;
  cursor: pointer;
  white-space: nowrap;
}

.btn-agregar:hover {
  background-color: #7dcfca;
}

/* Estado vacío/cargando */
.estado-info {
  color: gray;
  text-align: center;
  margin-top: 3rem;
  font-size: 1.1rem;
}

/* Grid de entradas */
.portfolio-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 1.25rem;
}

.portfolio-card {
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  cursor: pointer;
  position: relative;
  transition: transform 0.2s, box-shadow 0.2s;
}

.portfolio-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
}

.card-fotos {
  display: flex;
}

.foto-wrapper {
  flex: 1;
  position: relative;
}

.foto-label {
  position: absolute;
  top: 4px;
  left: 6px;
  background: rgba(0, 0, 0, 0.55);
  color: white;
  font-size: 0.7rem;
  padding: 2px 6px;
  border-radius: 4px;
  z-index: 1;
  text-transform: uppercase;
  font-weight: 600;
}

.foto-miniatura {
  width: 100%;
  height: 140px;
  object-fit: cover;
  display: block;
}

.card-info {
  padding: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.badge-categoria {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  padding: 2px 8px;
  border-radius: 20px;
  align-self: flex-start;
}

.cat-cortes { background: #b3d9ff; color: #003d6b; }
.cat-colores { background: #ffd6b3; color: #6b2a00; }
.cat-mechas { background: #d4f4dd; color: #1a5c29; }
.cat-tratamientos { background: #e8d4f4; color: #4a1c6b; }

.card-alumno {
  font-size: 0.9rem;
  font-weight: 600;
  color: #333;
}

.card-notas {
  font-size: 0.8rem;
  color: #666;
  margin: 0;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.btn-eliminar-card {
  position: absolute;
  top: 6px;
  right: 6px;
  background: rgba(235, 179, 179, 0.9);
  border: none;
  border-radius: 50%;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 0.85rem;
  color: #8b0000;
}

.btn-eliminar-card:hover {
  background: #ebb3b3;
}

/* Overlay genérico para modales */
.portfolio-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1060;
  padding: 1rem;
}

/* Modal de subida */
.portfolio-modal {
  background: white;
  border-radius: 10px;
  padding: 1.5rem;
  width: min(420px, 95vw);
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.modal-cerrar {
  position: absolute;
  top: 0.5rem;
  right: 0.75rem;
  background: transparent;
  border: none;
  font-size: 1.5rem;
  font-weight: bold;
  cursor: pointer;
  color: #555;
  line-height: 1;
  padding: 0;
}

.modal-cerrar:hover {
  color: #000;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.form-control {
  padding: 6px 8px;
  border-radius: 5px;
  border: 1px solid #ccc;
  font-size: 14px;
}

.error-msg {
  color: red;
  font-size: 0.85em;
  margin: 2px 0;
}

.btn-guardar {
  background-color: #9ce0db;
  border: none;
  border-radius: 8px;
  padding: 10px;
  font-weight: 700;
  cursor: pointer;
  margin-top: 8px;
  transition: background-color 0.2s;
}

.btn-guardar:hover:not(:disabled) {
  background-color: #7dcfca;
}

.btn-guardar:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Comparador antes/después */
.comparador-modal {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  width: min(800px, 95vw);
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.25);
}

.comparador-titulo {
  text-align: center;
  margin-bottom: 1rem;
  text-transform: capitalize;
}

.comparador-fotos {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.comparador-foto {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}

.comparador-foto .foto-label {
  position: static;
  background: #154e68;
  color: white;
  font-size: 0.75rem;
  padding: 3px 10px;
  border-radius: 20px;
  font-weight: 700;
  text-transform: uppercase;
}

.comparador-foto img {
  width: 100%;
  max-height: 360px;
  object-fit: contain;
  border-radius: 8px;
  border: 1px solid #ddd;
}

.comparador-separador {
  font-size: 2rem;
  color: #aaa;
  flex-shrink: 0;
}

.comparador-notas {
  margin-top: 1rem;
  color: #555;
  font-style: italic;
  text-align: center;
}

.comparador-fecha {
  text-align: center;
  font-size: 0.8rem;
  color: #aaa;
  margin-top: 4px;
}

/* Modal de confirmación */
.confirm-modal {
  background: white;
  border-radius: 10px;
  padding: 2rem;
  width: min(360px, 95vw);
  text-align: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.confirm-botones {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-top: 1.5rem;
}

.btn-cancelar {
  background: #eee;
  border: none;
  border-radius: 8px;
  padding: 8px 20px;
  cursor: pointer;
  font-weight: 600;
}

.btn-eliminar-confirm {
  background: #ebb3b3;
  border: none;
  border-radius: 8px;
  padding: 8px 20px;
  cursor: pointer;
  font-weight: 600;
}

.btn-eliminar-confirm:hover {
  background: #d88f8f;
}

@media (max-width: 600px) {
  .content {
    padding: 1rem;
  }

  .comparador-fotos {
    flex-direction: column;
  }

  .comparador-separador {
    transform: rotate(90deg);
  }
}
</style>
