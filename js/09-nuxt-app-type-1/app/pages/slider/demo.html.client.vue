<script setup lang="ts">
import { ref } from 'vue'
import { sleepAction } from '~/utils/sleep'

// region Init ////
const { $logger, initApp, processErrorGlobal } = useAppInit('Slider-Pomodoro')
const { $initializeB24Frame } = useNuxtApp()
const $b24 = await $initializeB24Frame()
$logger.info('Hi from slider')
// endregion ////

// region Init ////
const isLoading = ref(true)
// endregion ////

// region Actions ////
async function closeSlider() {
  $logger.log('Slider closed — let\'s go!')
  await $b24.parent.closeApplication()
}
// endregion ////

// region Lifecycle Hooks ////
onMounted(async () => {
  try {
    isLoading.value = true

    await initApp($b24)

    /**
     * @memo remove this pause -> it`s only for demo
     */
    await sleepAction(2_000)
    isLoading.value = false
  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: false,
      clearErrorHref: '/activity-list'
    })
  }
})
// endregion ////
</script>

<template>
  <div class="w-full py-10 px-4 gap-4 flex flex-col items-center justify-start">
    <template v-if="isLoading">
      <div class="my-8 max-lg:ps-3">
        <div class="relative p-sm2 rounded-md flex flex-col gap-sm border-2 transition-shadow shadow bg-white border-base-master/10 dark:bg-white/10 dark:border-base-100/20">
          <B24Skeleton class="h-5xl min-w-[110px] max-w-[200px] rounded-full" />
          <B24Skeleton class="h-5xl min-w-[110px] max-w-[400px] rounded-full" />
          <B24Skeleton class="h-5xl min-w-[110px] max-w-[300px] rounded-full" />
        </div>
      </div>
    </template>
    <template v-else>
      <div class="w-full flex-1 flex flex-col items-center gap-4">
        <ProseH1 class="text-center mb-0">
          Some title
        </ProseH1>

        <B24Button
          color="primary"
          depth="dark"
          label="Close slider"
          loading-auto
          @click.stop="closeSlider"
        />
      </div>
    </template>
  </div>
</template>
