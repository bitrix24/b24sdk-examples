<script setup lang="ts">
import { computed } from 'vue'
import { navigateTo } from '#imports'
import { usePageStore } from '~/stores/page'
import type { DescriptionListItem } from '@bitrix24/b24ui-nuxt'
import type { B24Frame } from '@bitrix24/b24jssdk'

const { t, locales: localesI18n, setLocale } = useI18n()
const page = usePageStore()

// region Init ////
const { $logger, initApp, b24Helper, destroyB24Helper, processErrorGlobal } = useAppInit('FeedbackPage')
const { $initializeB24Frame } = useNuxtApp()
let $b24: null | B24Frame = null

const infoItems = computed(() => [
  {
    label: t('page.base_feedback.message.app_code'),
    code: 'app_code',
    description: (b24Helper.value?.forB24Form.app_code ?? '').toString()
  },
  {
    label: t('page.base_feedback.message.app_status'),
    code: 'app_status',
    description: (b24Helper.value?.forB24Form.app_status ?? '').toString()
  },
  {
    label: t('page.base_feedback.message.payment_expired'),
    code: 'payment_expired',
    description: (b24Helper.value?.forB24Form.payment_expired ?? '').toString()
  },
  {
    label: t('page.base_feedback.message.days'),
    code: 'days',
    description: (b24Helper.value?.forB24Form.days ?? 0).toString()
  },
  {
    label: t('page.base_feedback.message.b24_plan'),
    code: 'b24_plan',
    description: (b24Helper.value?.forB24Form.b24_plan ?? '').toString()
  },
  {
    label: t('page.base_feedback.message.c_name'),
    code: 'c_name',
    description: (b24Helper.value?.forB24Form.c_name ?? '').toString()
  },
  {
    label: t('page.base_feedback.message.c_last_name'),
    code: 'c_last_name',
    description: (b24Helper.value?.forB24Form.c_last_name ?? '').toString()
  },
  {
    label: t('page.base_feedback.message.hostname'),
    code: 'hostname',
    description: (b24Helper.value?.forB24Form.hostname ?? '').toString()
  }
] satisfies DescriptionListItem[] )
// endregion ////

// region Actions ////
const makeOpenPage = async(url: string) => {
  navigateTo({
    path: url,
    query: {}
  }, {
    external: true,
    open: {
      target: '_blank'
    }
  })
}
// endregion ////

// region Lifecycle Hooks ////
onMounted(async () => {
  page.isLoading = true

  $b24 = await $initializeB24Frame()
  await initApp($b24, localesI18n, setLocale)

  page.title = t('page.base_feedback.seo.title')
  page.description = t('page.base_feedback.seo.description')

  page.isLoading = false

  $logger.info('Hi from base/feedback')
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
        <ProseP>{{ $t('page.base_feedback.message.line1') }}</ProseP>
        <ProseP>{{ $t('page.base_feedback.message.line2') }}</ProseP>
      </B24Advice>
    </AdviceBanner>
    <ContainerWrapper class="px-[22px] py-[5px]">
      <B24DescriptionList
        :b24ui="{ container: 'mt-0' }"
        :items="infoItems"
      >
        <template #description="{ item }">
          <template v-if="item.code === 'payment_expired'">
            <B24Badge v-if="item.description === 'N'" color="air-primary-success" label="not expired" />
            <B24Badge v-if="item.description === 'Y'" color="air-primary-alert" label="expired" />
          </template>
          <template v-else-if="item.code === 'hostname'">
            <B24Badge
              color="air-secondary-accent-2"
              use-link
              :label="item.description"
              @click.stop="makeOpenPage(item.description)"
            />
          </template>
          <template v-else>
            <B24Badge color="air-tertiary" :label="item.description" />
          </template>
        </template>
      </B24DescriptionList>
    </ContainerWrapper>
  </div>
</template>
