<script setup lang="ts">
import { onMounted } from 'vue'

const { processErrorGlobal } = useAppInit()
const isUseB24Frame = useState('isUseB24Frame')

onMounted(async () => {
  /**
   * @memo Skip init $b24
   * @see middleware/01.app.page.or.slider.global.ts
   */
  if (import.meta.server || !isUseB24Frame.value) {
    return
  }

  try {
    const { $initializeB24Frame } = useNuxtApp()
    await $initializeB24Frame()
  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: false,
      clearErrorHref: '/'
    })
  }
})
</script>

<template>
  <ClientOnly>
    <B24App>
      <NuxtLayout>
        <NuxtPage />
      </NuxtLayout>
    </B24App>
  </ClientOnly>
</template>
