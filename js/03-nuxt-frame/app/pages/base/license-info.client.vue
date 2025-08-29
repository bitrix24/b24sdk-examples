<script setup lang="ts">
import { computed } from 'vue'
import type { DescriptionListItem } from '@bitrix24/b24ui-nuxt'
import type { B24Frame } from '@bitrix24/b24jssdk'
import { usePageStore } from '~/stores/page'

const { t, locales: localesI18n, setLocale } = useI18n()
const page = usePageStore()

// region Init ////
const { $logger, initApp, b24Helper, destroyB24Helper } = useAppInit('LicenseInfoPage')
const { $initializeB24Frame } = useNuxtApp()
let $b24: null | B24Frame = null

const infoItems = computed(() => [
  {
    label: t('page.base_license-info.message.languageId'),
    code: 'languageId',
    description: (b24Helper.value?.licenseInfo.data.languageId || '').toString()
  },
  {
    label: t('page.base_license-info.message.license'),
    code: 'license',
    description: (b24Helper.value?.licenseInfo.data.license || '').toString()
  },
  {
    label: t('page.base_license-info.message.licenseType'),
    code: 'licenseType',
    description: (b24Helper.value?.licenseInfo.data.licenseType ?? '').toString()
  },
  {
    label: t('page.base_license-info.message.licensePrevious'),
    code: 'licensePrevious',
    description: (b24Helper.value?.licenseInfo.data.licensePrevious ?? '?').toString()
  },
  {
    label: t('page.base_license-info.message.licenseFamily'),
    code: 'licenseFamily',
    description: (b24Helper.value?.licenseInfo.data.licenseFamily ?? '').toString()
  },
  {
    label: t('page.base_license-info.message.isSelfHosted'),
    code: 'isSelfHosted',
    description: b24Helper.value?.licenseInfo.data.isSelfHosted ? 'Y' : 'N'
  },
  {
    label: t('page.base_license-info.message.isExpired'),
    code: 'isExpired',
    description: b24Helper.value?.paymentInfo.data.isExpired ? 'Y' : 'N'
  },
  {
    label: t('page.base_license-info.message.days'),
    code: 'days',
    description: (b24Helper.value?.paymentInfo.data.days ?? 0).toString()
  }
] satisfies DescriptionListItem[] )
// endregion ////

// region Lifecycle Hooks ////
onMounted(async () => {
  page.isLoading = true

  $b24 = await $initializeB24Frame()
  await initApp($b24, localesI18n, setLocale)

  page.title = t('page.base_license-info.seo.title')
  page.description = t('page.base_license-info.seo.description')

  page.isLoading = false

  $logger.info('Hi from base/license-info')
})

onUnmounted(() => {
  destroyB24Helper()
})
</script>

<template>
  <div>
    <AdviceBanner>
      <B24Advice
        class="w-full max-w-[550px]"
        :b24ui="{ descriptionWrapper: 'w-full' }"
        :avatar="{ src: '/avatar/assistant.png' }"
      >
        <ProseP>{{ $t('page.base_license-info.message.line1') }}</ProseP>
      </B24Advice>
    </AdviceBanner>
    <ContainerWrapper class="px-[22px] py-[5px]">
      <B24DescriptionList
        :b24ui="{ container: 'mt-0' }"
        :items="infoItems"
      >
        <template #description="{ item }">
          <template v-if="item.code === 'isSelfHosted'">
            <B24Badge v-if="item.description === 'Y'" color="air-secondary-accent" label="Self Hosted" />
            <B24Badge v-if="item.description === 'N'" color="air-primary" label="Cloud" />
          </template>
          <template v-else-if="item.code === 'isExpired'">
            <B24Badge v-if="item.description === 'N'" color="air-primary-success" label="not expired" />
            <B24Badge v-if="item.description === 'Y'" color="air-primary-alert" label="expired" />
          </template>
          <template v-else>
            <B24Badge color="air-tertiary" :label="item.description" />
          </template>
        </template>
      </B24DescriptionList>
    </ContainerWrapper>
  </div>
</template>
