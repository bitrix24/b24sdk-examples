<script setup lang="ts">
import * as locales from '@bitrix24/b24ui-nuxt/locale'

const { locale } = useI18n()
const lang = computed(() => locales[locale.value].code)
const dir = computed(() => locales[locale.value].dir)

useHead({
  htmlAttrs: {
    lang,
    dir
  }
})
</script>

<template>
  <B24App
    :locale="locales[locale]"
  >
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </B24App>
</template>
