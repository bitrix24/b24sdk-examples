<script setup lang="ts">
import { computed } from 'vue'
import { usePageStore } from '~/stores/page'
import { useAppSettingsStore } from '~/stores/appSettings'
import { useUserSettingsStore } from '~/stores/userSettings'
import type { NavigationMenuItem, BadgeProps } from '@bitrix24/b24ui-nuxt'
import type { B24Frame } from '@bitrix24/b24jssdk'
import { useAppInit } from '~/composables/useAppInit'
import UserOptionsSlideover from '~/components/UserOptionsSlideover.vue'
import SunIcon from '@bitrix24/b24icons-vue/main/SunIcon'
import SunIconAir from '@bitrix24/b24icons-vue/outline/SunIcon'
import MoonIcon from '@bitrix24/b24icons-vue/main/MoonIcon'
import MoonIconAir from '@bitrix24/b24icons-vue/outline/MoonIcon'
import SpannerIcon from '@bitrix24/b24icons-vue/main/SpannerIcon'
import SettingsIcon from '@bitrix24/b24icons-vue/outline/SettingsIcon'
import BtnSpinnerIcon from '@bitrix24/b24icons-vue/button-specialized/BtnSpinnerIcon'

// region Init ////
type colorMode = 'dark' | 'light' | 'edge-dark' | 'edge-light'

const { t } = useI18n()
const slots = defineSlots()
const overlay = useOverlay()
const appSettings = useAppSettingsStore()
const userSettings = useUserSettingsStore()

const userOptionsSlideover = overlay.create(UserOptionsSlideover)

const colorMode = useColorMode()
if (colorMode.value === 'system') {
  colorMode.preference = 'edge-dark'
}

const mode = ref<colorMode>(colorMode.value as colorMode)

const route = useRoute()
const page = usePageStore()
useSeoMeta({
  title: page.title,
  description: page.description
})

const { $logger, processErrorGlobal } = useAppInit('LayoutDefault')
const { $initializeB24Frame } = useNuxtApp()
const $b24: B24Frame = await $initializeB24Frame()
$logger.info('Hi from layouts/default')
// endregion ////

// region ColorMode ////
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
// endregion ////

// region Nav ////
const pages = computed<NavigationMenuItem[]>(() => {
  return [
    {
      label: t('layout.default.navbarHeader.home'),
      to: '/main.html'
    },
    {
      label: t('layout.default.navbarHeader.pages'),
      type: 'trigger' as NavigationMenuItem['type'],
      active: route.path.includes(`/base`),
      class: 'lg:hidden',
      children: [
        {
          label: t('page.base_app-info.nav'),
          to: '/base/app-info.html'
        },
        {
          label: t('page.base_license-info.nav'),
          to: '/base/license-info.html'
        },
        {
          label: t('page.base_specific-methods.nav'),
          to: '/base/specific-methods.html'
        },
        {
          label: t('page.base_specific-parameters.nav'),
          to: '/base/specific-parameters.html'
        },
        {
          label: t('page.base_currency.nav'),
          to: '/base/currency.html'
        },
        {
          label: t('page.base_lang.nav'),
          to: '/base/lang.html'
        },
        {
          label: t('page.base_feedback.nav'),
          to: '/base/feedback.html'
        }
      ]
    },
    {
      class: 'hidden lg:flex',
      label: t('page.base_app-info.nav'),
      to: '/base/app-info.html'
    },
    {
      class: 'hidden lg:flex',
      label: t('page.base_license-info.nav'),
      to: '/base/license-info.html'
    },
    {
      class: 'hidden lg:flex',
      label: t('page.base_specific-methods.nav'),
      to: '/base/specific-methods.html'
    },
    {
      class: 'hidden lg:flex',
      label: t('page.base_specific-parameters.nav'),
      to: '/base/specific-parameters.html'
    },
    {
      class: 'hidden lg:flex',
      label: t('page.base_currency.nav'),
      to: '/base/currency.html'
    },
    {
      class: 'hidden lg:flex',
      label: t('page.base_lang.nav'),
      to: '/base/lang.html'
    },
    {
      class: 'hidden lg:flex',
      label: t('page.base_feedback.nav'),
      to: '/base/feedback.html'
    }
  ]
})

const helpItems = computed(() => {
  const result: NavigationMenuItem[] = []

  if (userSettings.configSettings.isShowChangeColorMode) {
    result.push({
      label: colorModeLabel.value,
      icon: colorModeIcon.value,
      badge: {
        label: 'shift + d',
        color: 'air-secondary' as BadgeProps['color']
      },
      onSelect(e: Event) {
        e?.preventDefault()
        toggleMode()
      }
    })
  }

  return result
})

defineShortcuts({
  shift_d: () => {
    toggleMode()
  }
})
// endregion ////

// region Template ////
const isUseHeader = computed(() => !['/main'].includes(route.path))
// endregion ////

// region Actions ////
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

const makeOpenFeedBack = async() => {
  try {
    await $b24?.slider.openSliderAppPage(
      {
        place: 'feedback',
        bx24_width: 600,
        bx24_title: t('page.base_feedback.seo.title'),
      }
    )
  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: false,
      clearErrorHref: '/main.html'
    })
  }
}

const makeOpenOptionApp = async() => {
  try {
    await $b24?.slider.openSliderAppPage(
      {
        place: 'app-options',
        bx24_width: 650,
        bx24_title: t('page.app-options.seo.title'),
      }
    )
  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: false,
      clearErrorHref: '/main.html'
    })
  }
}

const makeOpenOptionUser = async() => {
  const instance = userOptionsSlideover.open()
  await instance.result
}
// endregion ////
</script>

<template>
  <B24SidebarLayout
    :use-light-content="false"
    :b24ui="{
      container: 'px-[22px] lg:px-[22px] lg:pt-0 mt-[12px]'
    }"
  >
    <template #navbar>
      <B24NavbarSection>
        <B24NavigationMenu
          :items="pages"
          orientation="horizontal"
        />
      </B24NavbarSection>
      <B24NavbarSpacer />
      <B24NavbarSection class="flex-row items-center justify-start gap-4">
        <ClientOnly>
          <B24NavigationMenu
            :items="helpItems"
            orientation="horizontal"
          />
        </ClientOnly>
        <B24ButtonGroup size="sm">
          <B24Badge
            size="xs"
            :label="`${appSettings.isTrial ? 'trial ' : ''}v:${appSettings.version}`"
            color="air-tertiary"
          />
          <B24Tooltip :content="{ side: 'bottom' }" :text="$t('layout.default.navbarHeader.optionApp')">
            <B24Button
              :icon="SpannerIcon"
              color="air-secondary-accent"
              @click="makeOpenOptionApp"
            />
          </B24Tooltip>
          <B24Tooltip :content="{ side: 'bottom' }" :text="$t('layout.default.navbarHeader.optionUser')">
            <B24Button
              :icon="SettingsIcon"
              color="air-secondary-accent"
              @click="makeOpenOptionUser"
            />
          </B24Tooltip>
        </B24ButtonGroup>
        <B24Button
          :label="$t('layout.default.navbarHeader.feedback')"
          color="air-secondary-accent"
          size="sm"
          @click="makeOpenFeedBack"
        />
      </B24NavbarSection>
    </template>

    <div v-if="page.isLoading">
      <div class="cursor-wait isolate absolute z-1000 inset-0 w-full flex flex-row flex-nowrap items-center justify-center h-[400px] min-h-[400px]">
        <BtnSpinnerIcon
          class="text-(--ui-color-design-plain-content-icon-secondary) size-[110px] animate-spin-slow"
          aria-hidden="true"
        />
      </div>
    </div>

    <!-- Header -->
    <template v-if="isUseHeader && !page.isLoading" #content-top>
      <div class="w-full flex flex-col gap-[4px]">
        <div class="flex items-center gap-[12px]">
          <div class="w-full flex items-center gap-[20px]">
            <ProseH2 class="font-(--ui-font-weight-semi-bold) mb-0 text-(length:--ui-font-size-4xl)/[calc(var(--ui-font-size-4xl)+2px)]">
              {{ page.title }}
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
        <ProseP v-if="page.description.length > 0" accent="less" class="mb-0">
          {{ page.description }}
        </ProseP>
      </div>
    </template>

    <template v-if="!!slots['header-actions'] && isUseHeader && !page.isLoading" #content-actions>
      <slot name="header-actions" />
    </template>

    <!-- Content -->
    <div v-show="!page.isLoading">
      <slot />
    </div>

    <template v-if="!!slots['footer'] && !page.isLoading" #content-bottom>
      <slot name="footer" />
    </template>
  </B24SidebarLayout>
</template>
