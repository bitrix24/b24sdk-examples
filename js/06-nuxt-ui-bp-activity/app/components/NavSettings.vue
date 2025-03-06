<script setup lang="ts">
import { ref } from 'vue'
import PulseCircleIcon from '@bitrix24/b24icons-vue/main/PulseCircleIcon'
import ItemIcon from '@bitrix24/b24icons-vue/crm/ItemIcon'
import InterconnectionIcon from '@bitrix24/b24icons-vue/crm/InterconnectionIcon'
import SettingsIcon from '@bitrix24/b24icons-vue/common-service/SettingsIcon'
import SunIcon from '@bitrix24/b24icons-vue/main/SunIcon'
import MoonIcon from '@bitrix24/b24icons-vue/main/MoonIcon'

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

const helpItems = ref([
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
  }
])

defineShortcuts(extractShortcuts(helpItems.value))
</script>

<template>
  <B24DropdownMenu
    :items="helpItems"
    :content="{
      align: 'start',
      side: 'left',
      sideOffset: 2
    }"
    :b24ui="{
      content: 'w-[240px]'
    }"
  >
    <B24Button
      :icon="SettingsIcon"
      color="link"
      size="xs"
    />
  </B24DropdownMenu>
</template>
