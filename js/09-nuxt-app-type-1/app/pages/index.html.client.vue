<script setup lang="ts">
useHead({
  title: 'Bitrix24 UI - Starter'
})

// region Init ////
const { $logger } = useAppInit('Index')
const { $initializeB24Frame } = useNuxtApp()
const $b24 = await $initializeB24Frame()
$logger.info('Hi from index')
// endregion ////

// region Action ////
function openSliderDemo() {
  $b24.slider.openSliderAppPage({
    place: 'slider-demo',
    bx24_width: 320,
    bx24_label: {
      bgColor: 'violet',
      text: '🍅 Pomodoro',
      color: '#ffffff'
    },
    bx24_title: 'Pomodoro'
  })
}
// endregion ////
</script>

<template>
  <div class="flex flex-col items-center justify-center gap-16 h-screen">
    <h1 class="font-b24-secondary text-h1 sm:text-8xl font-light">
      Bitrix24 UI - Starter
    </h1>
    <div class="flex flex-wrap items-center gap-2">
      <B24Button
        rounded
        label="Open Slider"
        color="primary"
        @click.stop="openSliderDemo"
      />

      <!-- B24Button
        rounded
        label="Install"
        color="link"
        depth="dark"
        to="/install.html"
      / -->
    </div>
  </div>
</template>
