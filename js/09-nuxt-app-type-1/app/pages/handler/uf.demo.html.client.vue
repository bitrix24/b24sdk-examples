<script setup lang="ts">
import { ref } from 'vue'
import { VisXYContainer, VisArea } from '@unovis/vue'
import TrendUpIcon from '@bitrix24/b24icons-vue/outline/TrendUpIcon'

type DataRecord = { x: number, y: number }

definePageMeta({
  layout: 'uf-placement'
})

// region Init ////
const { $logger } = useAppInit('Index')
const { $initializeB24Frame } = useNuxtApp()
const $b24 = await $initializeB24Frame()
$logger.info('Hi from index')
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
  $b24.slider.openSliderAppPage({
    place: 'slider-demo',
    bx24_width: 320,
    bx24_label: {
      bgColor: 'violet',
      text: '🍅 Pomodoro',
      color: '#ffffff'
    },
    bx24_title: 'Pomodoro'
  })
}
// endregion ////

// region Lifecycle Hooks ////
onMounted(async () => {
  await refreshData()
  await resizeWindow()
})
// endregion ////

// region Tools ////
function getRandomInt(max: number = 5) {
  return Math.floor(Math.random() * max)
}

async function resizeWindow() {
  await $b24.parent.fitWindow()
}
// endregion ////
</script>

<template>
  <div class="h-[245px] overflow-hidden">
    <B24FormField
      label="Some type of data"
      description="Change the data type for plotting"
      hint="Trend"
      required
    >
      <template #hint>
        <B24Badge
          rounded
          size="xs"
          color="ai"
          use-fill
          label="356"
          :icon="TrendUpIcon"
        />
      </template>
      <B24Select
        id="select"
        v-model="dropdownValue"
        color="ai"
        class="w-full"
        value-key="value"
        :items="dropdownItems"
        :content="{
          sideOffset: 4,
          collisionPadding: 1
        }"
        :b24ui="{
          base: 'text-base-760 hover:ring-1 hover:ring-inset hover:ring-ai-500 dark:hover:ring-ai-600 data-[state=open]:ring-1 data-[state=open]:ring-inset data-[state=open]:ring-ai-500 dark:data-[state=open]:ring-ai-600',
          trailingIcon: 'text-base-760 size-lg',
          content: 'max-h-[150px] rounded-[18px] w-[calc(var(--reka-select-trigger-width)-2px)] shadow-[0_6px_21px_rgba(0,0,0,0.15)] ring-0 border-0',
          viewport: 'ring-0 border-0',
          group: 'p-0 my-[8px] -mx-1',
          item: 'ps-[16px] pe-[16px] rounded-none before:rounded-none before:h-9',
          itemTrailingIcon: 'hidden'
        }"
        @change="refreshData"
      />
    </B24FormField>
    <div class="mt-1 w-full relative rounded overflow-hidden bg-tertiary/10 dark:bg-tertiary/5">
      <div
        style="background-position: 10px 10px"
        class=" absolute inset-0 bg-grid-example dark:bg-grid-example"
        :class="[
          [
            '[mask-image:linear-gradient(0deg,rgba(255,255,255,0.1),rgba(255,255,255,0.5))]',
            'dark:[mask-image:linear-gradient(0deg,rgba(255,255,255,0.1),rgba(255,255,255,0.5))]'
          ].join(' ')
        ]"
      />
      <div class="isolate relative p-t-2 min-h-40 w-full h-full flex flex-col flex-nowrap justify-end items-center gap-4">
        <VisXYContainer :data="data" height="120" @click.stop="openSliderDemo">
          <VisArea curve-type="basis" :x="x" :y="y" color="#7437d3" />
        </VisXYContainer>
      </div>
      <div class="pointer-events-none absolute rounded inset-0 border border-black/5 dark:border-white/5" />
    </div>
  </div>
</template>
