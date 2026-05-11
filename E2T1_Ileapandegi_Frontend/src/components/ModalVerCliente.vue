<template>
  <div class="ver-cliente-overlay" @click.self="$emit('cerrar')">
    <div class="ver-cliente-modal p-4 rounded shadow bg-white position-relative">
      <button type="button" class="ver-cliente-cerrar" @click="$emit('cerrar')" aria-label="Cerrar">&times;</button>
      <h5 class="mb-4">{{ cliente.name }} {{ cliente.surnames }}</h5>

      <div class="d-flex mb-3 flex-wrap gap-2">
        <div class="ver-cliente-avatar me-3">
          <img src="@/assets/img/user.png" alt="Avatar" class="rounded-circle" />
        </div>

        <div class="flex-grow-1">
          <div class="mb-3">
            <label class="form-label small">{{ t('modal.name') }}:</label>
            <input v-model="form.name" type="text" class="form-control form-control-sm" disabled />
          </div>

          <div class="mb-3">
            <label class="form-label small">{{ t('modal.surnames') }}:</label>
            <input v-model="form.surnames" type="text" class="form-control form-control-sm" disabled />
          </div>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label small">{{ t('modal.email') }}:</label>
        <input v-model="form.email" type="email" class="form-control form-control-sm" />
        <p v-if="errores.email" class="text-danger small mt-1">{{ errores.email }}</p>
      </div>

      <div class="mb-3">
        <label class="form-label small">{{ t('modal.phone') }}:</label>
        <input v-model="form.telephone" type="text" class="form-control form-control-sm" />
        <p v-if="errores.telephone" class="text-danger small mt-1">{{ errores.telephone }}</p>
      </div>

      <div class="mb-3">
        <label class="form-label small">{{ t('modal.observations') }}:</label>
        <textarea
          v-model="form.observations"
          rows="3"
          class="form-control form-control-sm"
          :placeholder="t('modal.no_observations')"
        ></textarea>
      </div>

      <button class="ver-cliente-guardar w-100 mt-2" @click="guardar">{{ t('modal.save') }}</button>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  cliente: { type: Object, required: true }
})

const emit = defineEmits(['cerrar', 'guardar'])

const form = reactive({
  name: '',
  surnames: '',
  email: '',
  telephone: '',
  observations: ''
})

const errores = reactive({
  email: '',
  telephone: ''
})

function validarForm() {
  errores.email = ''
  errores.telephone = ''
  let valido = true

  if (form.email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (!emailRegex.test(form.email)) {
      errores.email = t('modal.invalid_email')
      valido = false
    }
  }

  if (form.telephone) {
    const numeros = form.telephone.replace(/\D/g, '')
    if (!/^\d{9}$/.test(numeros)) {
      errores.telephone = t('modal.invalid_phone')
      valido = false
    }
  }

  return valido
}

watch(
  () => props.cliente,
  (newCliente) => {
    form.name = newCliente.name || ''
    form.surnames = newCliente.surnames || ''
    form.email = newCliente.email || ''
    form.telephone = newCliente.telephone || ''
    form.observations = newCliente.observations || ''
  },
  { immediate: true }
)

const guardar = () => {
  if (!validarForm()) return
  emit('guardar', { ...form, id: props.cliente.id })
  emit('cerrar')
}
</script>

<style scoped>
.ver-cliente-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1060;
  padding: 1rem;
}

.ver-cliente-modal {
  width: min(360px, 95vw);
  max-height: 90vh;
  overflow-y: auto;
  border-radius: 10px;
  position: relative;
}

.ver-cliente-cerrar {
  position: absolute;
  top: 0.5rem;
  right: 0.75rem;
  background: transparent;
  border: none;
  font-size: 1.5rem;
  font-weight: bold;
  line-height: 1;
  cursor: pointer;
  color: #555;
  padding: 0;
  z-index: 1;
}

.ver-cliente-cerrar:hover {
  color: #000;
}

.ver-cliente-avatar {
  display: flex;
  justify-content: center;
  align-items: center;
}

.ver-cliente-avatar img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 50%;
}

.ver-cliente-guardar {
  background-color: #9CE0DB;
  border: none;
  border-radius: 8px;
  padding: 8px 20px;
  font-weight: 600;
  cursor: pointer;
  color: black;
  transition: background-color 0.2s;
}

.ver-cliente-guardar:hover {
  background-color: #7dcfca;
}

@media (max-width: 480px) {
  .ver-cliente-modal {
    padding: 1rem !important;
  }

  .ver-cliente-avatar img {
    width: 60px;
    height: 60px;
  }
}
</style>
