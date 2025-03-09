<script setup lang="ts">
import { nextTick, ref } from 'vue'
import { scrollToTop } from '~/utils/scrollToTop'
import * as locales from '@bitrix24/b24ui-nuxt/locale'
import PulseCircleIcon from '@bitrix24/b24icons-vue/main/PulseCircleIcon'
import ItemIcon from '@bitrix24/b24icons-vue/crm/ItemIcon'
import InterconnectionIcon from '@bitrix24/b24icons-vue/crm/InterconnectionIcon'
import SettingsIcon from '@bitrix24/b24icons-vue/common-service/SettingsIcon'
import SunIcon from '@bitrix24/b24icons-vue/main/SunIcon'
import MoonIcon from '@bitrix24/b24icons-vue/main/MoonIcon'
import EarthLanguageIcon from '@bitrix24/b24icons-vue/main/EarthLanguageIcon'

const { locale, locales: localesI18n, setLocale } = useI18n()

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

const dir = computed(() => locales[locale.value].dir)

const langMap = ref<Map<string, boolean>>(new Map(
  Object.values(localesI18n.value).map(lang => [lang.code, false])
))
langMap.value.set(locale.value, true)

const helpItems = computed(() => {
  return [
    {
      label: 'Settings',
      icon: SettingsIcon,
      onSelect() {
        toast.add({ title: 'Action', description: 'Settings clicked' })
      }
    },
    {
      label: 'For integrators',
      icon: InterconnectionIcon,
      onSelect() {
        toast.add({ title: 'Action', description: 'For integrators clicked' })
      }
    },
    {
      type: 'separator' as const
    },
    {
      label: 'Help',
      icon: ItemIcon,
      onSelect() {
        toast.add({ title: 'Action', description: 'Help clicked' })
      }
    },
    {
      label: 'Support',
      icon: PulseCircleIcon,
      onSelect() {
        toast.add({ title: 'Action', description: 'Support clicked' })
      }
    },
    {
      type: 'separator' as const
    },
    {
      label: isDark.value ? 'Dark' : 'Light',
      icon: isDark.value ? MoonIcon : SunIcon,
      kbds: ['shift', 'd'],
      onSelect(e: Event) {
        e?.preventDefault()
        isDark.value = !isDark.value
      }
    },
    {
      label: 'Lang',
      icon: EarthLanguageIcon,
      children: localesI18n.value.map((localeRow) => {
        return {
          label: localeRow.name,
          type: 'checkbox' as const,
          checked: langMap.value.get(localeRow.code),
          onUpdateChecked() {
            [...langMap.value.keys()].forEach((lang) => {
              langMap.value.set(lang, false)
            })
            langMap.value.set(localeRow.code, true)

            setLocale(localeRow.code)

            nextTick(() => {
              scrollToTop()
            })
          }
        }
      })
    }
  ]
})

defineShortcuts(extractShortcuts(helpItems.value))
</script>

<template>
  <B24DropdownMenu
    :items="helpItems"
    :content="{
      align: 'start',
      side: dir === 'ltr' ? 'left' : 'right',
      sideOffset: 2
    }"
    :b24ui="{
      content: 'w-[240px] max-h-[245px]'
    }"
  >
    <B24Button
      :icon="SettingsIcon"
      color="link"
      size="xs"
    />
  </B24DropdownMenu>
</template>
