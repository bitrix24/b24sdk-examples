<script setup lang="ts">
/**
 * @memo For App options we use single page
 * @see app/pages/slider/app-options.client.vue:11
 */
const { t } = useI18n()
const emit = defineEmits<{ close: [boolean] }>()
</script>

<template>
  <B24Slideover
    :close="{ onClick: () => emit('close', false) }"
    :title="t('page.user-options.seo.title')"
    :description="t('page.user-options.seo.description')"
    :use-light-content="false"
    :b24ui="{
      content: 'sm:max-w-[970px] sm:top-[275px] sm:max-h-[calc(100%-275px)]',
      sidebarLayoutRoot: [
        'edge-dark',
        'edge-dark:[--air-theme-bg-color:#7c235b]',
        'edge-dark:[--air-theme-bg-size:cover]',
        'edge-dark:[--air-theme-bg-repeat:no-repeat]',
        'edge-dark:[--air-theme-bg-position:0_0]',
        'edge-dark:[--air-theme-bg-attachment:local]',
        'edge-dark:[--air-theme-bg-image:url(/bg/edge-dark-v2.jpg)]',
        'edge-dark:[--air-theme-bg-image-blurred:url(/bg/edge-dark-v2-blurred.webp)]'
      ].join(' '),
      sidebarLayoutLoadingIcon: 'text-(--ui-color-gray-70)'
    }"
  >
    <template #body>
      @todo
    </template>

    <template #footer>
      <div class="flex flex-row gap-[10px]">
        <B24Button
          :label="t('page.user-options.actions.save')"
          color="air-primary-success"
          @click="emit('close', true)"
        />
        <B24Button
          :label="t('page.user-options.actions.close')"
          color="air-tertiary"
          @click="emit('close', false)"
        />
      </div>
    </template>
  </B24Slideover>
</template>
