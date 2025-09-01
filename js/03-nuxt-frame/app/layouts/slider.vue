<script setup lang="ts">
import { usePageStore } from '~/stores/page'
import BtnSpinnerIcon from '@bitrix24/b24icons-vue/button-specialized/BtnSpinnerIcon'

// region Init ////
useHead({
  bodyAttrs: {
    // 'dark' | 'light' | 'edge-dark' | 'edge-light'
    class: `light`
  }
})

const slots = defineSlots()

const page = usePageStore()
useSeoMeta({
  title: page.title,
  description: page.description
})
// endregion ////
</script>

<template>
  <B24SidebarLayout
    :use-light-content="false"
    :b24ui="{
      root: '',
      container: 'mt-[20px]'
    }"
  >
    <div v-if="page.isLoading">
      <div class="cursor-wait isolate absolute z-1000 inset-0 w-full flex flex-row flex-nowrap items-center justify-center h-[400px] min-h-[400px]">
        <BtnSpinnerIcon
          class="text-(--ui-color-design-plain-content-icon-secondary) size-[110px] animate-spin-slow"
          aria-hidden="true"
        />
      </div>
    </div>

    <!-- Header -->
    <template v-if="!page.isLoading" #content-top>
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
        <ProseP v-if="page.description.length > 0" small accent="less" class="mb-0">
          {{ page.description }}
        </ProseP>
      </div>
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
