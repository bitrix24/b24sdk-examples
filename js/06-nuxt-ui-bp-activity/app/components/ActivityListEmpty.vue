<script setup lang="ts">
import AttentionIIcon from '@bitrix24/b24icons-vue/main/AttentionIIcon'

defineProps<{
  searchQuery?: string
}>()

const emit = defineEmits<{ clear: [] }>()
</script>

<template>
  <div class="flex flex-col items-center justify-center">
    <div class="text-base-350">
      <AttentionIIcon class="size-32 text-base-600" />
    </div>
    <h2 class="text-h1 my-6">
      No activity found
    </h2>
    <B24Button
      label="Clear your search and try again"
      rounded
      @click="emit('clear')"
    />
  </div>
</template>
