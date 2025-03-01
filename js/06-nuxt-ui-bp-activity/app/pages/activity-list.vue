<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
// import salesMarketingData from '~/data/sales-marketing'
// import communicationData from '~/data/communication'
import type { IActivity } from '~/types'
import { ModalLoader, SliderDetail } from '#components'
import PreDisplay from '~/components/PreDisplay.vue'
import FileCheckIcon from '@bitrix24/b24icons-vue/main/FileCheckIcon'

definePageMeta({
  layout: 'header-panel',
  title: 'Activity list'
})

const isShowDebug = ref(true)
const toast = useToast()
const overlay = useOverlay()
const modalLoader = overlay.create(ModalLoader)
const sliderDetail = overlay.create(SliderDetail)

const tabs = ref([
  {
    label: 'Sales and Marketing',
    content: 'Content for Sales and Marketing.',
    value: 'tab-sale'
  },
  {
    label: 'Communication',
    content: 'Content for Communication.',
    value: 'tab-communication'
  }
])
const active = ref('tab-sale')

const activities = ref<IActivity[]>([])

async function loadData(): Promise<void> {
  modalLoader.open()

  const { data } = await useAsyncData(() => {
    return queryCollection('contentActivities')
      // .path('/activities')
      .select('path', 'title', 'description')
      .all()
  })

  for (const row of (data?.value || {})) {
    activities.value.push({
      ...row,
      icon: undefined,
      isInstall: false
    })
  }

  modalLoader.close()
}

async function makeInstall(activity: IActivity): Promise<void> {
  await sleepAction()

  activity.isInstall = true

  toast.add({
    title: 'Success!',
    description: `Installed activity ${activity.title}`,
    icon: activity?.icon || FileCheckIcon,
    color: 'success'
  })
}

async function showSlider(activity: IActivity): Promise<void> {
  return sliderDetail.open({
    activity
  })
}

/**
 * Pause on xxx milliseconds
 *
 * @return {Promise<void>}
 * @constructor
 */
async function sleepAction(timeout: number = 1000): Promise<void> {
  modalLoader.open()
  return new Promise<void>(resolve => setTimeout(() => {
    modalLoader.close()
    resolve()
  }, timeout))
}

onMounted(async () => {
  try {
    loadData()
  } catch (error: any) {
    /**
     * @todo fix error
     */
    // $logger.error(error)
    showError({
      statusCode: 404,
      statusMessage: error?.message || error,
      data: {
        description: 'Problem in app',
        homePageIsHide: true,
        isShowClearError: true,
        clearErrorHref: '/'
      },
      cause: error,
      fatal: true
    })
  }
})

onUnmounted(() => {
  // $b24?.destroy()
})
</script>

<template>
  <div class="pb-28 px-4">
    <B24Tabs
      v-model="active"
      :items="tabs"
      orientation="horizontal"
      class="mb-4"
      :content="false"
    />
    <div class="grid grid-cols-[repeat(auto-fill,minmax(266px,1fr))] gap-y-sm gap-x-sm">
      <template v-for="(activity, activityIndex) in activities" :key="activityIndex">
        <div
          class="relative bg-white dark:bg-white/10 p-sm2 cursor-pointer rounded-md flex flex-row gap-sm border-2 transition-shadow shadow hover:shadow-lg hover:border-primary"
          :class="[
            activity?.isInstall ? 'border-green-400 dark:border-green-600' : 'border-base-master/10 dark:border-base-100/20'
          ]"
          @click.stop="async () => { return showSlider(activity) }"
        >
          <B24Avatar
            v-if="activity.icon"
            :icon="activity.icon"
            size="xl"
            class="border-2"
            :class="[
              activity?.isInstall ? 'border-green-400' : 'border-blue-400'
              // item.value === 'tab-sale' ? 'pb-1' : '',
              // item.value === 'tab-communication' ? 'pr-1' : ''
            ]"
            :b24ui="{
              root: activity?.isInstall ? 'bg-green-150 dark:bg-green-400' : 'bg-blue-150 dark:bg-blue-400',
              icon: activity?.isInstall ? 'text-green-400 dark:text-green-900' : 'text-blue-400 dark:text-blue-900'
            }"
          />
          <div class="flex flex-col items-start justify-between gap-2">
            <div>
              <div class="font-b24-secondary text-black dark:text-base-150 text-h6 leading-4 mb-xs font-semibold line-clamp-2">
                {{ activity.title }}
              </div>
              <div class="font-b24-primary text-sm text-base-500 line-clamp-2">
                <div>{{ activity.description }}</div>
              </div>
            </div>
            <div class="w-full flex flex-row gap-1 items-center justify-end">
              <B24Button
                v-if="!activity.isInstall"
                size="xs"
                rounded
                label="Install"
                color="primary"
                loading-auto
                @click.stop="async () => { return makeInstall(activity) }"
              />
              <B24Badge
                v-else
                size="lg"
                color="collab"
                use-fill
                label="Installed"
              />
            </div>
          </div>
        </div>
      </template>
    </div>

    <PreDisplay v-if="isShowDebug">
      {{ activities }}
    </PreDisplay>
  </div>
</template>
