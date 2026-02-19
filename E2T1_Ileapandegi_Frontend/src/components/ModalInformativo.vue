<template>
  <div class="modal-backdrop fade show" @click.self="$emit('cerrar')" style="display: block;">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Cliente guardado</h5>
          
        </div>
        <div class="modal-body">
          <p>El cliente se ha guardado correctamente.</p>
        </div>
        
      </div>
    </div>
  </div>
</template>

<style scoped>
  .modal-backdrop {
  position: fixed;
  inset: 0; 
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: rgba(0,0,0,0.0); 
  z-index: 1050;
}

  .modal-content{
  background: white;
  border-radius: 8px;
  padding: 12px 15px;
  width: 360px;
  position: relative;
  box-shadow: 0 4px 12px rgb(0 0 0 / 0.15);
  display: flex;
                 
}

</style>
