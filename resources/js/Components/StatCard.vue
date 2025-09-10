<script setup>
const props = defineProps({
  title: String,
  value: [String, Number],
});
</script>

<template>
  <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6 border border-gray-200 dark:border-gray-800">
    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ title }}</div>
    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ value }}</div>
  </div>
</template>
