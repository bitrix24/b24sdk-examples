<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import Fuse from 'fuse.js'
// import salesMarketingData from '~/data/sales-marketing'
// import communicationData from '~/data/communication'
import { EActivityCategory, EActivityBadge } from '~/types'
import type { IActivity, FilterSetting } from '~/types'
import { ModalLoader, SliderDetail, ActivityListSkeleton } from '#components'
import PreDisplay from '~/components/PreDisplay.vue'
import FileCheckIcon from '@bitrix24/b24icons-vue/main/FileCheckIcon'
import Settings1Icon from '@bitrix24/b24icons-vue/main/SettingsIcon'
import SearchIcon from '@bitrix24/b24icons-vue/button/SearchIcon'

definePageMeta({
  layout: false,
  title: 'Activity list'
})

const isShowDebug = ref(true)
const isLoading = ref(true)
const toast = useToast()
const overlay = useOverlay()
const modalLoader = overlay.create(ModalLoader)
const sliderDetail = overlay.create(SliderDetail)

const activities = ref<IActivity[]>([])

const searchQuery = ref('')
const searchResults = ref<IActivity[]>(activities.value)

const filterSettingsMap = ref<Map<EActivityBadge, boolean>>(new Map(
  Object.values(EActivityBadge).map(key => [key, false])
))

const tabs = ref(
  Object.values(EActivityCategory).map((value) => {
    const labelMap: Record<EActivityCategory, string> = {
      [EActivityCategory.Category1]: 'Sales and Marketing',
      [EActivityCategory.Category2]: 'Communication',
      [EActivityCategory.Category3]: 'Category3'
    }

    return {
      label: labelMap[value],
      value
    }
  })
)
const tabActive = ref(EActivityCategory.Category1)

const fuseOptions = {
  keys: ['title', 'description', 'category', 'badges'],
  includeScore: true,
  threshold: 0.4
}

searchResults.value = activities.value

const performSearch = () => {
  const fuse = new Fuse(activities.value, fuseOptions)

  if (searchQuery.value) {
    searchResults.value = fuse.search(searchQuery.value).map(result => result.item)
  } else {
    searchResults.value = activities.value
  }
}

watch(searchQuery, performSearch)

async function loadData(): Promise<void> {
  isLoading.value = true

  const data = await queryCollection('contentActivities')
    .select(
      'path',
      'title',
      'description',
      'category',
      'badges',
      'avatar'
    )
    .all()

  activities.value = data.map(
    activity => ({
      ...activity,
      isInstall: false
    } as IActivity)
  )

  searchResults.value = activities.value

  /**
   * @memo see beautiful skeleton
   * @todo remove
   */
  await sleepAction(20_000)
  isLoading.value = false
}

async function makeInstall(activity: IActivity): Promise<void> {
  modalLoader.open()
  await sleepAction()

  activity.isInstall = true

  modalLoader.close()

  toast.add({
    title: 'Success!',
    description: `Installed activity ${activity.title}`,
    avatar: activity.avatar
      ? { src: activity.avatar }
      : undefined,
    icon: activity?.avatar ? undefined : FileCheckIcon,
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
  return new Promise<void>(resolve => setTimeout(resolve, timeout))
}

// region Filter ////
const filterSettings = computed<FilterSetting[]>(() => {
  return Object.values(EActivityBadge).map((badge) => {
    const labelMap: Record<EActivityBadge, string> = {
      [EActivityBadge.Install]: 'Installed',
      [EActivityBadge.NotInstall]: 'Not installed',
      [EActivityBadge.Badge1]: 'Badge 1',
      [EActivityBadge.Badge2]: 'Badge 2',
      [EActivityBadge.Badge3]: 'Badge 3'
    }

    return {
      label: labelMap[badge],
      type: 'checkbox' as const,
      checked: filterSettingsMap.value.get(badge),
      onUpdateChecked(checked: boolean) {
        filterSettingsMap.value.set(badge, checked)
      },
      onSelect(e: Event) {
        e.preventDefault()
      }
    }
  })
})

const isSomeFilter = computed<boolean>(() => {
  for (const value of filterSettingsMap.value.values()) {
    if (value) {
      return true
    }
  }

  return false
})
// endregion ////

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
  <NuxtLayout name="header-panel">
    <template #header-middle>
      <div class="relative">
        <B24ButtonGroup no-split class="sm:pl-10">
          <B24Input
            v-model="searchQuery"
            type="search"
            :icon="SearchIcon"
            placeholder="Search..."
            class="min-w-[110px] max-w-[210px]"
            rounded
            @input="performSearch"
          />
          <B24DropdownMenu
            :items="filterSettings"
            :content="{
              align: 'start',
              side: 'left',
              sideOffset: 2
            }"
            :b24ui="{
              content: 'w-[140px]'
            }"
          >
            <B24Button rounded :icon="Settings1Icon" color="link" depth="dark" />
          </B24DropdownMenu>
        </B24ButtonGroup>
        <B24Chip
          v-if="isSomeFilter"
          inset
          standalone
          class="absolute top-0 right-2"
          size="2xs"
          color="primary"
        />
      </div>
    </template>
    <div class="px-4">
      <ActivityListSkeleton v-if="isLoading" />
      <template v-else>
        <B24Tabs
          v-model="tabActive"
          :items="tabs"
          orientation="horizontal"
          class="mb-4"
          :content="false"
        />
        <div
          v-if="searchResults.length"
          class="grid grid-cols-[repeat(auto-fill,minmax(266px,1fr))] gap-y-sm gap-x-sm"
        >
          <template v-for="(activity, activityIndex) in searchResults" :key="activityIndex">
            <div
              class="relative bg-white dark:bg-white/10 p-sm2 cursor-pointer rounded-md flex flex-row gap-sm border-2 transition-shadow shadow hover:shadow-lg hover:border-primary"
              :class="[
                activity?.isInstall ? 'border-green-400 dark:border-green-600' : 'border-base-master/10 dark:border-base-100/20'
              ]"
              @click.stop="async () => { return showSlider(activity) }"
            >
              <B24Avatar
                v-if="activity.avatar"
                :src="activity.avatar"
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
        <div v-else>
          <p>EMPTY</p>
        </div>
        <PreDisplay v-if="isShowDebug">
          {{ activities }}
        </PreDisplay>
      </template>
    </div>
  </NuxtLayout>
</template>
