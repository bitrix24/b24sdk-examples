<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { usePageStore } from '~/stores/page'
import type { B24Frame } from '@bitrix24/b24jssdk'

/**
 * @memo For User options we use `B24Slideover`
 * @see app/components/UserOptionsSlideover.vue
 */

definePageMeta({
  layout: false
})

const { t, locales: localesI18n, setLocale } = useI18n()
const page = usePageStore()

// region Init ////
const { $logger, initApp, destroyB24Helper, usePullClient, startPullClient } = useAppInit('SliderAppOptionsPage')
const { $initializeB24Frame } = useNuxtApp()
let $b24: null | B24Frame = null
// endregion ////

// region Lifecycle Hooks ////
onMounted(async () => {
  page.isLoading = true

  $b24 = await $initializeB24Frame()
  await initApp($b24, localesI18n, setLocale)

  page.title = t('page.app-options.seo.title')
  page.description = t('page.app-options.seo.description')

  usePullClient()
  startPullClient()

  page.isLoading = false

  $logger.info('Hi from slider/app-options')
})

onUnmounted(() => {
  destroyB24Helper()
})
// endregion ////
</script>

<template>
  <NuxtLayout name="slider">
    <div class="flex flex-col items-center justify-between gap-[16px]">
      <ContainerWrapper class="px-[16px] w-full">
        <B24Accordion
          sise="md"
          :items="[{
                label: 'setting1',
                slot: 'setting1'
              }]"
        >
          <template #setting1="{ item }">
            <div class="pb-[12px]">
              <ProseP>{{ item.label }}</ProseP>
              <ProseP>@todo</ProseP>
            </div>
          </template>
        </B24Accordion>
      </ContainerWrapper>
      <ContainerWrapper class="px-[16px] w-full">
        <B24Accordion
          sise="md"
          :items="[{
                label: 'setting2',
                slot: 'setting2'
              }]"
        >
          <template #setting2="{ item }">
            <div class="pb-[12px]">
              <ProseP>{{ item.label }}</ProseP>
              <ProseP>@todo</ProseP>
            </div>
          </template>
        </B24Accordion>
      </ContainerWrapper>
    </div>

    <template #footer>
      <div class="light bg-(--popup-window-background-color) flex items-center justify-center gap-3 border-t-1 border-t-(--ui-color-divider-less) shadow-top-md py-[9px] px-2 pr-(--scrollbar-width)">
        <div class="flex flex-row gap-[10px]">
          <B24Button
            :label="t('page.app-options.actions.save')"
            color="air-primary-success"
          />
          <B24Button
            :label="t('page.app-options.actions.close')"
            color="air-tertiary"
          />
        </div>
      </div>
    </template>
  </NuxtLayout>
</template>
