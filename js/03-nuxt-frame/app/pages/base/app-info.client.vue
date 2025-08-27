<script setup lang="ts">
import { computed } from 'vue'
import type { DescriptionListItem } from '@bitrix24/b24ui-nuxt'

const { t } = useI18n()
const route = useRoute()
route.meta.pageTitle = t('page.base_app-info.seo.title')
route.meta.pageDescription = t('page.base_app-info.seo.description')

// region Init ////
const { $logger, b24Helper } = useAppInit('AppInfoPage')
$logger.info('Hi from base/app-info')
// endregion ////

const infoItems = computed(() => [
  {
    label: t('page.base_app-info.message.id'),
    code: 'id',
    description: (b24Helper.value?.appInfo.data.id || '').toString()
  },
  {
    label: t('page.base_app-info.message.code'),
    code: 'code',
    description: (b24Helper.value?.appInfo.data.code || '').toString()
  },
  {
    label: t('page.base_app-info.message.version'),
    code: 'version',
    description: (b24Helper.value?.appInfo.data.version || '').toString()
  },
  {
    label: t('page.base_app-info.message.status'),
    code: 'status',
    statusCode: (b24Helper.value?.appInfo.statusCode || '').toString(),
    statusValue: (b24Helper.value?.appInfo.data.status || '').toString(),
    description: '?'
  },
  {
    label: t('page.base_app-info.message.isInstalled'),
    code: 'isInstalled',
    description: b24Helper.value?.appInfo.data.isInstalled ? 'Y' : 'N'
  }
] satisfies DescriptionListItem[] )
</script>

<template>
  <ContainerWrapper>
    <B24DescriptionList
      :b24ui="{ container: 'mt-0' }"
      :items="infoItems"
    >
      <template #description="{ item }">
        <template v-if="item.code === 'status'">
          <B24Badge color="air-primary-success" inverted :label="item.statusCode" /> <ProseCode>{{ item.statusValue }}</ProseCode>
        </template>
        <template v-else-if="item.code === 'isInstalled'">
          <B24Badge v-if="item.description === 'Y'" color="air-primary-success" label="installed" />
          <B24Badge v-if="item.description === 'N'" color="air-primary-alert" label="not installed" />
        </template>
        <template v-else>
          <B24Badge color="air-secondary-accent-2" :label="item.description" />
        </template>
      </template>
    </B24DescriptionList>
  </ContainerWrapper>
</template>
