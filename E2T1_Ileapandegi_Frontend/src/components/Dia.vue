<template>
    <div class="dia" @click="handleClick">
        <p>{{numero}}</p>
    </div>

    
</template>


<script setup>
const emit = defineEmits(['clickDia'])

const props = defineProps({
  numero: {
    type: Number,
    required: true,
  }
  
})
function handleClick() {
  emit('clickDia', props.numero) 
}


</script>

<style scoped>
.dia {
  background-color: #d7dad9; 
  color: rgb(8, 8, 8);
  padding: 1rem 1rem 5rem;
  border-radius: 20px;
  cursor: pointer;
  
}

</style>