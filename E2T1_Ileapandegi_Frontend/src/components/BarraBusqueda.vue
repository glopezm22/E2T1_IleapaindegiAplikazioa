<template>
  <div class="search-bar">
    <input
      type="text"
      :placeholder="placeholder"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
    />
  </div>
</template>

<script setup>
import { defineProps } from 'vue'

const props = defineProps({
  modelValue: String,
  placeholder: { type: String, default: 'Buscar...' }
})
</script>

<style scoped>
.search-bar {
  width: 100%;
}

.search-bar input {
  width: 100%;
  padding: 0.7rem 1rem;
  font-size: 1rem;
  border-radius: 0.5rem;
  border: 1px solid black;
  outline: none;
  background-color: white;
  color: black;
  transition: border 0.2s;
}

.search-bar input:focus {
  border-color: #1f8aa0;
}
</style>
