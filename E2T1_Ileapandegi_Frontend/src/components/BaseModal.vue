<template>
  <Teleport to="body">
    <div class="modal-overlay" @click.self="$emit('close')">
      <div class="modal">
        <h2>{{ title }}</h2>
        <slot />
      </div>
    </div>
  </Teleport>
</template>

<script setup>
defineProps({
  title: String
})

defineEmits(['close'])
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
  z-index: 9999; /* encima de todo */
}

/* ===== Modal ===== */
.modal {
  background: white;           /* fondo de la modal */
  padding: 2rem;               /* espacio interior */
  border-radius: 12px;         /* bordes redondeados */
  width: 400px;                /* ancho fijo */
  max-width: 95%;              /* responsivo en pantallas pequeñas */
  box-shadow: 0 10px 25px rgba(0,0,0,0.3); /* sombra elegante */
  display: flex;
  flex-direction: column;
  gap: 1rem;                   /* espacio entre hijos */
  position: relative;          /* para elementos internos absolutos si los hubiera */
}

/* ===== Form inputs ===== */
.modal input,
.modal textarea {
  width: 100%;
  padding: 0.5rem;
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 0.9rem;
  font-family: inherit;
  outline: none;
  box-sizing: border-box;
}

.modal input:focus,
.modal textarea:focus {
  border-color: #1976d2; /* azul foco */
  box-shadow: 0 0 0 2px rgba(25, 118, 210, 0.2);
}

/* ===== Labels ===== */
.modal label {
  font-weight: 600;
  margin-bottom: 0.2rem;
  display: block;
}

/* ===== Actions buttons ===== */
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

/* ===== Textarea ===== */
.modal textarea {
  resize: vertical;
  min-height: 60px;
}

/* ===== Responsivo ===== */
@media (max-width: 450px) {
  .modal {
    width: 90%;
    padding: 1.5rem;
  }
}
</style>
