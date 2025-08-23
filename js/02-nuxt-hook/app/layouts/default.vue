<script setup lang="ts">
import { computed } from 'vue'
import type { NavigationMenuItem } from '@bitrix24/b24ui-nuxt'
import SunIcon from '@bitrix24/b24icons-vue/main/SunIcon'
import MoonIcon from '@bitrix24/b24icons-vue/main/MoonIcon'

useHead({
  htmlAttrs: {
    class: 'scrollbar-transparent'
  },
  bodyAttrs: {}
})

const colorMode = useColorMode()

const route = useRoute()
const pageTitle = computed(() => route.meta?.title || '')
const pageDescription = computed(() => route.meta?.description || '')
useSeoMeta({
  title: pageTitle.value,
  description: pageDescription.value
})

const isDark = computed({
  get() {
    return colorMode.value === 'dark'
  },
  set() {
    colorMode.preference = colorMode.value === 'dark' ? 'light' : 'dark'
  }
})

const pages = computed<NavigationMenuItem[]>(() => {
  return [
    {
      label: 'List of companies',
      to: '/hook/crm-item-list'
    },
    {
      label: 'Testing Rest-Api calls',
      to: '/hook/testing-rest-api-calls'
    },
    {
      label: 'LoggerBrowser',
      to: '/tools/use-logger'
    },
    {
      label: 'DateTime',
      to: '/tools/date-time'
    },
    {
      label: 'IBAN',
      to: '/tools/iban'
    },
    {
      label: 'Result',
      to: '/core/use-result'
    },
    {
      label: '404',
      to: '/core/404'
    }
  ]
})

const helpItems = computed(() => {
  const result: NavigationMenuItem[] = []

  result.push({
    label: isDark.value ? 'Dark' : 'Light',
    icon: isDark.value ? MoonIcon : SunIcon,
    kbds: ['shift', 'd'],
    badge: {
      label: 'shift + d',
      color: 'default' as const
    },
    onSelect(e: Event) {
      e?.preventDefault()
      isDark.value = !isDark.value
    }
  })

  return result
})

defineShortcuts(extractShortcuts(helpItems.value))
</script>

<template>
  <B24SidebarLayout
    :use-light-content="route.path !== '/'"
  >
    <template #sidebar>
      <B24SidebarHeader>
        <div class="h-full flex items-center relative my-0 ps-[25px] pe-xs rtl:pe-[25px]">
          <B24Link href="/" class="mt-0 text-(--ui-color-design-selection-content)">
            <ProseH4 class="font-medium mb-0">
              Bitrix24::hooks
            </ProseH4>
          </B24Link>
        </div>
      </B24SidebarHeader>
      <B24SidebarBody>
        <B24NavigationMenu
          :items="pages"
          orientation="vertical"
        />
        <B24SidebarSpacer />
        <B24NavigationMenu
          :items="helpItems"
          orientation="vertical"
        />
      </B24SidebarBody>
    </template>
    <!-- / <template #navbar /> / -->

    <!-- Header -->
    <template #content-top>
      <!-- / header-title / -->
      <slot name="header-title">
        <div class="w-full flex flex-col gap-[20px]">
          <div class="flex items-center gap-[12px]">
            <div class="w-full flex flex-col items-start gap-[10px]">
              <ProseH2 class="font-semibold mb-0">
                {{ pageTitle }}
              </ProseH2>
              <ProseP small accent="less">
                {{ pageDescription }}
              </ProseP>
            </div>
          </div>
        </div>
      </slot>
    </template>

    <template #content-actions />

    <!-- Content -->
    <slot />
  </B24SidebarLayout>
</template>
