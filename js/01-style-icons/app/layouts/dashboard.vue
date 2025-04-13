<script setup lang="ts">
import { computed, useSlots } from 'vue'
import type { NavigationMenuItem } from '@bitrix24/b24ui-nuxt'

useHead({
  bodyAttrs: {
    class: 'text-base-master dark:text-base-150 bg-base-50 dark:bg-base-dark font-b24-system antialiased'
  }
})

const slots = useSlots()
const hasFooterNavbar = computed(() => {
  return !!slots['footer-navbar-left']
    || !!slots['footer-navbar-center']
    || !!slots['footer-right']
})

const route = useRoute()

const helpItems = computed<NavigationMenuItem[]>(() => {
  return [
    {
      label: 'b24style',
      to: 'https://bitrix24.github.io/b24style/',
      target: '_blank'
    },
    {
      label: 'b24icons',
      to: 'https://bitrix24.github.io/b24icons/',
      target: '_blank'
    },
    {
      label: 'b24jssdk',
      to: 'https://bitrix24.github.io/b24jssdk/',
      target: '_blank'
    },
    {
      label: 'b24ui',
      to: 'https://bitrix24.github.io/b24ui/',
      target: '_blank'
    }
  ]
})
</script>

<template>
  <B24StackedLayout
    :use-light-content="false"
    :b24ui="{
      header: 'border-b border-base-950/5 dark:border-white/10',
      container: route.path === '/' ? 'lg:mt-4' : ''
    }"
  >
    <!-- Header -->
    <template #navbar>
      <ProseH1 class="max-w-1/4 mb-0 ps-3 lg:ps-4 text-nowrap truncate">
        <slot name="header-title" />
      </ProseH1>
      <slot name="header-navbar" />
      <B24NavbarSpacer />
      <B24NavigationMenu
        class="max-lg:hidden"
        :items="helpItems"
        variant="link"
        orientation="horizontal"
        :b24ui="{
          link: 'text-sm text-base-500',
          linkLabelExternalIcon: 'h-3'
        }"
      />
    </template>

    <!-- Content -->
    <div
      :class="[
        hasFooterNavbar ? 'mb-[73px]' : ''
      ]"
    >
      <slot />
    </div>

    <!-- Footer -->
    <div
      v-if="hasFooterNavbar"
      class="w-full h-7xl shadow-top-sm fixed left-0 bottom-0 py-3 px-4 lg:px-6 bg-white"
    >
      <div class="flex flex-row flex-nowrap items-center justify-between gap-1">
        <div class="w-1/4 flex flex-row justify-start gap-4">
          <slot name="footer-navbar-left">
            &nbsp;
          </slot>
        </div>
        <div class="w-2/4 flex flex-row justify-center gap-4">
          <slot name="footer-navbar-center">
            &nbsp;
          </slot>
        </div>
        <div class="w-1/4 flex flex-row justify-end gap-1">
          <slot name="footer-navbar-right">
            &nbsp;
          </slot>
        </div>
      </div>
    </div>
  </B24StackedLayout>
</template>
