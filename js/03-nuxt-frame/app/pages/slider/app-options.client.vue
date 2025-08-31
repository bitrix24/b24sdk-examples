<script setup lang="ts">
import {ref, onMounted, onUnmounted, computed} from 'vue'
import { usePageStore } from '~/stores/page'
import { useUserStore } from '~/stores/user'
import { useAppSettingsStore } from '~/stores/appSettings'
import { AjaxError } from '@bitrix24/b24jssdk'
import type { B24Frame } from '@bitrix24/b24jssdk'
import type { AccordionItem } from '@bitrix24/b24ui-nuxt'
import ListIcon from '@bitrix24/b24icons-vue/main/ListIcon'
import CloudErrorIcon from '@bitrix24/b24icons-vue/main/CloudErrorIcon'
import ClockWithArrowIcon from '@bitrix24/b24icons-vue/main/ClockWithArrowIcon'

/**
 * @memo For User options we use `B24Slideover`
 * @see app/components/UserOptionsSlideover.vue
 */

definePageMeta({
  layout: false
})

const { t, locales: localesI18n, setLocale } = useI18n()
const page = usePageStore()
const toast = useToast()

// region Init ////
const { $logger, initApp, b24Helper, destroyB24Helper, usePullClient, startPullClient, processErrorGlobal } = useAppInit('SliderAppOptionsPage')
const appSettings = useAppSettingsStore()
const user = useUserStore()
const { $initializeB24Frame } = useNuxtApp()
let $b24: null | B24Frame = null

const deviceHistoryCleanupDays = ref([
  30, 60, 90, 120, 15, 180
])

const deviceHistoryCleanupDay = ref(appSettings.configSettings.deviceHistoryCleanupDays)

const infoItems = computed(() => [
  {
    label: t('page.app-options.option.history.title'),
    icon: ClockWithArrowIcon,
    slot: 'history'
  },
  {
    label: t('page.app-options.option.present.title'),
    icon: ListIcon,
    slot: 'present'
  }
] satisfies AccordionItem[])
// endregion ////

// region Actions ////
async function makeSave() {
  try {
    appSettings.configSettings.deviceHistoryCleanupDays = deviceHistoryCleanupDay.value

    await appSettings.saveSettings()

    /**
     * @todo fix this
     */
    /*/
    await getB24Helper().appOptions.save(
			{
				keyFloat: optionsData.keyFloat,
				keyInteger: optionsData.keyInteger,
				keyBool: optionsData.keyBool ? 'Y' : 'N',
				keyString: optionsData.keyString,
				keyArray: getB24Helper().appOptions.encode(
					optionsData.keyArray
				),
				keyObject: getB24Helper().appOptions.encode(
					optionsData.keyObject
				),
				keyDate: optionsData.keyDate?.toISO(),
				keyDateTime: optionsData.keyDateTime?.toISO(),
			},
			{
				moduleId: 'main',
				command: 'reload.options',
				params: {
					from: 'app.options'
				},
			}
		)
    //*/
    await makeSendPullCommand('reload.options', { from: 'app.options' })
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

async function makeSendPullCommand(command: string, params: Record<string, any> = {}) {
  try {
    page.isLoading = true

    await $b24?.callMethod(
      'pull.application.event.add',
      {
        COMMAND: command,
        PARAMS: params,
        MODULE_ID: b24Helper.value?.getModuleIdPullClient()
      }
    )

    $logger.warn('>> pull.send >>>', params)
  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: true,
      clearErrorHref: '/slider/app-options'
    })
  }
}

async function makeClose() {
  await $b24?.parent.closeApplication()
}

async function makeCancel() {
  await $b24?.parent.closeApplication()
}
// endregion ////

// region Lifecycle Hooks ////
onMounted(async () => {
  $logger.info('Hi from slider/app-options')

  try {
    page.isLoading = true

    $b24 = await $initializeB24Frame()
    await initApp($b24, localesI18n, setLocale)

    if( !user.isAdmin ) {
      throw new Error(t('page.app-options.error.notAdmin'))
    }

    page.title = t('page.app-options.seo.title')
    page.description = t('page.app-options.seo.description')

    usePullClient()
    startPullClient()
  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: true,
      clearErrorHref: '/slider/app-options'
    })
  } finally {
    page.isLoading = false
  }
})

onUnmounted(() => {
  destroyB24Helper()
})
// endregion ////
</script>

<template>
  <NuxtLayout name="slider">
    <B24Accordion
      :items="infoItems"
      :b24ui="{
          root:'light',
          item: 'mb-4 bg-(--ui-color-bg-content-primary) rounded-(--ui-border-radius-md)',
          trigger: 'py-[20px] px-[20px]',
          label: 'text-(length:--ui-font-size-2xl) text-(-ui-color-text-primary)',
          leadingIcon: 'text-(--ui-color-base-60)',
          trailingIcon: 'text-(-ui-color-text-primary)',
        }"
    >
      <template #history>
        <div class="px-4 pb-[12px]">
          <B24Separator class="mb-3" />
          <B24Alert
            class="mb-3"
            color="air-primary"
            :description="$t('page.app-options.option.history.alert')"
          />

          <B24FormField
            class="my-3"
            :label="$t('page.app-options.option.history.property')"
          >
            <B24Select
              v-model="deviceHistoryCleanupDay"
              :items="deviceHistoryCleanupDays"
              class="w-full"
            />
          </B24FormField>
        </div>
      </template>
      <template #present>
        <div class="px-4 pb-[12px]">
          <B24Separator class="mb-3" />
          <B24Alert
            class="mb-3"
            color="air-primary"
            :description="$t('page.app-options.option.present.alert')"
          />

          <B24FormField
            class="my-3"
            :label="$t('page.app-options.option.present.property')"
          >
            <B24Select
              v-model="deviceHistoryCleanupDay"
              :items="deviceHistoryCleanupDays"
              class="w-full"
            />
          </B24FormField>
        </div>
      </template>
    </B24Accordion>
    <template #footer>
      <div class="light bg-(--popup-window-background-color) flex items-center justify-center gap-3 border-t-1 border-t-(--ui-color-divider-less) shadow-top-md py-[9px] px-2 pr-(--scrollbar-width)">
        <div class="flex flex-row gap-[10px]">
          <B24Button
            :label="t('page.app-options.actions.save')"
            color="air-primary-success"
            loading-auto
            @click.stop="makeSave"
          />
          <B24Button
            :label="t('page.app-options.actions.cancel')"
            color="air-tertiary"
            @click.stop="makeCancel"
          />
        </div>
      </div>
    </template>
  </NuxtLayout>
</template>
