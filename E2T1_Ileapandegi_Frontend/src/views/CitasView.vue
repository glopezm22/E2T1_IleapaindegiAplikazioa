<template>
  <div class="content">
    <div class="content2">
      <div class="header-mes">
        <h1>Citas</h1>
        <div class="selectores">
          <select v-model="mesActual" class="selector">
            <option v-for="(nombre, index) in meses" :key="index" :value="index">
              {{ nombre }}
            </option>
          </select>
          <select v-model="anoActual" class="selector">
            <option v-for="year in anosDisponibles" :key="year" :value="year">
              {{ year }}
            </option>
          </select>
        </div>
      </div>
      <Mes :mes="mesActual" :ano="anoActual"/>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import Mes from '@/components/Mes.vue'

const hoy = new Date()
const mesActual = ref(hoy.getMonth())
const anoActual = ref(hoy.getFullYear())

const meses = [
  'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
  'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
]

// se crean los años para seleccion (5 antes y 5 despues)
const anosDisponibles = computed(() => {
  const anoBase = hoy.getFullYear()
  const anos = []
  for (let i = anoBase - 5; i <= anoBase + 5; i++) {
    anos.push(i)
  }
  return anos
})
</script>

<style scoped>
.header-mes {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.selectores {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.selector {
  background-color: #f5f5f5;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  padding: 10px 15px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  color: #333;
  min-width: 120px;
}

.selector:hover {
  border-color: #82d8d8;
  background-color: #fff;
}

.selector:focus {
  outline: none;
  border-color: #82d8d8;
  box-shadow: 0 0 0 3px rgba(130, 216, 216, 0.1);
}

h1 {
  margin: 0;
  font-size: 2rem;
}


@media (max-width: 768px) {
  .header-mes {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  h1 {
    font-size: 1.5rem;
  }

  .selectores {
    width: 100%;
    justify-content: flex-start;
  }

  .selector {
    flex: 1;
    min-width: 100px;
    font-size: 14px;
    padding: 8px 12px;
  }
}

@media (max-width: 480px) {
  h1 {
    font-size: 1.25rem;
  }

  .selectores {
    flex-direction: column;
    width: 100%;
  }

  .selector {
    width: 100%;
    min-width: unset;
  }
}
</style>