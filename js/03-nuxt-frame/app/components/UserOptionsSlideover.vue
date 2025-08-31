<script setup lang="ts">
import { ref } from 'vue'
import { AjaxError } from '@bitrix24/b24jssdk'
import { useUserSettingsStore } from '~/stores/userSettings'
import ListIcon from '@bitrix24/b24icons-vue/main/ListIcon'
import CloudErrorIcon from '@bitrix24/b24icons-vue/main/CloudErrorIcon'
import ClockWithArrowIcon from '@bitrix24/b24icons-vue/main/ClockWithArrowIcon'

/**
 * @memo For App options we use single page
 * @see app/pages/slider/app-options.client.vue:11
 */

const { t } = useI18n()
const toast = useToast()

const emit = defineEmits<{ close: [boolean] }>()

// region Init ////
const { $logger } = useAppInit('UserOptionsSlideoverComponent')
const userSettings = useUserSettingsStore()

const deviceHistoryCleanupDays = ref([
  30, 60, 90, 120, 15, 180
])

const deviceHistoryCleanupDay = ref(userSettings.configSettings.deviceHistoryCleanupDays)

$logger.info('Hi from components/UserOptionsSlideover')
// endregion ////

// region Actions ////
async function makeSave() {
  try {
    userSettings.configSettings.deviceHistoryCleanupDays = deviceHistoryCleanupDay.value

    await userSettings.saveSettings()

    await makeClose()
  } catch (error) {
    $logger.error(error)

    let title = 'Error'
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
      content: 'sm:max-w-[970px] sm:top-[275px] sm:max-h-[calc(100%-275px)]',
    }"
  >
    <template #body>
      <B24Collapsible
        :default-open="true"
        class="light mb-4 flex flex-col gap-0 w-full bg-(--ui-color-bg-content-primary) rounded"
      >
        <B24Button
          class="group w-full"
          :label="$t('component.settings.slider.history.title')"
          :icon="ClockWithArrowIcon"
          use-dropdown
          block
          size="lg"
          :b24ui="{
            trailingIcon: 'group-data-[state=open]:rotate-180 transition-transform duration-200'
          }"
        />
        <template #content>
          <div class="px-4 mb-3">
            <B24Separator class="mb-3" />

            <B24Alert
              color="air-primary"
              :description="$t('component.settings.slider.history.alert')"
            />

            <B24FormField
              class="my-3"
              :label="$t('component.settings.slider.history.property')"
            >
              <B24Select
                v-model="deviceHistoryCleanupDay"
                :items="deviceHistoryCleanupDays"
                class="w-full"
              />
            </B24FormField>
          </div>
        </template>
      </B24Collapsible>

      <B24Collapsible
        :default-open="false"
        class="flex flex-col gap-0 w-full bg-(--ui-color-bg-content-primary) rounded"
      >
        <B24Button
          normal-case
          class="group w-full"
          :label="$t('component.settings.slider.log.title')"
          :icon="ListIcon"
          use-dropdown
          block
          size="lg"
          :b24ui="{
            trailingIcon: 'group-data-[state=open]:rotate-180 transition-transform duration-200'
          }"
        />
        <template #content>
          <div class="px-4 mb-3">
            <B24Separator class="mb-3" />
            <B24Alert
              class="mb-3"
              color="air-primary"
              :description="$t('component.settings.slider.log.alert')"
            />

            <B24FormField
              class="my-3"
              :label="$t('component.settings.slider.history.property')"
            >
              <B24Select
                v-model="deviceHistoryCleanupDay"
                :items="deviceHistoryCleanupDays"
                class="w-full"
              />
            </B24FormField>
          </div>
        </template>
      </B24Collapsible>
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
