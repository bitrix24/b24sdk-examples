<script setup lang="ts">
useHead({
  bodyAttrs: {
    class: 'bg-tertiary font-b24-system text-base-900 antialiased'
  }
})
</script>

<template>
  <div class="absolute transition-none h-screen-and-footer top-0 bottom-0 left-0 right-0 flex flex-col">
    <slot />
  </div>
</template>
