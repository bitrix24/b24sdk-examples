<script setup lang="ts">
import { computed } from 'vue'
import { Logo } from '#components'
import type { NavigationMenuItem } from '@bitrix24/b24ui-nuxt'
import WheelIcon from '@bitrix24/b24icons-vue/common-service/WheelIcon'

useHead({
  bodyAttrs: {
    class: 'text-base-master dark:text-base-150 bg-base-50 dark:bg-base-dark font-b24-system antialiased'
  }
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
    <template #sidebar>
      <B24SidebarHeader>
        <B24SidebarSection class="ps-[18px] flex-row  items-center justify-start gap-0.5 text-primary">
          <Logo class="size-8" />
          <ProseH4 class="mb-0 leading-none">
            {{ '@todo app.title' }}
          </ProseH4>
        </B24SidebarSection>
      </B24SidebarHeader>
      <B24SidebarBody>
        @todo Cat
        <B24SidebarSpacer />
        <B24NavigationMenu
          :items="helpItems"
          variant="pill"
          orientation="vertical"
        />
      </B24SidebarBody>
      <B24SidebarFooter>
        @todo IntegratorNav
      </B24SidebarFooter>
    </template>

    <!-- Header -->
    <template #navbar>
      <div class="ps-[18px]">
        <slot name="header-title" />
      </div>
      <B24NavbarSection>
        <B24Button
          color="link"
          size="sm"
          normal-case
          :icon="WheelIcon"
          :b24ui="{
            base: 'font-light',
            leadingIcon: 'text-info-text'
          }"
        >
          <div class="flex flex-col">
            <div class="text-3xs leading-tight opacity-70 text-base-500 items-center">
              use case
            </div>
            <div
              class="text-sm text-primary-link cursor-pointer transition-opacity hover:opacity-80 border-b border-dashed border-b-primary-link"
            >
              Some action
            </div>
          </div>
        </B24Button>
        <div class="flex flex-row gap-2xs items-end">
          <WheelIcon class="size-xl2 text-info-text" />
          <div class="flex flex-col">
            <div class="text-3xs leading-tight opacity-70 text-base-500 items-center">
              use case
            </div>
            <div
              class="text-sm text-primary-link cursor-pointer transition-opacity hover:opacity-80 border-b border-dashed border-b-primary-link"
              onclick="alert('[Some action].click')"
            >
              Some action
            </div>
          </div>
        </div>
      </B24NavbarSection>
      <B24NavbarSection>
        @todo B24NavbarSection 1
      </B24NavbarSection>
      <B24NavbarSpacer />
      <B24NavigationMenu
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
    <slot />
  </B24StackedLayout>
</template>
