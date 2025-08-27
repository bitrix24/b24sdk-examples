<script setup lang="ts">
import { onMounted } from 'vue'
import type { B24Frame } from '@bitrix24/b24jssdk'

const { t } = useI18n()

definePageMeta({
  layout: 'index-page'
})
useHead({
  title: t('page.index.seo.title')
})

// region Init ////
const { $logger, processErrorGlobal } = useAppInit('IndexPage')
const { $initializeB24Frame } = useNuxtApp()
const $b24: B24Frame = await $initializeB24Frame()

const isAutoOpenActivityList = ref(true)
const isHmrUpdate = import.meta.hot?.data?.isHmrUpdate || false
if (!import.meta.hot && import.meta.client) {
  $logger.info('Update -> clear hmrUpdateFlag')
  sessionStorage.removeItem('hmrUpdateFlag')
}
// endregion ////

// region Lifecycle Hooks ////
onMounted(async () => {
  $logger.info('Hi from index page')

  try {
    await $b24.parent.setTitle(t('page.index.seo.title'))

    if (import.meta.hot && isHmrUpdate) {
      $logger.info('HMR update -> skip openActivityList')
    } else {
      if (isAutoOpenActivityList.value) {
        openActivityList()
      }

      if (import.meta.hot) {
        import.meta.hot.data.isHmrUpdate = true
      }
    }
  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: true,
      clearErrorHref: '/'
    })
  }
})
// endregion ////

// region Action ////
function openActivityList() {
  if ($b24.placement.isSliderMode) {
    $b24.slider.closeSliderAppPage()
  }

  window.setTimeout(() => {
    /**
     * @need fix lang
     */
    $b24.slider.openSliderAppPage({
      place: 'main',
      bx24_width: 1650,
      bx24_label: {
        bgColor: 'violet',
        text: '🛠️',
        color: '#ffffff'
      },
      bx24_title: t('page.main.seo.title')
    })
  }, 10)
}
// endregion ////
</script>

<template>
  <AdviceCenter>
    <B24Advice
      class="w-full max-w-[550px]"
      :b24ui="{ descriptionWrapper: 'w-full' }"
      :avatar="{ src: '/avatar/assistant.png' }"
    >
      <ProseH2>{{ $t('page.index.message.title') }}</ProseH2>
      <ProseP>{{ $t('page.index.message.line1') }}</ProseP>
      <B24Separator class="my-4" />
      <div class="flex flex-row flex-wrap items-center justify-center gap-2">
        <B24Button
          rounded
          :label="$t('page.index.menu.main')"
          color="air-primary"
          @click.stop="openActivityList"
        />
      </div>
    </B24Advice>
  </AdviceCenter>
</template>
