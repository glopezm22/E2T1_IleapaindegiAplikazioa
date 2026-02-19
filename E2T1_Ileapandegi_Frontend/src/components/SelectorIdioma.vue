<template>
  <div class="selector-idioma">
    <select v-model="idioma" @change="cambiarIdioma">
      <option value="es">Español</option>
      <option value="eu">Euskera</option>
    </select>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const { locale } = useI18n()
const idioma = ref(locale.value)

function cambiarIdioma() {
  locale.value = idioma.value
}
</script>

<style scoped>
.selector-idioma {
  margin-top: auto;
  top: 1rem;
  right: 1.5rem;
}

.selector-idioma select {
  padding: 0.4rem 0.8rem;
  border-radius: 8px;
  border: 1px solid #ccc;
  background-color: #f8f9fa;
  color: #333;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.selector-idioma select:hover {
  border-color: #1f8aa0;
  background-color: #e9f5f7;
}
</style>