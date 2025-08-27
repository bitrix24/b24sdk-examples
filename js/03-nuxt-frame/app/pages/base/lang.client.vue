<script setup lang="ts">
import { computed } from 'vue'
import type { DescriptionListItem } from '@bitrix24/b24ui-nuxt'

const { t } = useI18n()
const route = useRoute()
route.meta.pageTitle = t('page.base_lang.seo.title')
route.meta.pageDescription = t('page.base_lang.seo.description')

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
</script>

<template>
  <div>
    <AdviceBanner>
      <B24Advice
        class="w-full max-w-[550px]"
        :b24ui="{ descriptionWrapper: 'w-full' }"
        :avatar="{ src: '/avatar/assistant.png' }"
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
