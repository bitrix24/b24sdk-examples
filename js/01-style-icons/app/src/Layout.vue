<script setup lang="ts">
import { type Ref, ref } from 'vue'

interface MenuItem {
  title: string
  href: string
}

const title = ref('Local app with token storage')

const menuList: Ref<MenuItem[]> = ref([
  {
    title: 'API Docs',
    href: 'https://apidocs.bitrix24.com'
  },
  {
    title: '@bitrix24/b24phpsdk',
    href: 'https://github.com/bitrix24/b24phpsdk'
  },
  {
    title: '@bitrix24/b24style',
    href: 'https://bitrix24.github.io/b24style/reference/colors.html'
  },
  {
    title: '@bitrix24/b24icons',
    href: 'https://bitrix24.github.io/b24icons/guide/icons.html'
  }
])
</script>

<template>
  <header class="relative z-10 border-b border-gray-200 bg-white">
    <div class="px-6 py-4">
      <div class="flex flex-shrink-0 items-center justify-between gap-6">
        <div class="text-h2 text-nowrap truncate">
          {{ title }}
        </div>
        <div class="hidden sm:flex sm:flex-row gap-2 text-sm text-base-500 divide-x divide-base-400 ">
          <a
            v-for="(menuItem, menuIndex) in menuList"
            :key="menuIndex"
            :href="menuItem.href"
            target="_blank"
            class="whitespace-nowrap pl-2 hover:text-info-text hover:underline"
          >{{ menuItem.title }}</a>
        </div>
      </div>
    </div>
  </header>
  <main class="flex-auto overflow-auto px-6">
    <slot />
  </main>
</template>
