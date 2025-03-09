<script setup lang="ts">
import Logo from '~/components/Logo.vue'
import NavSettings from '~/components/NavSettings.vue'

useHead({
  bodyAttrs: {
    class: 'text-base-master dark:text-base-150 bg-base-50 dark:bg-base-dark font-b24-system antialiased'
  }
})

const route = useRoute()
</script>

<template>
  <div class="relative flex flex-col">
    <div class="fixed py-2 ps-4 pe-(--scrollbar-width) top-0 z-1 min-h-14 w-full bg-white dark:bg-base-master shadow-bottom-md flex items-center justify-between gap-4 sm:gap-8">
      <slot name="header-start">
        <B24Link raw to="/" class="flex items-center justify-between gap-0.5">
          <Logo class="size-8 text-primary" />
          <div class="hidden sm:block text-primary leading-none">
            {{ route.meta.title }}
          </div>
        </B24Link>
        <div class="hidden sm:block">
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
      </slot>

      <div class="grow flex items-center justify-between gap-4">
        <slot name="header-middle">
          <!-- / header-middle / -->
        </slot>
      </div>

      <div class="pe-4 flex items-center justify-end gap-0.5 sm:gap-4">
        <slot name="header-end">
          <NavSettings />
        </slot>
      </div>
    </div>

    <div class="mt-16">
      <slot />
    </div>
  </div>
</template>
