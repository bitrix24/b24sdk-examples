<script setup lang="ts">
import { computed } from 'vue'
import type { NavigationMenuItem } from '@bitrix24/b24ui-nuxt'
import SunIcon from '@bitrix24/b24icons-vue/main/SunIcon'
import MoonIcon from '@bitrix24/b24icons-vue/main/MoonIcon'

const colorMode = useColorMode()

useHead({
  bodyAttrs: {
    class: 'text-base-master dark:text-base-150 bg-base-50 dark:bg-base-dark font-b24-system antialiased'
  }
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
      color: 'default' as const,
      depth: 'light' as const,
      useFill: false
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
    :use-light-content="true"
  >
    <template #sidebar>
      <B24SidebarHeader>
        <B24SidebarSection class="ps-[18px] flex-row  items-center justify-start gap-0.5 text-primary">
          <ProseA href="/">
            <ProseH4 class="mb-0 leading-none">
              Bitrix24::hooks
            </ProseH4>
          </ProseA>
        </B24SidebarSection>
      </B24SidebarHeader>
      <B24SidebarBody>
        <B24NavigationMenu
          :items="pages"
          variant="pill"
          orientation="vertical"
        />
        <B24SidebarSpacer />
        <B24NavigationMenu
          :items="helpItems"
          variant="pill"
          orientation="vertical"
        />
      </B24SidebarBody>
    </template>
    <template #navbar />

    <!-- Header -->
    <div>
      <slot name="header-title">
        <!-- / header-title / -->
      </slot>
    </div>

    <!-- Content -->
    <slot />
  </B24SidebarLayout>
</template>
