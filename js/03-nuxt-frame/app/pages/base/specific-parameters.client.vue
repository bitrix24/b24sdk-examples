<script setup lang="ts">
const { t } = useI18n()
const route = useRoute()
route.meta.pageTitle = t('page.base_specific-parameters.seo.title')
route.meta.pageDescription = t('page.base_specific-parameters.seo.description')
</script>

<template>
  <AdviceCenter>
    <B24Advice
      class="w-full max-w-[550px]"
      :b24ui="{ descriptionWrapper: 'w-full' }"
      :avatar="{ src: '/avatar/assistant.png' }"
    >
      <ProseP>@todo</ProseP>
    </B24Advice>
  </AdviceCenter>
</template>
