<script setup lang="ts">
import { computed } from 'vue'
import { UserNav, Logo, ModalForIntegrators } from '#components'
import { EActivityCategory } from '~/types'
import type { NavigationMenuItem } from '@bitrix24/b24ui-nuxt'
import useSearch from '~/composables/useSearch'
import PulseCircleIcon from '@bitrix24/b24icons-vue/main/PulseCircleIcon'
import InterconnectionIcon from '@bitrix24/b24icons-vue/crm/InterconnectionIcon'
import SettingsIcon from '@bitrix24/b24icons-vue/common-service/SettingsIcon'
import SunIcon from '@bitrix24/b24icons-vue/main/SunIcon'
import MoonIcon from '@bitrix24/b24icons-vue/main/MoonIcon'
import HelpIcon from '@bitrix24/b24icons-vue/main/HelpIcon'

const { t } = useI18n()
const route = useRoute()
const colorMode = useColorMode()

useHead({
  bodyAttrs: {
    class: 'text-base-master dark:text-base-150 bg-base-50 dark:bg-base-dark font-b24-system antialiased'
  }
})

const overlay = useOverlay()
const modalForIntegrators = overlay.create(ModalForIntegrators)

const isDark = computed({
  get() {
    return colorMode.value === 'dark'
  },
  set() {
    colorMode.preference = colorMode.value === 'dark' ? 'light' : 'dark'
  }
})

const { categoryActive } = useSearch()

const categories = computed(() => (handleClick: () => void) => {
  return [
    {
      label: t('composables.useSearchInput.categories.all'),
      value: 'all',
      active: categoryActive.value === 'all',
      onSelect(e: Event) {
        e.preventDefault()
        categoryActive.value = 'all'

        handleClick()
      }
    },
    ...Object.values(EActivityCategory).map((value) => {
      return {
        label: t(`composables.useSearchInput.categories.${value}`),
        value,
        active: categoryActive.value === value,
        onSelect(e: Event) {
          e.preventDefault()
          categoryActive.value = value

          handleClick()
        }
      }
    })
  ] satisfies NavigationMenuItem[]
})

const helpItems = computed(() => (handleClick: () => void) => {
  return [
    {
      label: t('component.nav.settings.settings'),
      icon: SettingsIcon,
      to: '/'
    },
    {
      label: t('component.nav.settings.integrators'),
      icon: InterconnectionIcon,
      async onSelect(e: Event) {
        e.preventDefault()
        handleClick()

        const isSave = await modalForIntegrators.open()
        if (!isSave) {
          return
        }
      }
    },
    {
      label: t('component.nav.settings.help'),
      icon: HelpIcon,
      to: 'https://apidocs.bitrix24.com/',
      target: '_blank'
    },
    {
      label: t('component.nav.settings.support'),
      icon: PulseCircleIcon,
      to: 'https://helpdesk.bitrix24.com/',
      target: '_blank'
    },
    {
      label: isDark.value ? t('component.nav.settings.dark') : t('component.nav.settings.light'),
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
    }
  ] satisfies NavigationMenuItem[]
})

defineShortcuts(extractShortcuts(helpItems.value(() => {})))
</script>

<template>
  <B24SidebarLayout
    :use-light-content="route.path !== '/activity-list'"
  >
    <template #sidebar="{ handleClick }">
      <B24SidebarHeader>
        <B24SidebarSection class="ps-[18px] flex-row  items-center justify-start gap-0.5 text-primary">
          <Logo class="size-8" />
          <ProseH4 class="mb-0 leading-none">
            {{ $t('app.title') }}
          </ProseH4>
        </B24SidebarSection>
      </B24SidebarHeader>
      <B24SidebarBody>
        <B24NavigationMenu
          :items="categories(handleClick)"
          variant="pill"
          orientation="vertical"
        />
        <B24SidebarSpacer />
        <B24NavigationMenu
          :items="helpItems(handleClick)"
          variant="pill"
          orientation="vertical"
        />
      </B24SidebarBody>
      <B24SidebarFooter>
        <UserNav />
      </B24SidebarFooter>
    </template>

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
