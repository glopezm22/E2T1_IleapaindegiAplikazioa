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
        <router-link to="/portfolio" active-class="active">{{ t('menu.portfolio') }}</router-link>
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
  min-width: 200px;
  background-color: #222;
  color: white;
  display: flex;
  flex-direction: column;
  padding: 2rem 1rem;
  user-select: none;
}

@media (max-width: 768px) {
  .sidebar {
    width: 100%;
    min-width: unset;
    flex-direction: row;
    flex-wrap: wrap;
    padding: 0.75rem 1rem;
    gap: 0.5rem;
  }

  .logo {
    margin-bottom: 0 !important;
    margin-right: auto;
  }

  .logo img {
    width: 80px !important;
  }

  .menu {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    gap: 4px;
    width: 100%;
  }

  .menu li {
    margin-bottom: 0;
  }

  .menu a {
    padding: 0.5rem 0.75rem;
    font-size: 0.8rem;
  }

  .idioma {
    margin-top: 0 !important;
  }
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
  padding: 0.75rem 1.25rem;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  border-radius: 0.5rem;
  font-size: 0.82rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  border-left: 3px solid transparent;
  transition: background-color 0.2s ease, color 0.2s ease, border-left-color 0.2s ease;
}

.menu a:hover {
  background-color: rgba(255, 255, 255, 0.07);
  color: #fff;
}

.menu a.active {
  background-color: rgba(156, 224, 219, 0.12);
  border-left-color: #9ce0db;
  color: #9ce0db;
}

.idioma {
  margin-top: auto; /* lo empuja al fondo del sidebar */
}
</style>
