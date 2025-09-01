<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { usePageStore } from '~/stores/page'
import { useAppSettingsStore } from '~/stores/appSettings'
import { VisXYContainer, VisArea } from '@unovis/vue'
import type { B24Frame, TypePullMessage } from '@bitrix24/b24jssdk'
import TrendUpIcon from '@bitrix24/b24icons-vue/outline/TrendUpIcon'

type DataRecord = { x: number, y: number }

definePageMeta({
  layout: 'placement'
})

const { t, locales: localesI18n, setLocale } = useI18n()
const page = usePageStore()

// region Init ////
const { $logger, moduleId, initApp, reloadData, b24Helper, destroyB24Helper, usePullClient, useSubscribePullClient, startPullClient, processErrorGlobal } = useAppInit('crm_deal_detail_tab')
const appSettings = useAppSettingsStore()
const { $initializeB24Frame } = useNuxtApp()
let $b24: null | B24Frame = null
// endregion ////

// region Init ////
const data = ref<DataRecord[]>([])

const x = (d: DataRecord) => d.x
const y = (d: DataRecord) => d.y
const dropdownItems = ref(['CRM settings', 'My company details', 'Access permissions', 'CRM Payment', 'CRM.Delivery', 'Scripts', 'Create script', 'Install from Bitrix24.Market'])
const dropdownValue = ref('CRM Payment')
// endregion ////

// region Actions ////
async function refreshData() {
  let i = 0
  data.value = [
    { x: ++i, y: getRandomInt() },
    { x: ++i, y: getRandomInt() },
    { x: ++i, y: getRandomInt() },
    { x: ++i, y: getRandomInt() },
    { x: ++i, y: getRandomInt() },
    { x: ++i, y: getRandomInt() },
    { x: ++i, y: getRandomInt() }
  ]
}

function openSliderDemo() {
  $b24?.slider.openSliderAppPage({
    place: 'app-options',
    bx24_width: 650,
    bx24_title: t('page.app-options.seo.title'),
  })
}

const makeSendPullCommandHandler = async (message: TypePullMessage) => {
  $logger.warn('<< pull.get <<<', message)

  if (message.command === 'reload.options') {
    $logger.info("Get pull command for update. Reinit the application")
    page.isLoading = true
    await reloadData()
    page.isLoading = false
  }
}
// endregion ////

// region Tools ////
function getRandomInt(max: number = 5) {
  return Math.floor(Math.random() * max)
}

async function resizeWindow() {
  await $b24?.parent.fitWindow()
}
// endregion ////

// region Lifecycle Hooks ////
onMounted(async () => {
  try {
    page.isLoading = true

    $b24 = await $initializeB24Frame()
    await initApp($b24, localesI18n, setLocale)

    page.title = t('placement.crm_deal_detail_tab.seo.title')
    page.description = t('placement.crm_deal_detail_tab.seo.description')

    usePullClient()
    useSubscribePullClient(
      makeSendPullCommandHandler.bind( this ),
      moduleId
    )
    startPullClient()
    await refreshData()
    await resizeWindow()

    $logger.info('Hi from crm-deal-detail-tab')

  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: true,
      clearErrorHref: '/handler/uf.demo.html'
    })
  } finally {
    page.isLoading = false
  }
})

onUnmounted(() => {
  if (b24Helper.value) {
    destroyB24Helper()
  }
})
// endregion ////
</script>

<template>
  <div>
    <div class="bg-white p-[8px] rounded flex flex-row items-center justify-star gap-[12px]">
      <B24Badge
        rounded
        size="xl"
        color="air-primary-copilot"
        inverted
        :label="appSettings.configSettings.deviceHistoryCleanupDays"
        :icon="TrendUpIcon"
      />
      <div>
        <B24Select
          id="select"
          v-model="dropdownValue"
          color="air-primary"
          class="w-[200px]"
          value-key="value"
          :items="dropdownItems"
          :content="{
            sideOffset: 4,
            collisionPadding: 1
          }"
          :b24ui="{
            // base: 'hover:ring-1 hover:ring-inset hover:ring-ai-500 dark:hover:ring-ai-600 data-[state=open]:ring-1 data-[state=open]:ring-inset data-[state=open]:ring-ai-500',
          }"
          @change="refreshData"
        />
      </div>
    </div>
    <div class="mt-[12px] w-full relative rounded overflow-hidden bg-white">
      <div
        style="background-position: 10px 10px"
        class="absolute inset-0 bg-grid-example dark:bg-grid-example"
        :class="[
          [
            '[mask-image:linear-gradient(0deg,rgba(255,255,255,0.1),rgba(255,255,255,0.5))]'
          ].join(' ')
        ]"
      />
      <div class="isolate relative p-t-2 min-h-40 w-full h-full flex flex-col flex-nowrap justify-end items-center gap-4">
        <VisXYContainer
          v-if="!page.isLoading"
          :data="data"
          height="440"
          @click.stop="openSliderDemo"
        >
          <VisArea curve-type="basis" :x="x" :y="y" color="#7437d3" />
        </VisXYContainer>
      </div>
      <div class="pointer-events-none absolute rounded inset-0 border border-black/5" />
    </div>
  </div>
</template>
