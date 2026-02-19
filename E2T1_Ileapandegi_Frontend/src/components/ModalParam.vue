<!-- ModalParam.vue -->
<template>
  <div class="modal-backdrop">
    <div class="modal-container">
      <h2>{{ title }}</h2>

      <form @submit.prevent="submitForm">
        <slot></slot>
        <div class="modal-buttons">
          <button type="submit">{{ submitText }}</button>
          <button type="button" class="btn-cancel" @click="$emit('close')">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  submitText: { type: String, default: 'Guardar' }
})
const emit = defineEmits(['close', 'submit'])

const submitForm = (e) => {
  emit('submit', e)
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-container {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  width: 400px;
  max-width: 90%;
}

.modal-container h2 {
  margin-bottom: 1rem;
  color: #164e63;
}

.modal-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 1rem;
}

.modal-buttons button {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  border: none;
  cursor: pointer;
}

.modal-buttons .btn-cancel {
  background: #aaa;
  color: white;
}

.modal-buttons button[type="submit"] {
  background: #164e63;
  color: white;
}
</style>
