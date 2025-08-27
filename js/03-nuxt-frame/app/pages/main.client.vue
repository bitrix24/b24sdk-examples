<script setup lang="ts">
import { useI18n } from '#imports'

const { t } = useI18n()

useHead({
  title: t('page.index.seo.title')
})

// region Init ////
const { $logger } = useAppInit('MainPage')
$logger.info('Hi from main page')
// endregion ////
</script>

<template>
  <AdviceCenter>
    <B24Advice
      class="w-full max-w-[550px]"
      :b24ui="{ descriptionWrapper: 'w-full' }"
      :avatar="{ src: '/avatar/assistant.png' }"
    >
      <ProseP>{{ $t('page.main.message.line1') }}</ProseP>
      <ProseP>
        {{ $t('page.main.message.line2') }}
        <div class="ms-[4px] inline-flex flex-row items-center gap-[4px]">
          <B24Kbd value="shift" size="sm" accent="accent" /><B24Kbd value="d" size="sm" accent="accent" />
        </div>
      </ProseP>
      <B24Separator class="my-4" accent="accent" :label="$t('page.main.message.scopes')"/>
      <div class="inline-flex flex-row flex-wrap items-center gap-[4px]">
        <ProseCode color="air-primary-copilot">user_brief</ProseCode>
        <ProseCode color="air-primary-copilot">crm</ProseCode>
        <ProseCode color="air-primary-copilot">pull</ProseCode>
      </div>
    </B24Advice>
  </AdviceCenter>
</template>
