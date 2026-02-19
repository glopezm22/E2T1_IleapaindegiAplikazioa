<template>
  <div class="modal-overlay">
    <div class="modal">
      <h2>Editar equipamiento</h2>

      <input v-model="localForm.name" placeholder="Nombre" />
      <input v-model="localForm.brand" placeholder="Marca" />
      <input v-model="localForm.label" placeholder="Etiqueta" />
      <input v-model="localForm.description" placeholder="Descripción" />

      <div class="actions">
        <button class="btn-add" @click="$emit('submit', localForm)">
          Guardar cambios
        </button>
        <button class="btn-delete" @click="$emit('close')">
          Cancelar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue'

const props = defineProps({
  item: Object
})

const localForm = reactive({
  id: null,
  name: '',
  brand: '',
  label: '',
  description: ''
})

watch(
  () => props.item,
  (val) => Object.assign(localForm, val),
  { immediate: true }
)
</script>

<style scoped>
/* ===== Overlay ===== */
.modal-overlay {
  position: fixed;
  inset: 0; /* top:0; right:0; bottom:0; left:0 */
  background: rgba(0, 0, 0, 0.5); /* fondo semi-transparente */
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

/* ===== Modal ===== */
.modal {
  background: #ffffff;          /* Color único para todas las modales */
  padding: 2rem;
  border-radius: 12px;
  width: 400px;
  max-width: 90%;
  box-shadow: 0 10px 25px rgba(0,0,0,0.3);
  display: flex;
  flex-direction: column;
  gap: 1rem;
  position: relative;
  text-align: left;
  height: auto;
}

/* ===== Form Inputs ===== */
.modal input,
.modal textarea {
  width: 100%;
  padding: 0.5rem;
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 0.9rem;
}

.modal input:focus,
.modal textarea:focus {
  border-color: #1976d2;
  box-shadow: 0 0 0 2px rgba(25, 118, 210, 0.2);
}

/* ===== Labels ===== */
.modal label {
  font-weight: 600;
  margin-bottom: 0.2rem;
  display: block;
}

/* ===== Actions Buttons ===== */
.modal .actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 1rem;
}

.modal button {
  padding: 0.6rem 1rem;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  font-weight: 600;
  transition: background 0.2s;
}

.modal button[type="submit"] {
  background: #1976d2;
  color: white;
}

.modal button[type="submit"]:hover {
  background: #115293;
}

.modal button[type="button"] {
  background: #e0e0e0;
  color: #333;
}

.modal button[type="button"]:hover {
  background: #bdbdbd;
}

/* Textarea */
.modal textarea {
  resize: vertical;
  min-height: 60px;
}

/* Responsivo */
@media (max-width: 450px) {
  .modal {
    width: 90%;
    padding: 1.5rem;
  }
}
</style>
