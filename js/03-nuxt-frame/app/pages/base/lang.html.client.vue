<script setup lang="ts">
import { computed } from 'vue'
import { usePageStore } from '~/stores/page'
import type { DescriptionListItem } from '@bitrix24/b24ui-nuxt'
import type { B24Frame } from '@bitrix24/b24jssdk'

const { t, locales: localesI18n, setLocale } = useI18n()
const page = usePageStore()

// region Init ////
const { $logger, initApp, destroyB24Helper } = useAppInit('LangPage')
const { $initializeB24Frame } = useNuxtApp()
let $b24: null | B24Frame = null

const infoItems = computed(() => [
  {
    label: t('page.base_lang.message.message1'),
    code: 'message1',
    description: t('message1')
  },
  {
    label: t('page.base_lang.message.message2'),
    code: 'message2',
    description: t('message2')
  }
] satisfies DescriptionListItem[] )
// endregion ////

// region Lifecycle Hooks ////
onMounted(async () => {
  page.isLoading = true

  $b24 = await $initializeB24Frame()
  await initApp($b24, localesI18n, setLocale)

  page.title = t('page.base_lang.seo.title')
  page.description = t('page.base_lang.seo.description')

  page.isLoading = false

  $logger.info('Hi from base/lang')
})

onUnmounted(() => {
  destroyB24Helper()
})
// endregion ////
</script>

<template>
  <div>
    <AdviceBanner>
      <B24Advice
        class="w-full max-w-[550px]"
        :b24ui="{ descriptionWrapper: 'w-full' }"
        :avatar="{ src: '../avatar/assistant.png' }"
      >
        <ProseP>{{ $t('page.base_lang.message.line1') }}</ProseP>
      </B24Advice>
    </AdviceBanner>
    <ContainerWrapper class="px-[22px] py-[5px]">
      <B24DescriptionList
        :b24ui="{ container: 'mt-0' }"
        :items="infoItems"
      />
    </ContainerWrapper>
  </div>
</template>
