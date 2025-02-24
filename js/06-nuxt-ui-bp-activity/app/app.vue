<script setup lang="ts">
const toaster = ref({
	position: 'top-right' as const,
	expand: true,
	duration: 8000
})
</script>

<template>
  <B24App
    :toaster="toaster"
  >
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </B24App>
</template>