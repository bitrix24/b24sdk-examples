<script setup lang="ts">
import { computed, ref } from 'vue'
import { AjaxError } from '@bitrix24/b24jssdk'
import type { AccordionItem } from '@bitrix24/b24ui-nuxt'
import { useUserSettingsStore } from '~/stores/userSettings'
import CloudErrorIcon from '@bitrix24/b24icons-vue/main/CloudErrorIcon'
import ClockWithArrowIcon from '@bitrix24/b24icons-vue/main/ClockWithArrowIcon'

/**
 * @memo For App options we use single page
 * @see app/pages/slider/app-options.client.vue:11
 */

const { t } = useI18n()
const toast = useToast()
const page = usePageStore()

const emit = defineEmits<{ close: [boolean] }>()

// region Init ////
const { $logger } = useAppInit('UserOptionsSlideoverComponent')
const userSettings = useUserSettingsStore()

const someValue_1 = ref(userSettings.configSettings.someValue_1)
const someValue_2 = ref(userSettings.configSettings.someValue_2)
const isShowChangeColorMode = ref(userSettings.configSettings.isShowChangeColorMode)

const activeInfoItem = ref(['0'])
const infoItems = computed(() => [
  {
    label: t('page.user-options.option.demo.title'),
    icon: ClockWithArrowIcon,
    slot: 'demo'
  }
] satisfies AccordionItem[])

$logger.info('Hi from components/UserOptionsSlideover')
// endregion ////

// region Actions ////
async function makeSave() {
  try {
    // @todo need show loader from current B24Slideover
    // page.isLoading = true
    userSettings.configSettings.someValue_1 = someValue_1.value
    userSettings.configSettings.someValue_2 = someValue_2.value
    userSettings.configSettings.isShowChangeColorMode = isShowChangeColorMode.value

    await userSettings.saveSettings()

    await makeClose()
  } catch (error) {
    $logger.error(error)

    let title = t('page.user-options.error.title')
    let description = ''

    if (error instanceof AjaxError) {
      title = `[${error.name}] ${error.code} (${error.status})`
      description = `${error.message}`
    } else if (error instanceof Error) {
      description = error.message
    } else {
      description = error as string
    }

    toast.add({
      title: title,
      description,
      color: 'air-primary-alert',
      icon: CloudErrorIcon
    })
  } finally {
    // page.isLoading = false
  }
}

async function makeClose() {
	emit('close', true)
}

async function makeCancel() {
	emit('close', false)
}
// endregion ////
</script>

<template>
  <B24Slideover
    :close="{ onClick: async () => { await makeCancel() } }"
    :title="t('page.user-options.seo.title')"
    :description="t('page.user-options.seo.description')"
    :use-light-content="false"
    :b24ui="{
      content: 'sm:max-w-[650px] sm:top-[275px] sm:max-h-[calc(100%-275px)]',
    }"
  >
    <template #body>
      <B24Accordion
        v-model="activeInfoItem"
        type="multiple"
        :items="infoItems"
        :b24ui="{
          root: 'light',
          item: 'mb-[16px] last:mb-0 bg-(--ui-color-bg-content-primary) rounded-(--ui-border-radius-md) border-b-0',
          trigger: 'py-[20px] px-[20px]',
          label: 'text-(length:--ui-font-size-2xl) text-(-ui-color-text-primary)',
          leadingIcon: 'text-(--ui-color-base-60)',
          trailingIcon: 'text-(-ui-color-text-primary)',
        }"
      >
        <template #demo>
          <div class="px-4 pb-[12px]">
            <B24Separator class="mb-3" />
            <div class="flex flex-col items-start justify-between gap-[18px]">
              <B24Alert
                color="air-secondary"
                :description="$t('page.user-options.option.demo.alert')"
                :b24ui="{ description: 'text-(--ui-color-base-70)' }"
              />

              <B24FormField
                :description="$t('page.user-options.option.demo.property_someValue_1')"
              >
                <B24InputNumber
                  v-model="someValue_1"
                  class="w-[190px]"
                  size="lg"
                />
              </B24FormField>

              <B24FormField
                class="w-full"
                :description="$t('page.user-options.option.demo.property_someValue_2')"
              >
                <B24Input
                  v-model="someValue_2"
                  size="lg"
                />
              </B24FormField>

              <B24FormField
                :description="$t('page.user-options.option.demo.property_isShowChangeColorMode')"
              >
                <B24Switch
                  v-model="isShowChangeColorMode"
                  size="lg"
                />
              </B24FormField>
            </div>
          </div>
        </template>
      </B24Accordion>
    </template>

    <template #footer>
      <div class="flex flex-row gap-[10px]">
        <B24Button
          :label="t('page.user-options.actions.save')"
          color="air-primary-success"
          @click.stop="makeSave"
        />
        <B24Button
          :label="t('page.user-options.actions.cancel')"
          color="air-tertiary"
          @click.stop="makeCancel"
        />
      </div>
    </template>
  </B24Slideover>
</template>
