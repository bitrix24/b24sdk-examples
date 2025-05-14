<script setup lang="ts">
import PlayCircleIcon from '@bitrix24/b24icons-vue/main/PlayCircleIcon'

useHead({
  title: 'Bitrix24 UI - Starter'
})
</script>

<template>
  <div class="flex flex-col items-center justify-center gap-16 h-screen">
  <h1 class="font-b24-secondary text-h1 sm:text-8xl font-light">
    Bitrix24 UI - Starter
  </h1>
    <div class="flex flex-wrap items-center gap-2">
    <B24Button
      rounded
      label="Documentation"
      color="ai"
      :icon="PlayCircleIcon"
      to="https://bitrix24.github.io/b24ui/guide/installation-nuxt-app.html"
      target="_blank"
    />

    <B24Button
      rounded
      label="GitHub"
      color="link"
      depth="dark"
      to="https://github.com/bitrix24/b24ui"
      target="_blank"
    />
    </div>
  </div>
</template>
