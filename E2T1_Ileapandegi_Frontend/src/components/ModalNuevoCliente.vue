<template>
  <div class="modal-backdrop" @click.self="cerrarModal">
    <div class="modal">
      <button class="cerrar-btn" @click="cerrarModal">X</button>
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
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1050;
}

.modal {
  background: white;
  border-radius: 8px;
  padding: 20px 25px;
  width: 360px;
  overflow-y: auto;
  position: relative;
  box-shadow: 0 4px 12px rgb(0 0 0 / 0.15);
  display: flex;
  flex-direction: column;
  gap: 15px;
  height: auto;
  max-height: none;
  overflow: visible;
}

.cerrar-btn {
  position: absolute;
  top: 10px; right: 12px;
  border: none;
  background: transparent;
  font-weight: bold;
  font-size: 16px;
  cursor: pointer;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

input {
  padding: 6px 8px;
  border-radius: 5px;
  border: 1px solid #ccc;
  font-size: 14px;
}

.guardar-btn {
  margin-top: 10px;
  background-color: #82d8d8;
  border: none;
  border-radius: 8px;
  padding: 8px 20px;
  font-weight: 600;
  cursor: pointer;
}
</style>
