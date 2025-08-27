<script setup lang="ts">
import { computed } from 'vue'
import type { NavigationMenuItem, BadgeProps, SidebarLayoutInstance } from '@bitrix24/b24ui-nuxt'
import SunIcon from '@bitrix24/b24icons-vue/main/SunIcon'
import SunIconAir from '@bitrix24/b24icons-vue/outline/SunIcon'
import MoonIcon from '@bitrix24/b24icons-vue/main/MoonIcon'
import MoonIconAir from '@bitrix24/b24icons-vue/outline/MoonIcon'
import { useAppInit } from '~/composables/useAppInit'

type colorMode = 'dark' | 'light' | 'edge-dark' | 'edge-light'

const colorMode = useColorMode()
if (colorMode.value === 'system') {
  colorMode.preference = 'edge-dark'
}

const mode = ref<colorMode>(colorMode.value as colorMode)

const route = useRoute()
const pageTitle: ComputedRef<string> = computed(() => (route.meta?.pageTitle ?? '') as string)
const pageDescription: ComputedRef<string> = computed(() => (route.meta?.pageDescription ?? '') as string)
useSeoMeta({
  title: pageTitle.value,
  description: pageDescription.value
})

const itemsForColorMode = computed(() => {
  return [
    {
      label: 'dark',
      code: 'dark',
      icon: MoonIcon
    },
    {
      label: 'light',
      code: 'light',
      icon: SunIcon
    },
    {
      label: 'edge-dark',
      code: 'edge-dark',
      icon: MoonIconAir
    },
    {
      label: 'edge-light',
      code: 'edge-light',
      icon: SunIconAir
    }
  ]
})

function toggleMode() {
  switch (mode.value) {
    case 'dark':
      mode.value = 'light'
      colorMode.preference = 'light'
      break
    case 'light':
      mode.value = 'edge-dark'
      colorMode.preference = 'edge-dark'
      break
    case 'edge-dark':
      mode.value = 'edge-light'
      colorMode.preference = 'edge-light'
      break
    case 'edge-light':
    default:
      mode.value = 'dark'
      colorMode.preference = 'dark'
      break
  }
}

const colorModeIcon = computed(() => {
  const theme = itemsForColorMode.value.find((row) => {
    return row.code === mode.value
  })

  if (theme) {
    return theme.icon
  }

  return MoonIcon
})

const colorModeLabel = computed(() => {
  const theme = itemsForColorMode.value.find((row) => {
    return row.code === mode.value
  })

  if (theme) {
    return theme.label
  }

  return mode.value
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
    label: colorModeLabel.value,
    icon: colorModeIcon.value,
    kbds: ['shift', 'd'],
    badge: {
      label: 'shift + d',
      color: 'air-secondary' as BadgeProps['color']
    },
    onSelect(e: Event) {
      e?.preventDefault()
      toggleMode()
    }
  })

  return result
})

defineShortcuts(extractShortcuts(helpItems.value))

const { setRootSideBarApi } = useAppInit()

const currentSidebarRef = ref<SidebarLayoutInstance | null>(null)

onMounted(() => {
  if (currentSidebarRef.value) {
    setRootSideBarApi(currentSidebarRef?.value.api as unknown as SidebarLayoutInstance['api'])
  }
})
</script>

<template>
  <B24SidebarLayout
    ref="currentSidebarRef"
    :use-light-content="!(['/', '/core/use-result', '/tools/use-logger', '/hook/testing-rest-api-calls'].includes(route.path))"
  >
    <template #sidebar>
      <B24SidebarHeader>
        <div class="h-full flex items-center relative my-0 ps-[25px] pe-xs rtl:pe-[25px]">
          <B24Link href="/" class="mt-0 text-(--ui-color-design-selection-content)">
            <ProseH4 class="font-medium mb-0">
              Bitrix24::Hooks
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
        <ClientOnly>
          <B24NavigationMenu
            :items="helpItems"
            orientation="vertical"
            :b24ui="{
              linkLeadingBadge: '-top-[10px] left-[34px]'
            }"
          />
        </ClientOnly>
      </B24SidebarBody>
    </template>
    <!-- / <template #navbar /> / -->

    <!-- Header -->
    <template v-if="!(['/', '/core/use-result'].includes(route.path))" #content-top>
      <div class="w-full flex flex-col gap-[20px]">
        <div class="lg:backdrop-blur-sm lg:backdrop-brightness-110 px-[15px] py-[10px] flex flex-col items-start justify-between gap-[20px] rounded-(--ui-border-radius-md)">
          <!-- / header-title / -->
          <div class="w-full flex flex-row items-center justify-between gap-[20px]">
            <div class="flex-1 flex flex-row items-center justify-end gap-[12px]">
              <div class="flex-1">
                <ProseH2 class="text-(--b24ui-typography-label-color) leading-[29px] font-(--ui-font-weight-light)">
                  {{ pageTitle }}
                </ProseH2>
                <ProseP small accent="less">
                  {{ pageDescription }}
                </ProseP>
              </div>
            </div>
          </div>
          <!-- / header-actions / -->
          <div class="flex-1 hidden sm:flex flex-row items-center justify-end gap-[12px]">
            <slot name="header-actions" />
          </div>
        </div>
      </div>
    </template>

    <!-- Content -->
    <slot />
  </B24SidebarLayout>
</template>
