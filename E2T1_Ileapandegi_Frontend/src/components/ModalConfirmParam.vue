<!-- ModalConfirm.vue -->
<template>
  <div class="modal-backdrop">
    <div class="modal-container">
      <h2>{{ title }}</h2>
      <p>{{ message }}</p>
      <div class="modal-buttons">
        <button @click="$emit('confirm')">Confirmar</button>
        <button class="btn-cancel" @click="$emit('close')">Cancelar</button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: 'Confirmar' },
  message: { type: String, default: '¿Estás seguro?' }
})
defineEmits(['close','confirm'])
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  top:0; left:0;
  width:100%; height:100%;
  background: rgba(0,0,0,0.5);
  display:flex;
  justify-content:center;
  align-items:center;
  z-index:1000;
}

.modal-container {
  background:white;
  padding:1.5rem;
  border-radius:12px;
  width: 350px;
  max-width:90%;
}

.modal-buttons {
  display:flex;
  justify-content:flex-end;
  gap:0.5rem;
  margin-top:1rem;
}

.modal-buttons button {
  padding:0.5rem 1rem;
  border:none;
  border-radius:6px;
  cursor:pointer;
}

.modal-buttons .btn-cancel { background:#aaa; color:white; }
.modal-buttons button:first-child { background:#d32f2f; color:white; }
</style>
