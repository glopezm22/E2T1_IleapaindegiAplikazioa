<script setup>
import Menu from './components/AppMenu.vue'
import Login from './views/LoginView.vue'
import { useRoute } from 'vue-router'

const route = useRoute()
</script>

<template>
  <!-- LOGIN -->
  <div v-if="route.name === 'login'">
    <Login />
  </div>

  <!-- RESTO DE LA APP -->
  <div v-else class="app-layout">
    <Menu />
    <main class="app-main">
      <router-view />
    </main>
  </div>
</template>

<style>
*,
*::before,
*::after {
  box-sizing: border-box;
}

.app-layout {
  display: flex;
  min-height: 100vh;
}

.app-main {
  flex: 1;
  min-width: 0;
  overflow-x: hidden;
}

@media (max-width: 768px) {
  .app-layout {
    flex-direction: column;
  }
}
</style>
