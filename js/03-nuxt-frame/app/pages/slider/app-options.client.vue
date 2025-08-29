<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { usePageStore } from '~/stores/page'
import type { B24Frame } from '@bitrix24/b24jssdk'

/**
 * @memo For User options we use `B24Slideover`
 * @see app/components/UserOptionsSlideover.vue
 */

definePageMeta({
  layout: false
})

const { t, locales: localesI18n, setLocale } = useI18n()
const page = usePageStore()

// region Init ////
const { $logger, initApp, destroyB24Helper, usePullClient, startPullClient } = useAppInit('SliderAppOptionsPage')
const { $initializeB24Frame } = useNuxtApp()
let $b24: null | B24Frame = null
// endregion ////

// region Lifecycle Hooks ////
onMounted(async () => {
  page.isLoading = true

  $b24 = await $initializeB24Frame()
  await initApp($b24, localesI18n, setLocale)

  page.title = t('page.app-options.seo.title')
  page.description = t('page.app-options.seo.description')

  usePullClient()
  startPullClient()

  page.isLoading = false

  $logger.info('Hi from slider/app-options')
})

onUnmounted(() => {
  destroyB24Helper()
})
// endregion ////
</script>

<template>
  <NuxtLayout name="slider">
    <AdviceBanner>
      <B24Advice
        class="w-full max-w-[550px]"
        :b24ui="{ descriptionWrapper: 'w-full' }"
        :avatar="{ src: '/avatar/assistant.png' }"
      >
        <ProseP>@todo</ProseP>
      </B24Advice>
    </AdviceBanner>
  </NuxtLayout>
</template>
