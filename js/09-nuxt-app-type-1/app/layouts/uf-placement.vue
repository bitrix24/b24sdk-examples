<script setup lang="ts">
useHead({
  bodyAttrs: {
    class: 'text-base-master dark:text-base-150 bg-base-20 dark:bg-base-dark font-b24-system antialiased'
  }
})
</script>

<template>
  <div class="relative mt-1.5">
    <slot />
  </div>
</template>
