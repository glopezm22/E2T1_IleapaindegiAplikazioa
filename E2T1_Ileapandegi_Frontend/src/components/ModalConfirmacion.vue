<template>
  <div v-if="mostrar" class="modal-dialog-overlay" @click.self="$emit('cancelar')">
    <div class="modal" tabindex="-1">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ t('deleteUser.title') }}</h5>
          <button class="btn-cerrar" @click="$emit('cancelar')" aria-label="Cerrar modal">&times;</button>
        </div>
        <div class="modal-body">
          <p class="modal-mensaje">{{ mensaje }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn-eliminar" @click="$emit('confirmar')">{{ t('deleteUser.delete') }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

defineProps({
  mensaje: String,
  mostrar: Boolean
})
</script>

<style scoped>
.modal-dialog-overlay {
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
  width: 100%;
  max-width: 400px;
  max-height: 25vh;    
  border-radius: 12px;
  padding: 1rem 1.5rem;
  overflow-y: auto;
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  font-family: Arial, sans-serif;
  overflow-y: auto;
  height: auto;
  max-height: none;
  overflow: visible;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h5 {
  margin: 0;
  font-weight: 700;
  font-size: 1.1rem;
  color: #111;
}

.btn-cerrar {
  background: none;
  border: none;
  font-size: 1.5rem;
  line-height: 1;
  cursor: pointer;
  color: #444;
  padding: 0;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background-color 0.2s ease;
}

.btn-cerrar:hover {
  background-color: #eee;
}

.modal-mensaje {
  font-size: 0.9rem;
  color: #333;
  margin: 0;
  line-height: 1.3;
  text-align: left;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
}

.btn-eliminar {
  background-color: #c20b0b;
  color: white;
  border: none;
  padding: 0.5rem 1.2rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.3s ease;
  user-select: none;
}

</style>
