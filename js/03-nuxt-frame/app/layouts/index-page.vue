<script setup lang="ts">
useHead({
  bodyAttrs: {
    // 'dark' | 'light' | 'edge-dark' | 'edge-light'
    class: `light light:[--air-theme-bg-color:#ffffff]`
  }
})
</script>

<template>
  <B24SidebarLayout
    ref="currentSidebarRef"
    :use-light-content="false"
    :b24ui="{ root: 'overflow-y-hidden' }"
  >
    <!-- Content -->
    <slot />
  </B24SidebarLayout>
</template>
