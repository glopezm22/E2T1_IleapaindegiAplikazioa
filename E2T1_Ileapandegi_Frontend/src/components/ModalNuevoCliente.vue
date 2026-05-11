<template>
  <div class="nuevo-cliente-overlay" @click.self="cerrarModal">
    <div class="nuevo-cliente-modal">
      <button class="nuevo-cliente-cerrar" @click="cerrarModal" aria-label="Cerrar">&times;</button>
      <h2>{{ t('newClient.title') }}</h2>

      <div class="form-group">
        <label for="nombre">{{ t('newClient.name') }}:</label>
        <input id="nombre" v-model="cliente.name" type="text" />
        <p v-if="errores.name" class="error-msg">{{ errores.name }}</p>
      </div>

      <div class="form-group">
        <label for="apellidos">{{ t('newClient.surnames') }}:</label>
        <input id="apellidos" v-model="cliente.surnames" type="text" />
        <p v-if="errores.surnames" class="error-msg">{{ errores.surnames }}</p>
      </div>

      <div class="form-group">
        <label for="telefono">{{ t('newClient.phone') }}:</label>
        <input id="telefono" v-model="cliente.telephone" type="tel" />
        <p v-if="errores.telephone" class="error-msg">{{ errores.telephone }}</p>
      </div>

      <div class="form-group">
        <label for="email">{{ t('newClient.email') }}:</label>
        <input id="email" v-model="cliente.email" type="email" />
        <p v-if="errores.email" class="error-msg">{{ errores.email }}</p>
      </div>

      <div class="form-group">
        <label>{{ t('newClient.homeClient') }}:</label>
        <div class="radio-group">
          <label>
            <input type="radio" v-model="cliente.home_client" :value="true" />
            {{ t('newClient.yes') }}
          </label>
          <label>
            <input type="radio" v-model="cliente.home_client" :value="false" />
            {{ t('newClient.no') }}
          </label>
        </div>
        <p v-if="errores.home_client" class="error-msg">{{ errores.home_client }}</p>
      </div>

      <button class="guardar-btn" @click="validarYGuardar">{{ t('newClient.save') }}</button>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const emit = defineEmits(['guardar', 'cerrar'])

const cliente = reactive({
  name: '',
  surnames: '',
  telephone: '',
  home_client: null,
  email: ''
})

const errores = reactive({
  name: '',
  surnames: '',
  telephone: '',
  email: '',
  home_client: ''
})

function validarCliente(cliente) {
  errores.name = ''
  errores.surnames = ''
  errores.telephone = ''
  errores.email = ''
  errores.home_client = ''

  const letrasRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/
  if (!cliente.name) errores.name = t('newClient.errors.nameRequired')
  else if (!letrasRegex.test(cliente.name)) errores.name = t('newClient.errors.onlyLetters')

  if (!cliente.surnames) errores.surnames = t('newClient.errors.surnamesRequired')
  else if (!letrasRegex.test(cliente.surnames)) errores.surnames = t('newClient.errors.onlyLetters')

  if (cliente.telephone) {
    const telRegex = /^[0-9+\-\s]+$/
    if (!telRegex.test(cliente.telephone)) errores.telephone = t('newClient.errors.invalidPhone')
    const numeros = cliente.telephone.replace(/\D/g, '')
    if (!/^\d{9}$/.test(numeros)) {
      errores.telephone = t('newClient.errors.phone9Digits')
    }
  }

  if (cliente.email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (!emailRegex.test(cliente.email)) errores.email = t('newClient.errors.invalidEmail')
  }

  if (cliente.home_client === null) errores.home_client = t('newClient.errors.selectYesNo')

  return !errores.name && !errores.surnames && !errores.telephone && !errores.email && !errores.home_client
}

function validarYGuardar() {
  if (!validarCliente(cliente)) return
  emit('guardar', { ...cliente })
  cerrarModal()
}

function cerrarModal() {
  emit('cerrar')
}
</script>

<style scoped>
.nuevo-cliente-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1060;
  padding: 1rem;
}

.nuevo-cliente-modal {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  width: min(360px, 95vw);
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  box-shadow: 0 4px 12px rgb(0 0 0 / 0.15);
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.nuevo-cliente-cerrar {
  position: absolute;
  top: 0.5rem;
  right: 0.75rem;
  border: none;
  background: transparent;
  font-weight: bold;
  font-size: 1.5rem;
  line-height: 1;
  cursor: pointer;
  color: #555;
  padding: 0;
  z-index: 1;
}

.nuevo-cliente-cerrar:hover {
  color: #000;
}

.radio-group {
  display: flex;
  gap: 20px;
  align-items: center;
}

.error-msg {
  color: red;
  font-size: 0.85em;
  margin: 2px 0 8px 0;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

input[type='text'],
input[type='tel'],
input[type='email'] {
  padding: 6px 8px;
  border-radius: 5px;
  border: 1px solid #ccc;
  font-size: 14px;
  width: 100%;
}

.guardar-btn {
  margin-top: 10px;
  background-color: #82d8d8;
  border: none;
  border-radius: 8px;
  padding: 8px 20px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.guardar-btn:hover {
  background-color: #5ec9c9;
}
</style>
