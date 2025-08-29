<script setup lang="ts">
import { computed } from 'vue'
import { usePageStore } from '~/stores/page'
import type { DescriptionListItem } from '@bitrix24/b24ui-nuxt'
import type { B24Frame } from '@bitrix24/b24jssdk'

const { t, locales: localesI18n, setLocale } = useI18n()
const page = usePageStore()

// region Init ////
const { $logger, initApp, b24Helper, destroyB24Helper } = useAppInit('AppInfoPage')
const { $initializeB24Frame } = useNuxtApp()
let $b24: null | B24Frame = null

const infoItems = computed(() => [
  {
    label: t('page.base_app-info.message.id'),
    code: 'id',
    description: (b24Helper.value?.appInfo.data.id ?? '').toString()
  },
  {
    label: t('page.base_app-info.message.code'),
    code: 'code',
    description: (b24Helper.value?.appInfo.data.code ?? '').toString()
  },
  {
    label: t('page.base_app-info.message.version'),
    code: 'version',
    description: (b24Helper.value?.appInfo.data.version ?? '').toString()
  },
  {
    label: t('page.base_app-info.message.status'),
    code: 'status',
    statusCode: (b24Helper.value?.appInfo.statusCode ?? '').toString(),
    statusValue: (b24Helper.value?.appInfo.data.status ?? '').toString(),
    description: '?'
  },
  {
    label: t('page.base_app-info.message.isInstalled'),
    code: 'isInstalled',
    description: b24Helper.value?.appInfo.data.isInstalled ? 'Y' : 'N'
  }
] satisfies DescriptionListItem[] )
// endregion ////

// region Lifecycle Hooks ////
onMounted(async () => {
  page.isLoading = true

  $b24 = await $initializeB24Frame()
  await initApp($b24, localesI18n, setLocale)

  page.title = t('page.base_app-info.seo.title')
  page.description = t('page.base_app-info.seo.description')

  page.isLoading = false

  $logger.info('Hi from base/app-info')
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
        :avatar="{ src: '/avatar/assistant.png' }"
      >
        <ProseP>{{ $t('page.base_app-info.message.line1') }}</ProseP>
      </B24Advice>
    </AdviceBanner>
    <ContainerWrapper class="px-[22px] py-[5px]">
      <B24DescriptionList
        :b24ui="{ container: 'mt-0' }"
        :items="infoItems"
      >
        <template #description="{ item }">
          <template v-if="item.code === 'status'">
            <B24Badge color="air-secondary-accent-2" :label="item.statusCode" />
            <B24Badge color="air-tertiary" class="ms-[6px]" :label="item.statusValue" />
          </template>
          <template v-else-if="item.code === 'isInstalled'">
            <B24Badge v-if="item.description === 'Y'" color="air-primary-success" label="installed" />
            <B24Badge v-if="item.description === 'N'" color="air-primary-alert" label="not installed" />
          </template>
          <template v-else>
            <B24Badge color="air-tertiary" :label="item.description" />
          </template>
        </template>
      </B24DescriptionList>
    </ContainerWrapper>
  </div>
</template>
