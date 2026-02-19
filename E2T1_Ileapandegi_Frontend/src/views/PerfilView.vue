<template>
  <div class="content">
    <div class="content2">

      <!-- Título -->
      <h1>{{ $t('profile.title') }}</h1>

      <!-- ===== Información personal ===== -->
      <div class="perfil-card">

        <div class="perfil-left">
          <div class="avatar">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="avatar">
          </div>
        </div>

        <div class="perfil-right">
          <div class="form-group">
            <label>{{ $t('profile.name') }}</label>
            <input v-model="perfil.nombre" disabled />
          </div>

          <div class="form-group">
            <label>{{ $t('profile.email') }}</label>
            <input v-model="perfil.email" disabled />
          </div>

          <div class="form-group">
            <label>{{ $t('profile.role') }}</label>
            <input v-model="perfil.rol" disabled />
          </div>
        </div>

      </div>

      <!-- ===== Progreso ===== -->
      <div class="progreso">
        <h2>{{ $t('profile.progress') }}</h2>

        <div class="progress-item" v-for="item in progreso" :key="item.nombre">
          <div class="progress-header">
            <span>{{ item.nombre }}</span>
            <span>{{ item.valor }}%</span>
          </div>

          <div class="progress-bar">
            <div
              class="progress-fill"
              :style="{ width: item.valor + '%' }"
            ></div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

/* ===== Datos perfil ===== */
const perfil = ref({
  nombre: '',
  email: '',
  rol: ''
})

/* ===== Progreso ===== */
const progreso = ref([])

/* ===== Función para cargar datos del usuario ===== */
/* ===== Función para cargar datos del usuario ===== */
const cargarPerfil = async () => {
  try {
    // Traemos los datos del perfil
    const resPerfil = await fetch('http://100.25.200.198:8000/api/profile', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    })
    const dataPerfil = await resPerfil.json()
    localStorage.setItem('name', dataPerfil.name)
    localStorage.setItem('user_id', dataPerfil.id)
    perfil.value.nombre = dataPerfil.name
    perfil.value.rol = dataPerfil.rol
    perfil.value.email = dataPerfil.email || ''

    // Traemos los datos de progreso
    const resProgreso = await fetch('http://100.25.200.198:8000/api/profile/progress', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    })
    const dataProgreso = await resProgreso.json()
    console.log('DATA PROGRESO:', dataProgreso)

    // Suponiendo que la API devuelve algo como:
    // { servicios: [ { id, nombre, completado, cantidad_completada }, ... ] }

    const SERVICIOS_POR_COMPLETAR = 10

    progreso.value = dataProgreso.servicios.map(s => ({
      nombre: s.nombre,
      valor: Math.min((s.cantidad_completada / SERVICIOS_POR_COMPLETAR) * 100, 100)
    }))

  } catch (error) {
    console.error('Error al cargar perfil y progreso', error)
  }
}


/* ===== Cargar al montar el componente ===== */
onMounted(() => {
  cargarPerfil()
})
</script>


<style scoped>
/* ===== Layout ===== */
h1 {
  margin-bottom: 2rem;
}

/* ===== Perfil ===== */
.perfil-card {
  display: flex;
  gap: 2rem;
  align-items: center;
  margin-bottom: 3rem;
}

.perfil-left {
  flex: 0 0 120px;
}

.avatar {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  overflow: hidden;
  border: 4px solid #164e63;
}

.avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.perfil-right {
  flex: 1;
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-size: 0.85rem;
  margin-bottom: 0.3rem;
  font-weight: 600;
}

.form-group input {
  padding: 0.6rem;
  border-radius: 8px;
  border: 1px solid #ccc;
  background: #f5f5f5;
}

/* ===== Progreso ===== */
.progreso h2 {
  margin-bottom: 1.5rem;
}

.progress-item {
  margin-bottom: 1.2rem;
}

.progress-header {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
  margin-bottom: 0.3rem;
}

.progress-bar {
  width: 100%;
  height: 12px;
  background: #e0e0e0;
  border-radius: 10px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #164e63;
  border-radius: 10px;
  transition: width 0.4s ease;
}
</style>
