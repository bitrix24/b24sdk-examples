<script setup lang="ts">
import Settings1Icon from '@bitrix24/b24icons-vue/main/SettingsIcon'
import SearchIcon from '@bitrix24/b24icons-vue/button/SearchIcon'
import SettingsIcon from '@bitrix24/b24icons-vue/common-service/SettingsIcon'

import Logo from '~/components/Logo.vue'
import ColorMode from '~/components/ColorMode.vue'

useHead({
  bodyAttrs: {
    class: 'text-base-master dark:text-base-150 bg-base-50 dark:bg-base-dark font-b24-system antialiased'
  }
})

const route = useRoute()
type KeyType = 'install' | 'not-install' | 'type-1' | 'type-2'

const filterSettingsMap = ref<Map<KeyType, boolean>>(new Map([
  ['install', false],
  ['not-install', false],
  ['type-1', false],
  ['type-2', false]
]))

const filterSettings = computed(() => [
  {
    label: 'Installed',
    type: 'checkbox' as const,
    checked: filterSettingsMap.value.get('install'),
    onUpdateChecked(checked: boolean) {
      filterSettingsMap.value.set('install', checked)
    },
    onSelect(e: Event) {
      e.preventDefault()
    }
  },
  {
    label: 'Not installed',
    type: 'checkbox' as const,
    checked: filterSettingsMap.value.get('not-install'),
    onUpdateChecked(checked: boolean) {
      filterSettingsMap.value.set('not-install', checked)
    },
    onSelect(e: Event) {
      e.preventDefault()
    }
  },
  {
    label: 'Type 1',
    type: 'checkbox' as const,
    checked: filterSettingsMap.value.get('type-1'),
    onUpdateChecked(checked: boolean) {
      filterSettingsMap.value.set('type-1', checked)
    },
    onSelect(e: Event) {
      e.preventDefault()
    }
  },
  {
    label: 'Type 2',
    type: 'checkbox' as const,
    checked: filterSettingsMap.value.get('type-2'),
    onUpdateChecked(checked: boolean) {
      filterSettingsMap.value.set('type-2', checked)
    },
    onSelect(e: Event) {
      e.preventDefault()
    }
  }
])
</script>

<template>
  <div class="relative flex flex-col">
    <div class="fixed top-0 z-1 w-full bg-white dark:bg-base-master shadow-bottom-md flex items-center justify-between gap-0.5 sm:gap-8 pr-(--scrollbar-width)">
      <B24Link raw to="/" class="p-4 flex items-center justify-between gap-0.5">
        <Logo class="size-8 sm:size-16 text-primary" />

        <div class="hidden sm:block text-primary">
          {{ route.meta.title }}
        </div>
      </B24Link>
      <div class="pl-4 hidden sm:block">
        <div class="flex flex-row gap-2xs items-end">
          <div class="flex flex-col items-start justify-center gap-0.5">
            <div class="text-3xs leading-tight opacity-70 text-base-500">
              developer
            </div>
            <div class="text-3xs leading-tight text-base-700">
              Company Name
            </div>
          </div>
        </div>
      </div>

      <div class="grow flex items-center justify-between gap-4">
        <B24ButtonGroup no-split>
          <B24Input
            :icon="SearchIcon"
            placeholder="Search..."
            class="min-w-[110px] max-w-[210px]"
            rounded
          />
          <B24DropdownMenu
            :b24ui="{
              content: 'w-[198px] mr-[36px]'
            }"
            :items="filterSettings"
            :content="{
              align: 'end',
              contentSide: 'bottom',
              sideOffset: 2
            }"
          >
            <B24Button rounded class="self-center" :icon="Settings1Icon" color="link" depth="dark" />
          </B24DropdownMenu>
        </B24ButtonGroup>
      </div>

      <div class="px-4 flex items-center justify-end gap-0.5 sm:gap-4">
        <B24Button :icon="SettingsIcon" color="link" to="/" />
        <ColorMode />
      </div>
    </div>

    <div class="mt-24">
      <slot />
    </div>
  </div>
</template>
