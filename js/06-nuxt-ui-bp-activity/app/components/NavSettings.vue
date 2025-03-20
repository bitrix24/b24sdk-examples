<script setup lang="ts">
import { computed } from 'vue'
import { ModalForIntegrators } from '#components'
import * as locales from '@bitrix24/b24ui-nuxt/locale'
import PulseCircleIcon from '@bitrix24/b24icons-vue/main/PulseCircleIcon'
import InterconnectionIcon from '@bitrix24/b24icons-vue/crm/InterconnectionIcon'
import SettingsIcon from '@bitrix24/b24icons-vue/common-service/SettingsIcon'
import SunIcon from '@bitrix24/b24icons-vue/main/SunIcon'
import MoonIcon from '@bitrix24/b24icons-vue/main/MoonIcon'
import HelpIcon from '@bitrix24/b24icons-vue/main/HelpIcon'

const { t } = useI18n()

const toast = useToast()
const colorMode = useColorMode()

const isDark = computed({
  get() {
    return colorMode.value === 'dark'
  },
  set() {
    colorMode.preference = colorMode.value === 'dark' ? 'light' : 'dark'
  }
})

const overlay = useOverlay()
const modalForIntegrators = overlay.create(ModalForIntegrators)

const helpItems = computed(() => {
  return [
    {
      label: t('component.nav.settings.settings'),
      icon: SettingsIcon,
      onSelect() {
        toast.add({ title: 'Action', description: 'Settings clicked' })
      }
    },
    {
      label: t('component.nav.settings.integrators'),
      icon: InterconnectionIcon,
      async onSelect() {
        const isSave = await modalForIntegrators.open()
        if (!isSave) {
          return
        }
      }
    },
    {
      type: 'label' as const
    },
    {
      label: t('component.nav.settings.help'),
      icon: HelpIcon,
      onSelect() {
        toast.add({ title: 'Action', description: 'Help clicked' })
      }
    },
    {
      label: t('component.nav.settings.support'),
      icon: PulseCircleIcon,
      onSelect() {
        toast.add({ title: 'Action', description: 'Support clicked' })
      }
    },
    {
      type: 'label' as const
    },
    {
      label: isDark.value ? t('component.nav.settings.dark') : t('component.nav.settings.light'),
      icon: isDark.value ? MoonIcon : SunIcon,
      kbds: ['shift', 'd'],
      onSelect(e: Event) {
        e?.preventDefault()
        isDark.value = !isDark.value
      }
    }
  ]
})

defineShortcuts(extractShortcuts(helpItems.value))
</script>

<template>
  <B24NavigationMenu
    :items="helpItems"
    variant="pill"
    orientation="vertical"
  />
</template>
