<template>
  <nav class="sidebar">
    <div class="logo">
      <img src="@/assets/img/logoSanturtzi.png" alt="Santurtzi LHFP" />
    </div>

    <ul class="menu">
      <li>
        <router-link to="/home" active-class="active">{{ t('menu.home') }}</router-link>
      </li>
      <li>
        <router-link to="/inventario" active-class="active">{{ t('menu.inventory') }}</router-link>
      </li>
      <li>
        <router-link to="/clientes" active-class="active">{{ t('menu.clients') }}</router-link>
      </li>
      <li>
        <router-link to="/citas" active-class="active">{{ t('menu.appointments') }}</router-link>
      </li>
      <li>
        <router-link to="/perfil" active-class="active">{{ t('menu.profile') }}</router-link>
      </li>

      <!-- Enlace solo para Admin -->
      <li v-if="rol === 'A'">
        <router-link to="/parametrizacion" active-class="active">{{ t('menu.settings') }}</router-link>
      </li>
    </ul>
    <div class="idioma">
      <IdiomaSelector />
    </div>
  </nav>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import IdiomaSelector from '@/components/SelectorIdioma.vue'

const { t } = useI18n()

// Suponiendo que guardas el token y con eso obtienes info del usuario
const rol = ref('')

// Cargar rol del usuario al montar
onMounted(async () => {
  try {
    const res = await fetch('http://localhost:8000/api/profile', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    })
    const data = await res.json()
    rol.value = data.rol || '' // Asegurarse de que exista
  } catch (error) {
    console.error('Error al obtener rol del usuario:', error)
  }
})
</script>

<style scoped>
/* Sidebar */
.sidebar {
  width: 240px;
  background-color: #222;
  color: white;
  display: flex;
  flex-direction: column;
  padding: 2rem 1rem;
  user-select: none;
}

.logo {
  margin-bottom: 3rem;
  display: flex;
  justify-content: center;
}

.logo img {
  width: 130px;
  user-select: none;
}

.menu {
  list-style: none;
  padding: 0;
  margin: 0;
  flex-grow: 1;
}

.menu li {
  margin-bottom: 0.5rem;
}

.menu a {
  display: block;
  padding: 1rem 1.5rem;
  color: white;
  text-decoration: none;
  border-radius: 0.5rem;
  transition: background-color 0.25s ease;
}

.menu a:hover,
.menu a.active {
  background-color: #1f8aa0;
}

.idioma {
  margin-top: auto; /* lo empuja al fondo del sidebar */
}
</style>
