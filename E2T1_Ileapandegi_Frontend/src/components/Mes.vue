<template>
    <div class="mes mt-3">
        <div
            v-for="dia in semana"
            :key="dia"
            class="cabecera"
        >
            {{ dia }}
        </div>

        <div
            v-for="(dia, index) in diasMes"
            :key="index"
        >
            <Dia 
                v-if="dia" 
                :numero="dia.dia"
                @clickDia="abrirDia" 
            />
        </div>
    </div>
</template>

<script setup>
import router from '@/router'
import Dia from './Dia.vue'
import { computed } from 'vue'

const props = defineProps({
  mes: {
    type: Number,
    required: true
  },
  ano: {
    type: Number,
    required: true
  }
})

function abrirDia(dia) {
    router.push({ 
        name: 'DiaDetalle', 
        params: { 
            dia: dia, 
            mes: props.mes, 
            ano: props.ano 
        } 
    })
}

const semana = ['L', 'M', 'X', 'J', 'V']

const diasMes = computed(() => {
    const dias = []

    const primerDiaSemana = new Date(props.ano, props.mes, 1).getDay()

    const offset = primerDiaSemana === 0 ? 6 : primerDiaSemana - 1
    for (let i = 0; i < Math.min(offset, 5); i++) {
        dias.push(null)
    }

    const ultimoDia = new Date(props.ano, props.mes + 1, 0).getDate()
    for (let d = 1; d <= ultimoDia; d++) {
        const fecha = new Date(props.ano, props.mes, d)
        const diaSemana = fecha.getDay()

        if (diaSemana >= 1 && diaSemana <= 5) {
            dias.push({
                dia: d,
                fecha: fecha.toISOString()
            })
        }
    }

    return dias
})
</script>

<style scoped>
.mes {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 1rem;
  width: 100%;
  box-sizing: border-box;
}

.cabecera {
  text-align: center;
  font-weight: bold;
  font-size: 1rem;
  padding: 0.5rem;
}


@media (max-width: 768px) {
  .mes {
    gap: 0.75rem;
  }

  .cabecera {
    font-size: 0.9rem;
    padding: 0.4rem;
  }
}


@media (max-width: 480px) {
  .mes {
    gap: 0.5rem;
  }

  .cabecera {
    font-size: 0.75rem;
    padding: 0.25rem;
  }
}
</style>