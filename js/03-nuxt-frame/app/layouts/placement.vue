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
      root: 'overflow-y-hidden',
      container: 'mt-0 p-0 lg:p-0'
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
    <!-- Content -->
    <div v-show="!page.isLoading">
      <slot />
    </div>

    <template v-if="!!slots['footer'] && !page.isLoading" #content-bottom>
      <slot name="footer" />
    </template>
  </B24SidebarLayout>
</template>

<style scoped>
.--app {
  scrollbar-gutter: auto;
}
</style>
