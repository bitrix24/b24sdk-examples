<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import type { IActivity } from '~/types'
/**
 * @todo remove ActivityItemSkeleton
 */
import { ModalLoader, ModalConfirm, ActivityItemSliderDetail, ActivityListSkeleton, ActivityListEmpty } from '#components'
import useSearchInput from '~/composables/useSearchInput'
import useDynamicFilter from '~/composables/useDynamicFilter'
import { getBadgeProps } from '~/composables/useLabelMapBadge'
import { sleepAction } from '~/utils/sleep'
import FileCheckIcon from '@bitrix24/b24icons-vue/main/FileCheckIcon'
import Settings1Icon from '@bitrix24/b24icons-vue/main/SettingsIcon'
import SearchIcon from '@bitrix24/b24icons-vue/button/SearchIcon'

definePageMeta({
  layout: false,
  title: 'Activity list'
})

// region Init ////
const isShowDebug = ref(true)
const isLoading = ref(true)
const toast = useToast()
const overlay = useOverlay()
const modalLoader = overlay.create(ModalLoader)
const modalConfirm = overlay.create(ModalConfirm)
const activitySliderDetail = overlay.create(ActivityItemSliderDetail)

const { locale } = useI18n()
// endregion ////

// region Search ////
const {
  activities,
  searchInput,
  searchQuery,
  filterBadges,
  isSomeBadgeFilter,
  makeClearFilter,
  tabs,
  tabActive,
  activitiesList
} = useSearchInput()
// endregion ////

// region Data ////
async function loadData(): Promise<void> {
  isLoading.value = true

  const data = await queryCollection(`contentActivities_${locale.value}`)
    .select(
      'path',
      'title',
      'description',
      'categories',
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

  /**
   * @todo get from b24 info about install
   */
  isLoading.value = false
}

watch(locale, () => {
  loadData()
})
// endregion ////

// region Actions ////
async function showSlider(activity: IActivity): Promise<void> {
  return activitySliderDetail.open({
    activity
  })
}

async function makeInstall(activity: IActivity): Promise<void> {
  modalLoader.open()
  await sleepAction(1000)

  activity.isInstall = true

  modalLoader.close()

  toast.add({
    title: 'Success!',
    description: `Installed activity ${activity.title}. Now you can use it in your business processes.`,
    avatar: activity.avatar
      ? { src: activity.avatar }
      : undefined,
    icon: activity?.avatar ? undefined : FileCheckIcon,
    color: 'success'
  })
}

async function makeUnInstall(activity: IActivity): Promise<void> {
  const isConfirm = await modalConfirm.open({
    activity
  })

  if (!isConfirm) {
    return
  }

  modalLoader.open()

  await sleepAction()

  activity.isInstall = false

  toast.add({
    title: 'Success!',
    description: `Uninstalled activity ${activity.title}. You must ensure that business processes are working properly.`,
    avatar: activity.avatar
      ? { src: activity.avatar }
      : undefined,
    icon: activity?.avatar ? undefined : FileCheckIcon,
    color: 'success'
  })

  modalLoader.close()
}
// endregion ////

// region Filter ////
const searchResults = useDynamicFilter<IActivity>(
  searchQuery,
  activitiesList,
  ['title', 'description', 'categories', 'badges'],
  {
    includeScore: true
  }
)
// endregion ////

// region Mounted / Unmounted ////
onMounted(async () => {
  try {
    await loadData()
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
// endregion ////
</script>

<template>
  <NuxtLayout name="header-panel">
    <template #header-middle>
      <div
        v-show="!isLoading"
        class="relative"
      >
        <B24ButtonGroup no-split class="sm:pl-10">
          <B24Input
            ref="searchInput"
            v-model="searchQuery"
            type="search"
            :icon="SearchIcon"
            placeholder="Search..."
            class="min-w-[110px] max-w-[210px]"
            rounded
          />
          <B24DropdownMenu
            :items="filterBadges"
            :content="{
              align: 'start',
              side: 'left',
              sideOffset: 2
            }"
            :b24ui="{
              content: 'w-[240px]'
            }"
          >
            <B24Button rounded :icon="Settings1Icon" color="link" depth="dark" />
          </B24DropdownMenu>
        </B24ButtonGroup>
        <B24Chip
          v-if="isSomeBadgeFilter"
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
          class="hidden lg:block mb-4 "
          :content="false"
        />
        <ProseH1>H1</ProseH1>
        <div
          v-if="searchResults.length"
          class="grid grid-cols-[repeat(auto-fill,minmax(266px,1fr))] gap-y-sm gap-x-sm"
        >
          <template v-for="(activity, activityIndex) in searchResults" :key="activityIndex">
            <div
              class="relative bg-white dark:bg-white/10 p-sm2 cursor-pointer rounded-md flex flex-row gap-sm border-2 transition-shadow shadow hover:shadow-lg hover:border-primary"
              :class="[
                activity?.isInstall ? 'border-green-300 dark:border-green-800' : 'border-base-master/10 dark:border-base-100/20'
              ]"
              @click.stop="async () => { return showSlider(activity) }"
            >
              <B24Avatar
                v-if="activity.avatar"
                :src="activity.avatar"
                size="xl"
                class="border-2"
                :class="[
                  activity?.isInstall ? 'border-green-300 dark:border-green-800' : 'border-blue-400'
                ]"
                :b24ui="{
                  root: activity?.isInstall ? 'bg-green-300 dark:bg-green-800' : 'bg-blue-150 dark:bg-blue-400',
                  icon: activity?.isInstall ? 'text-green-300 dark:text-green-800' : 'text-blue-400 dark:text-blue-900'
                }"
              />
              <div class="w-full flex flex-col items-start justify-between gap-2">
                <div>
                  <div v-if="activity.title" class="font-b24-secondary text-black dark:text-base-150 text-h6 leading-4 mb-xs font-semibold line-clamp-1">
                    <div>{{ activity.title }}</div>
                  </div>
                  <div v-if="activity.badges" class="mb-2 w-full flex flex-row flex-wrap items-start justify-start gap-2">
                    <B24Badge
                      v-for="(badge, badgeIndex) in activity.badges"
                      :key="badgeIndex"
                      size="xs"
                      color="collab"
                      :label="badge"
                      v-bind="getBadgeProps(badge)"
                    />
                  </div>
                  <div v-if="activity.description" class="font-b24-primary text-sm text-base-500 line-clamp-2">
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
                  <B24Button
                    v-else
                    size="xs"
                    rounded
                    label="Uninstall"
                    color="default"
                    depth="light"
                    loading-auto
                    @click.stop="async () => { return makeUnInstall(activity) }"
                  />
                </div>
              </div>
            </div>
          </template>
        </div>
        <ActivityListEmpty
          v-else
          :search-query="searchQuery"
          @clear="makeClearFilter"
        />
        <ProsePre v-if="isShowDebug">
          {{ activities }}
        </ProsePre>
      </template>
    </div>
  </NuxtLayout>
</template>
