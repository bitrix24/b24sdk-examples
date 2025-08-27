<script setup lang="ts">
import { computed } from 'vue'
import type { DescriptionListItem } from '@bitrix24/b24ui-nuxt'
import type { B24Frame } from '@bitrix24/b24jssdk'

definePageMeta({
  layout: false
})

const { t } = useI18n()
const route = useRoute()
route.meta.pageTitle = t('page.base_feedback.seo.title')
route.meta.pageDescription = t('page.base_feedback.seo.description')

// region Init ////
const { $logger, b24Helper } = useAppInit('FeedbackPage')
const { $initializeB24Frame } = useNuxtApp()
const $b24: B24Frame = await $initializeB24Frame()

$logger.info('Hi from base/feedback')
// endregion ////

const infoItems = computed(() => [
  {
    label: 'app_code',
    code: 'app_code',
    description: (b24Helper.value?.forB24Form.app_code ?? '').toString()
  },
  {
    label: 'app_status',
    code: 'app_status',
    description: (b24Helper.value?.forB24Form.app_status ?? '').toString()
  },
  {
    label: 'payment_expired',
    code: 'payment_expired',
    description: (b24Helper.value?.forB24Form.payment_expired ?? '').toString()
  },
  {
    label: 'days',
    code: 'days',
    description: (b24Helper.value?.forB24Form.days ?? 0).toString()
  },
  {
    label: 'b24_plan',
    code: 'b24_plan',
    description: (b24Helper.value?.forB24Form.b24_plan ?? '').toString()
  },
  {
    label: 'c_name',
    code: 'c_name',
    description: (b24Helper.value?.forB24Form.c_name ?? '').toString()
  },
  {
    label: 'c_last_name',
    code: 'c_last_name',
    description: (b24Helper.value?.forB24Form.c_last_name ?? '').toString()
  },
  {
    label: 'hostname',
    code: 'hostname',
    description: (b24Helper.value?.forB24Form.hostname ?? '').toString()
  }
] satisfies DescriptionListItem[] )

// region Actions ////
const makeOpenFeedBack = async() => {
  await $b24.slider.openSliderAppPage(
    {
      place: 'feedback',
      bx24_width: 650,
      bx24_label: {
        bgColor: 'green',
        text: t('page.feedback.seo.title'),
        color: '#ffffff',
      },
      bx24_title: t('page.base_feedback.seo.title'),
    }
  )
}
// endregion ////
</script>

<template>
  <NuxtLayout name="default">
    <template #top-actions>
      <B24Button
        color="air-secondary-accent"
        :label="$t('page.base_feedback.actions.open')"
        @click.stop="makeOpenFeedBack()"
      />
    </template>
    <ContainerWrapper class="px-[22px] py-[5px]">
      <B24DescriptionList
        :b24ui="{ container: 'mt-0' }"
        :items="infoItems"
      />
    </ContainerWrapper>
  </NuxtLayout>
</template>
