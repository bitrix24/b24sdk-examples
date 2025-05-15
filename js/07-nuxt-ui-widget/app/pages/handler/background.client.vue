<script setup lang="ts">
// region Init ////
const { $logger } = useAppInit()
const { $initializeB24Frame } = useNuxtApp()
const $b24 = await $initializeB24Frame()
// endregion ////

// region Action ////
$logger.info('Hi from bg')
window.setTimeout(() => {
  $b24.slider.openSliderAppPage({
    place: 'slider-pomodoro',
    bx24_width: 320,
    bx24_label: {
      bgColor: 'violet',
      text: '🍅 Pomodoro',
      color: '#ffffff'
    },
    bx24_title: 'Pomodoro'
  })
}, 10)
// endregion ////
</script>

<template>
  <div class="flex flex-col gap-4">
    Nothing to see here
  </div>
</template>
