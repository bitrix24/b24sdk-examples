<script setup lang="ts">
import { computed } from 'vue'
import type { NavigationMenuItem, BadgeProps, SidebarLayoutInstance } from '@bitrix24/b24ui-nuxt'
import SunIcon from '@bitrix24/b24icons-vue/main/SunIcon'
import SunIconAir from '@bitrix24/b24icons-vue/outline/SunIcon'
import MoonIcon from '@bitrix24/b24icons-vue/main/MoonIcon'
import MoonIconAir from '@bitrix24/b24icons-vue/outline/MoonIcon'
import { useAppInit } from '~/composables/useAppInit'

type colorMode = 'dark' | 'light' | 'edge-dark' | 'edge-light'

const { t } = useI18n()

const slots = defineSlots()

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
      label: t('page.base_app-info.nav'),
      to: '/base/app-info'
    },
    {
      label: t('page.base_license-info.nav'),
      to: '/base/license-info'
    },
    {
      label: t('page.base_specific-methods.nav'),
      to: '/base/specific-methods'
    },
    {
      label: t('page.base_specific-parameters.nav'),
      to: '/base/specific-parameters'
    },
    {
      label: t('page.base_currency.nav'),
      to: '/base/currency'
    },
    {
      label: t('page.base_lang.nav'),
      to: '/base/lang'
    },
    {
      label: t('page.base_feedback.nav'),
      to: '/base/feedback'
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

const isUseHeader = computed(() => !['/main'].includes(route.path))
</script>

<template>
  <B24SidebarLayout
    ref="currentSidebarRef"
    :use-light-content="false"
  >
    <template #sidebar>
      <B24SidebarHeader>
        <div class="h-full flex items-center relative my-0 ps-[25px] pe-xs rtl:pe-[25px]">
          <B24Link href="/main" class="mt-0 text-(--ui-color-design-selection-content)">
            <ProseH4 class="font-medium mb-0">
              {{ $t('layout.default.sidebarHeader.title') }}
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
    <template v-if="isUseHeader" #content-top>
      <div class="w-full flex flex-col gap-[6px]">
        <div class="flex items-center gap-[12px]">
          <div class="w-full flex items-center gap-[20px]">
            <ProseH2 class="font-semibold mb-0">
              {{ pageTitle }}
            </ProseH2>
            <slot name="top-actions-start" />
          </div>
          <div
            v-if="!!slots['top-actions-end']"
            class="flex-1 hidden sm:flex flex-row items-center justify-end gap-[12px]"
          >
            <slot name="top-actions-end" />
          </div>
        </div>
        <ProseP v-if="pageDescription.length > 0" accent="less" class="mb-0">
          {{ pageDescription }}
        </ProseP>
      </div>
    </template>

    <template v-if="!!slots['header-actions'] && isUseHeader" #content-actions>
      <slot name="header-actions" />
    </template>

    <!-- Content -->
    <slot />
  </B24SidebarLayout>
</template>
