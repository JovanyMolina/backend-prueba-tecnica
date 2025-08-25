<script setup>
const props = defineProps({
  label: String,
  type: String,
  modelValue: String,
  error: String,
});
const emit = defineEmits(["update:modelValue"]);
</script>

<template>
  <label v-if="label" class="block text-sm text-slate-600">{{ label }}</label>
  <input
    :type="type || 'text'"
    class="mt-1 w-full border rounded px-3 py-2 focus:outline-none"
    :class="error ? 'border-red-500' : 'border-slate-300'"
    :value="modelValue"
    @input="emit('update:modelValue', $event.target.value)"
  />
  <p v-if="error" class="text-xs text-red-600 mt-1">{{ error }}</p>
</template>
