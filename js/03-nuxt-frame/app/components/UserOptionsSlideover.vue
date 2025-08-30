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
    }"
  >
    <template #body>
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
