<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { navigateTo } from '#imports'
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
const { $logger, moduleId, initApp, destroyB24Helper, usePullClient, startPullClient, processErrorGlobal } = useAppInit('SliderAppOptionsPage')
const appSettings = useAppSettingsStore()
const user = useUserStore()
const { $initializeB24Frame } = useNuxtApp()
let $b24: null | B24Frame = null

const deviceHistoryCleanupDays = ref([
  15, 30, 60, 90, 120, 180
])

const deviceHistoryCleanupDay = ref(appSettings.configSettings.deviceHistoryCleanupDays)
const activeInfoItem = ref(['0'])
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
function initData() {
  deviceHistoryCleanupDay.value = appSettings.configSettings.deviceHistoryCleanupDays
}

const makeOpenPage = async(url: string) => {
  navigateTo({
    path: $b24?.slider.getUrl(url).toString() || '',
    query: {}
  }, {
    external: true,
    open: {
      target: '_blank'
    }
  })
}

async function makeSave() {
  try {
    page.isLoading = true

    appSettings.configSettings.deviceHistoryCleanupDays = deviceHistoryCleanupDay.value

    await appSettings.saveSettings()

    await makeSendPullCommand('reload.options', { from: 'app.options' })
    await makeClose()
  } catch (error) {
    $logger.error(error)

    let title = t('page.app-options.error.title')
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
    page.isLoading = false
  }
}

async function makeSendPullCommand(command: string, params: Record<string, any> = {}) {
  try {
    $logger.warn('>> pull.send >>>', {
      COMMAND: command,
      PARAMS: params,
      MODULE_ID: moduleId
    })

    await $b24?.callMethod(
      'pull.application.event.add',
      {
        COMMAND: command,
        PARAMS: params,
        MODULE_ID: moduleId
      }
    )
  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: true,
      clearErrorHref: '/slider/app-options.html'
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

    initData()
  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: true,
      clearErrorHref: '/slider/app-options.html'
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
      <template #history>
        <div class="px-4 pb-[12px]">
          <B24Separator class="mb-3" />
          <div class="flex flex-col items-start justify-between gap-[18px]">
            <B24Alert
              color="air-secondary"
              :description="$t('page.app-options.option.history.alert')"
              :b24ui="{ description: 'text-(--ui-color-base-70)' }"
            />

            <B24FormField
              class="w-[190px]"
              :description="$t('page.app-options.option.history.property')"
            >
              <B24Select
                v-model="deviceHistoryCleanupDay"
                :items="deviceHistoryCleanupDays"
                size="lg"
                class="w-full"
              />
            </B24FormField>
          </div>
        </div>
      </template>
      <template #present>
        <div class="px-4 pb-[12px]">
          <B24Separator class="mb-3" />
          <div class="flex flex-col items-start justify-between gap-[18px]">
            <B24Alert
              color="air-secondary"
              :description="$t('page.app-options.option.present.alert')"
              :b24ui="{ description: 'text-(--ui-color-base-70)' }"
            />
            <div class="w-full text-right">
              <B24Link
                is-action
                class="text-(length:--ui-font-size-sm)"
                @click="makeOpenPage('/settings/configs/event_log.php')"
              >
                {{ $t('page.app-options.option.present.action') }}
              </B24Link>
            </div>
          </div>
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
