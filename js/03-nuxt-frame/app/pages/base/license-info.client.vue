<script setup lang="ts">
import { computed } from 'vue'
import type { DescriptionListItem } from '@bitrix24/b24ui-nuxt'

const { t } = useI18n()
const route = useRoute()
route.meta.pageTitle = t('page.base_license-info.seo.title')
route.meta.pageDescription = t('page.base_license-info.seo.description')

// region Init ////
const { $logger, b24Helper } = useAppInit('AppInfoPage')
$logger.info('Hi from base/license-info')
// endregion ////

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
</script>

<template>
  <ContainerWrapper class="px-[22px] py-[5px]">
    <B24DescriptionList
      :b24ui="{ container: 'mt-0' }"
      :items="infoItems"
    >
      <template #description="{ item }">
        <template v-if="item.code === 'isSelfHosted'">
          <B24Badge v-if="item.description === 'Y'" color="air-primary" label="Self Hosted" />
          <B24Badge v-if="item.description === 'N'" color="air-primary" inverted label="Cloud" />
        </template>
        <template v-else-if="item.code === 'isExpired'">
          <B24Badge v-if="item.description === 'N'" color="air-primary-success" label="not expired" />
          <B24Badge v-if="item.description === 'Y'" color="air-primary-alert" label="expired" />
        </template>
        <template v-else>
          <B24Badge color="air-secondary-accent-2" :label="item.description" />
        </template>
      </template>
    </B24DescriptionList>
  </ContainerWrapper>
</template>
