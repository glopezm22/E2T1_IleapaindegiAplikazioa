<template>
  <div class="content">
    <div class="content2">

      <h1>Gestión de Inventario</h1>

      <!-- Tabs -->
      <div class="tabs">
        <button :class="{ active: activeTab === 'material' }" @click="activeTab = 'material'">
          Material
        </button>

        <button :class="{ active: activeTab === 'equipamientos' }" @click="activeTab = 'equipamientos'">
          Equipamientos
        </button>
      </div>

      <div v-if="activeTab === 'material'">
        <!-- Header -->
        <div class="header">
          <h1>
            {{ materialView === 'items' ? 'Material' : 'Categorías de Material' }}
          </h1>

          <div style="display:flex; gap:0.5rem">
            <button v-if="materialView === 'items'" class="btn-add" @click="openModal('add')">
              + Agregar material
            </button>

            <button v-if="materialView === 'items'" class="btn-add" style="background:#455a64"
              @click="materialView = 'categories'">
              📂 Gestionar categorías
            </button>

            <button v-if="materialView === 'categories'" class="btn-add" style="background:#455a64"
              @click="materialView = 'items'">
              ← Volver a materiales
            </button>
          </div>
        </div>

        <div v-if="materialView === 'items'">

          <!-- Buscador -->
          <input v-model="search" type="text" placeholder="Buscar por nombre, lote o marca..." class="search-input" />

          <!-- Tabla -->
          <table class="inventario-table">
            <thead>
              <tr>
                <th>Lote</th>
                <th>Nombre</th>
                <th>Cantidad</th>
                <th>Categoría</th>
                <th>Marca</th>
                <th>Fecha cad</th>
                <th>Descripción</th>
                <th>Acciones</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="item in paginatedData" :key="item.id">
                <td>{{ item.batch }}</td>
                <td>{{ item.name }}</td>
                <td :class="{ lowStock: item.stock <= item.min_stock }">{{ item.stock }}</td>
                <td>{{ item.category.name }}</td>
                <td>{{ item.brand }}</td>
                <td>{{ formatDate(item.expiration_date) }}</td>
                <td class="description">{{ item.description }}</td>
                <td class="actions">
                  <button class="btn-edit" @click="openModal('edit', item)">✏️</button>
                  <button class="btn-delete" @click="openModal('delete', item)">🗑️</button>
                </td>
              </tr>

              <tr v-if="paginatedData.length === 0">
                <td colspan="8" class="empty">No hay resultados</td>
              </tr>
            </tbody>
          </table>

          <!-- Paginación -->
          <div class="pagination">
            <button @click="prevPage" :disabled="currentPage === 1">«</button>
            <span>Página {{ currentPage }} de {{ totalPages }}</span>
            <button @click="nextPage" :disabled="currentPage === totalPages">»</button>
          </div>

        </div>

        <div v-if="materialView === 'categories'">
          <button class="btn-add" @click="openCategoryModal('add')">
            + Agregar categoría
          </button>

          <input v-model="categorySearch" type="text" placeholder="Buscar categoría..." class="search-input" />

          <table class="inventario-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Acciones</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="cat in filteredCategories" :key="cat.id">
                <td>{{ cat.id }}</td>
                <td>{{ cat.name }}</td>
                <td class="actions">
                  <button class="btn-edit" @click="openCategoryModal('edit', cat)">✏️</button>
                  <button class="btn-delete" @click="openCategoryModal('delete', cat)">🗑️</button>
                </td>
              </tr>

              <tr v-if="filteredCategories.length === 0">
                <td colspan="3" class="empty">No hay categorías</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>




      <!-- ===== EQUIPAMIENTOS ===== -->
      <!-- ===== EQUIPAMIENTOS ===== -->
      <div v-if="activeTab === 'equipamientos'">

        <div class="header">
          <h1>Equipamientos</h1>
          <button class="btn-add" @click="openEquipmentModal('add')">
            + Agregar equipamiento
          </button>
        </div>

        <!-- Selector sub-tabs para Equipamientos -->
        <div class="equipment-subtabs">
          <button :class="{ active: equipmentView === 'lista' }" @click="equipmentView = 'lista'">Lista
            Completa</button>
          <button :class="{ active: equipmentView === 'gestion' }" @click="equipmentView = 'gestion'">Gestión de
            Equipamientos</button>
        </div>

        <!-- Vista Gestión con dos columnas -->
        <div v-if="equipmentView === 'gestion'" class="equipment-columns">
          <!-- Equipamientos disponibles -->
          <div class="column">
            <h2>Disponibles</h2>
            <table class="inventario-table">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Marca</th>
                  <th>Etiqueta</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in availableEquipments" :key="item.id">
                  <td>{{ item.name }}</td>
                  <td>{{ item.brand }}</td>
                  <td>{{ item.label }}</td>
                  <td class="actions">
                    <button class="btn-assign" @click="assignEquipment(item)">
                      Asignar
                    </button>
                  </td>
                </tr>
                <tr v-if="availableEquipments.length === 0">
                  <td colspan="4" class="empty">No hay equipamientos disponibles</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Equipamientos ocupados -->
          <div class="column">
            <h2>Ocupados</h2>
            <table class="inventario-table">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Marca</th>
                  <th>Etiqueta</th>
                  <th>Responsable</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in occupiedEquipments" :key="item.student_equipment_id">
                  <td>{{ item.name }}</td>
                  <td>{{ item.brand }}</td>
                  <td>{{ item.label }}</td>
                  <td>{{ item.responsible }}</td>
                  <td class="actions">
                    <button class="btn-assign" @click="finishEquipmentUsage(item.student_equipment_id)">
                      Finalizar uso
                    </button>
                  </td>
                </tr>
                <tr v-if="occupiedEquipments.length === 0">
                  <td colspan="5" class="empty">No hay equipamientos ocupados</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Vista Lista Completa -->
        <div v-if="equipmentView === 'lista'">
          <input v-model="equipmentSearch" type="text" placeholder="Buscar por código, nombre o responsable..."
            class="search-input" />

          <table class="inventario-table">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Marca</th>
                <th>Etiqueta</th>
                <th>Descripción</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in equipmentPaginatedData" :key="item.id">
                <td>{{ item.name }}</td>
                <td>{{ item.brand }}</td>
                <td>{{ item.label }}</td>
                <td>{{ item.description }}</td>
                <td class="actions">
                  <button class="btn-edit" @click="openEquipmentModal('edit', item)">✏️</button>
                  <button class="btn-delete" @click="openEquipmentModal('delete', item)">🗑️</button>
                </td>
              </tr>
              <tr v-if="filteredEquipments.length === 0">
                <td colspan="5" class="empty">No hay resultados</td>
              </tr>
            </tbody>
          </table>

          <div class="pagination">
            <button @click="equipmentPrevPage" :disabled="equipmentCurrentPage === 1">«</button>
            <span>Página {{ equipmentCurrentPage }} de {{ equipmentTotalPages }}</span>
            <button @click="equipmentNextPage" :disabled="equipmentCurrentPage === equipmentTotalPages">»</button>
          </div>
        </div>
      </div>


    </div>


    <!-- ===== MODAL ===== -->
    <InventarioAddModal v-if="showModal && modalType === 'add'" @close="closeModal" @submit="submitForm" />

    <InventarioEditModal v-if="showModal && modalType === 'edit'" :item="form" @close="closeModal"
      @submit="submitForm" />

    <InventarioDeleteModal v-if="showModal && modalType === 'delete'" :item="form" @close="closeModal"
      @confirm="deleteItem" />

    <EquipmentAddModal v-if="showEquipmentModal && equipmentModalType === 'add'" @close="closeEquipmentModal"
      @submit="submitEquipment" />

    <EquipmentEditModal v-if="showEquipmentModal && equipmentModalType === 'edit'" :item="equipmentForm"
      @close="closeEquipmentModal" @submit="submitEquipment" />

    <EquipmentDeleteModal v-if="showEquipmentModal && equipmentModalType === 'delete'" :item="equipmentForm"
      @close="closeEquipmentModal" @confirm="deleteEquipment" />

    <CategoryAddModal v-if="showCategoryModal && categoryModalType === 'add'" @close="closeCategoryModal"
      @submit="submitCategory" />

    <CategoryEditModal v-if="showCategoryModal && categoryModalType === 'edit'" :item="categoryForm"
      @close="closeCategoryModal" @submit="submitCategory" />

    <CategoryDeleteModal v-if="showCategoryModal && categoryModalType === 'delete'" :item="categoryForm"
      @close="closeCategoryModal" @confirm="deleteCategory" />

    <AssignEquipmentModal v-if="showAssignModal" :item="assignItem" @close="showAssignModal = false"
      @confirm="confirmAssignEquipment" />

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import InventarioAddModal from '../components/InventarioAddModal.vue'
import InventarioEditModal from '../components/InventarioEditModal.vue'
import InventarioDeleteModal from '../components/InventarioDeleteModal.vue'
/* ===== Variables ===== */
const inventario = ref([])
const search = ref('')
const currentPage = ref(1)
const perPage = 7

const showModal = ref(false)
const modalType = ref('') // add | edit | delete
const form = ref({})



/* ===== API ===== */
const token = localStorage.getItem('token')
const apiUrl = 'http://localhost:8000/api/consumables'

/* ===== Tabs ===== */
const activeTab = ref('material')

/* ===== Equipamientos ===== */
const equipments = ref([])
const equipmentSearch = ref('')

const equipmentForm = ref({})
const equipmentModalType = ref('')
const showEquipmentModal = ref(false)

const equipmentApiUrl = 'http://localhost:8000/api/equipments'

const fetchEquipments = async () => {
  const res = await fetch(equipmentApiUrl, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  })
  equipments.value = await res.json()
}

const filteredEquipments = computed(() =>
  equipments.value.filter(e =>
    (e.name ?? '').toLowerCase().includes(equipmentSearch.value.toLowerCase()) ||
    (e.brand ?? '').toLowerCase().includes(equipmentSearch.value.toLowerCase()) ||
    (e.label ?? '').toLowerCase().includes(equipmentSearch.value.toLowerCase()) ||
    (e.description ?? '').toLowerCase().includes(equipmentSearch.value.toLowerCase())
  )
)

const openEquipmentModal = (type, item = {}) => {
  equipmentModalType.value = type
  equipmentForm.value = { ...item }
  showEquipmentModal.value = true
}

const closeEquipmentModal = () => {
  showEquipmentModal.value = false
  equipmentModalType.value = ''
}


const submitEquipment = async (data) => {
  const method = equipmentModalType.value === 'add' ? 'POST' : 'PUT'
  const url = equipmentModalType.value === 'add'
    ? equipmentApiUrl
    : `${equipmentApiUrl}/${data.id}`
  await fetch(url, {
    method,
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  })

  await fetchEquipments()
  closeEquipmentModal()
}

const equipmentCurrentPage = ref(1)
const equipmentPerPage = 7

const equipmentTotalPages = computed(() =>
  Math.ceil(filteredEquipments.value.length / equipmentPerPage)
)

const equipmentPaginatedData = computed(() => {
  const start = (equipmentCurrentPage.value - 1) * equipmentPerPage
  return filteredEquipments.value.slice(start, start + equipmentPerPage)
})

const equipmentNextPage = () => {
  if (equipmentCurrentPage.value < equipmentTotalPages.value)
    equipmentCurrentPage.value++
}

const equipmentPrevPage = () => {
  if (equipmentCurrentPage.value > 1)
    equipmentCurrentPage.value--
}

watch(equipmentSearch, () => {
  equipmentCurrentPage.value = 1
})


import EquipmentAddModal from '../components/EquipmentAddModal.vue'
import EquipmentEditModal from '../components/EquipmentEditModal.vue'
import EquipmentDeleteModal from '../components/EquipmentDeleteModal.vue'


const equipmentView = ref('lista') // o 'gestion', según la vista inicial que quieras

// Equipamientos disponibles = equipments sin student_equipment activo
const availableEquipments = computed(() =>
  equipments.value.filter(e =>
    !studentEquipments.value.some(se => se.equipment_id === e.id)
  )
)

const occupiedEquipments = computed(() =>
  studentEquipments.value.map(se => ({
    student_equipment_id: se.id,
    equipment_id: se.equipment.id,
    name: se.equipment.name,
    brand: se.equipment.brand,
    label: se.equipment.label,
    responsible: `${se.student.name} ${se.student.surnames}`,
    start_datetime: se.start_datetime
  }))
)


const finishEquipmentUsage = async (occupiedItemId) => {
  console.log("Se finalizaaa" + occupiedItemId);
  await fetch(`http://localhost:8000/api/student-equipments/${occupiedItemId}/finish`, {
    method: 'PUT',
    headers: { 'Authorization': `Bearer ${token}` }
  })

  await fetchAllEquipments()
}
watch(activeTab, (tab) => {
  if (tab === 'equipamientos') {
    fetchAllEquipments()
  }
})

const studentEquipments = ref([])

const fetchStudentEquipmentsActive = async () => {
  const res = await fetch('http://localhost:8000/api/student-equipments/active', {
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  })
  studentEquipments.value = await res.json()
}


const fetchAllEquipments = async () => {
  await Promise.all([
    fetchEquipments(),
    fetchStudentEquipmentsActive()
  ])
}



const assignEquipment = (item) => {
  openAssignModal(item)
}

const confirmAssignEquipment = async (payload) => {
  const token = localStorage.getItem('token')

  if (!payload.student_id) {
    // Finalizar uso
    await fetch(`http://localhost:8000/api/student-equipments/${payload.id}/finish`, { method: 'PUT', headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' } })
  } else {
    // Asignar equipamiento
    await fetch('http://localhost:8000/api/student-equipments', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        student_id: payload.student_id,
        equipment_id: payload.id,
        start_datetime: new Date().toISOString().slice(0, 19).replace('T', ' ')
      })

    })
  }
  await fetchAllEquipments()
}


const deleteEquipment = async () => {
  await fetch(`${equipmentApiUrl}/${equipmentForm.value.id}`, {
    method: 'DELETE',
    headers: {
      'Authorization': `Bearer ${token}`
    }
  })

  await fetchEquipments()
  closeEquipmentModal()
}

const fetchInventario = async () => {
  const res = await fetch(apiUrl, {
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    }
  })
  inventario.value = await res.json()
}

/* ===== Buscar ===== */
const filteredData = computed(() =>
  inventario.value.filter(item =>
    item.name.toLowerCase().includes(search.value.toLowerCase()) ||
    item.batch.toLowerCase().includes(search.value.toLowerCase()) ||
    item.brand.toLowerCase().includes(search.value.toLowerCase())
  )
)

/* ===== Paginación ===== */
const totalPages = computed(() => Math.ceil(filteredData.value.length / perPage))
const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredData.value.slice(start, start + perPage)
})
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }
const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }

watch(search, () => currentPage.value = 1)
watch(activeTab, (tab) => {
  if (tab === 'equipamientos') {
    fetchEquipments()
  }
})

/* ===== Utils ===== */
const formatDate = (date) => date ? new Date(date).toLocaleDateString('es-ES') : '—'

/* ===== MODAL ===== */
const openModal = (type, item = {}) => {
  modalType.value = type
  form.value = { ...item } // clonar el objeto para no mutar el original
  showModal.value = true
}


const closeModal = () => {
  showModal.value = false
  modalType.value = ''   // esto es lo que realmente desmonta el modal
}

/* ===== CRUD ===== */
const submitForm = async (data) => {
  form.value = data

  const method = modalType.value === 'add' ? 'POST' : 'PUT'
  const url = modalType.value === 'add'
    ? apiUrl
    : `${apiUrl}/${form.value.id}`

  await fetch(url, {
    method,
    headers: {
      'Content-Type': 'application/json',
      Authorization: `Bearer ${token}`
    },
    body: JSON.stringify(form.value)
  })

  await fetchInventario()
  closeModal()
}


const deleteItem = async () => {
  try {
    const res = await fetch(`${apiUrl}/${form.value.id}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${token}` }
    })

    if (!res.ok) throw new Error('Error al eliminar')
    await fetchInventario()
    closeModal()
  } catch (error) {
    alert(error.message)
  }
}

onMounted(fetchInventario)

import CategoryAddModal from '../components/CategoryAddModal.vue'
import CategoryEditModal from '../components/CategoryEditModal.vue'
import CategoryDeleteModal from '../components/CategoryDeleteModal.vue'


const materialView = ref('items')
// 'items' | 'categories'

const categories = ref([])
const categorySearch = ref('')
const showCategoryModal = ref(false)
const categoryModalType = ref('')
const categoryForm = ref({})

const categoryApiUrl = 'http://localhost:8000/api/categories'

const fetchCategories = async () => {
  const res = await fetch(categoryApiUrl, {
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  })
  categories.value = await res.json()
}

const filteredCategories = computed(() =>
  categories.value.filter(c =>
    c.name.toLowerCase().includes(categorySearch.value.toLowerCase())
  )
)

watch(materialView, v => {
  if (v === 'categories') fetchCategories()
})

const openCategoryModal = (type, item = {}) => {
  categoryModalType.value = type
  categoryForm.value = { ...item }
  showCategoryModal.value = true
}

const closeCategoryModal = () => {
  showCategoryModal.value = false
  categoryModalType.value = ''
}
const submitCategory = async (data) => {
  const method = categoryModalType.value === 'add' ? 'POST' : 'PUT'
  const url = categoryModalType.value === 'add'
    ? categoryApiUrl
    : `${categoryApiUrl}/${data.id}`

  await fetch(url, {
    method,
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ name: data.name })
  })

  await fetchCategories()
  closeCategoryModal()
}

const deleteCategory = async () => {
  await fetch(`${categoryApiUrl}/${categoryForm.value.id}`, {
    method: 'DELETE',
    headers: {
      Authorization: `Bearer ${token}`
    }
  })

  await fetchCategories()
  closeCategoryModal()
}

import AssignEquipmentModal from '../components/AssignEquipmentModal.vue'

const showAssignModal = ref(false)
const assignItem = ref({})

const openAssignModal = (item) => {
  assignItem.value = { ...item }
  showAssignModal.value = true
}

</script>


<style scoped>
/* Header */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.btn-add {
  background: #2e7d32;
  color: white;
  border: none;
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

.btn-add:hover {
  background: #1b5e20;
}

/* Search */
.search-input {
  width: 100%;
  margin: 1rem 0;
  padding: 0.6rem 1rem;
  border-radius: 8px;
  border: 1px solid #ccc;
}

/* Table */
.inventario-table {
  width: 100%;
  border-collapse: collapse;
}

.inventario-table thead {
  background: #164e63;
  color: white;
}

.inventario-table th,
.inventario-table td {
  padding: 0.8rem;
  font-size: 0.9rem;
}

.inventario-table tbody tr:nth-child(odd) {
  background: #c7dadd;
}

.lowStock {
  color: #c62828;
  font-weight: 700;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.btn-edit {
  background: #1976d2;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 0.3rem 0.6rem;
}

.btn-delete {
  background: #d32f2f;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 0.3rem 0.6rem;
}

/* Pagination */
.pagination {
  margin-top: 1rem;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
}

.pagination button {
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
  border: none;
  background: #164e63;
  color: white;
  cursor: pointer;
}

.pagination button:disabled {
  background: #aaa;
  cursor: not-allowed;
}

.empty {
  text-align: center;
  padding: 1rem;
  font-style: italic;
}

/* ===== Responsive ===== */

@media (max-width: 768px) {
  .header {
    flex-direction: column;
    align-items: flex-start;
  }

  .btn-add {
    width: 100%;
  }

  .inventario-table th,
  .inventario-table td {
    font-size: 0.8rem;
    padding: 0.5rem;
  }

  .search-input {
    font-size: 0.9rem;
  }
}

@media (max-width: 480px) {

  .inventario-table th,
  .inventario-table td {
    font-size: 0.75rem;
    padding: 0.4rem;
  }

  .btn-add {
    padding: 0.5rem;
  }
}

/* Tabs */
.tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.tabs button {
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  background: #e0e0e0;
  font-weight: 600;
}

.tabs button.active {
  background: #164e63;
  color: white;
}

.equipment-subtabs {
  margin-bottom: 1rem;
  display: flex;
  gap: 0.5rem;
}

.equipment-subtabs button {
  padding: 0.4rem 1rem;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  background: #e0e0e0;
  font-weight: 600;
}

.equipment-subtabs button.active {
  background: #164e63;
  color: white;
}

.equipment-columns {
  display: flex;
  gap: 1rem;
}

.column {
  flex: 1;
  overflow-x: auto;
}

.column h2 {
  margin-bottom: 0.5rem;
}

.btn-assign {
  background: #f57c00;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 0.3rem 0.6rem;
}
</style>
