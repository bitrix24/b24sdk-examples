<script setup lang="ts">
import { defineLocale } from '@bitrix24/b24ui-nuxt/composables/defineLocale'
import * as locales from '@bitrix24/b24ui-nuxt/locale'

const localeAr = {
  name: 'العربية',
  code: 'ar',
  dir: 'rtl',
  messages: {
    inputMenu: {
      noMatch: 'لا توجد نتائج مطابقة',
      noData: 'لا توجد بيانات',
      create: 'إنشاء "{label}"'
    },
    inputNumber: {
      increment: 'زيادة',
      decrement: 'تقليل'
    },
    commandPalette: {
      placeholder: 'اكتب أمرًا أو ابحث...',
      noMatch: 'لا توجد نتائج مطابقة',
      noData: 'لا توجد بيانات',
      close: 'إغلاق'
    },
    selectMenu: {
      noMatch: 'لا توجد نتائج مطابقة',
      noData: 'لا توجد بيانات',
      create: 'إنشاء "{label}"',
      search: 'بحث...'
    },
    toast: {
      close: 'إغلاق'
    },
    carousel: {
      prev: 'السابق',
      next: 'التالي',
      goto: 'الذهاب إلي شريحة {slide}'
    },
    modal: {
      close: 'إغلاق'
    },
    slideover: {
      close: 'إغلاق'
    },
    alert: {
      close: 'إغلاق'
    },
    table: {
      noData: 'لا توجد بيانات'
    }
  }
}

defineLocale(localeAr)

const { locale } = useI18n()
const lang = computed(() => {
  if (locale.value == localeAr.code) {
    return localeAr.code
  }

  return locales[locale.value].code
})
const dir = computed(() => {
  if (locale.value == localeAr.code) {
    return localeAr.dir
  }
  return locales[locale.value].dir
})

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
