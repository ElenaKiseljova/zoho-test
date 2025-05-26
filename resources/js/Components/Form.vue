<script setup>
defineProps({
  stages: {
    type: [Array, null],
    required: false,
  },
});
</script>

<template>
  <div class="py-2">
    <template v-if="stages?.length">
      <h2 class="text-red-500 font-bold mb-2">Stages!!!</h2>

      <ul class="flex-col space-y-1 text-orange-400">
        <li v-for="stage in stages" :key="stage.id">
          {{ stage.name }}
        </li>
      </ul>
    </template>
  </div>
</template>

<style lang="scss" scoped></style>
