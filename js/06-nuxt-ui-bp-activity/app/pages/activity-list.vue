<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import type { IActivity } from '~/types'
import { ModalLoader, ModalConfirm, ActivityItemSliderDetail, ActivityListSkeleton, ActivityListEmpty } from '#components'
import type { Collections } from '@nuxt/content'
import useSearchInput from '~/composables/useSearchInput'
import useDynamicFilter from '~/composables/useDynamicFilter'
import { getBadgeProps } from '~/composables/useLabelMapBadge'
import { sleepAction } from '~/utils/sleep'
import * as locales from '@bitrix24/b24ui-nuxt/locale'
import FileCheckIcon from '@bitrix24/b24icons-vue/main/FileCheckIcon'
import Settings1Icon from '@bitrix24/b24icons-vue/main/SettingsIcon'
import SearchIcon from '@bitrix24/b24icons-vue/button/SearchIcon'
import CheckIcon from '@bitrix24/b24icons-vue/main/CheckIcon'

const { locale, t, defaultLocale } = useI18n()

definePageMeta({
  layout: false
})

useHead({
  title: t('page.list.seo.title')
})

// region Init ////
const isShowDebug = ref(false)
const isLoading = ref(true)
const toast = useToast()
const overlay = useOverlay()
const modalLoader = overlay.create(ModalLoader)
const modalConfirm = overlay.create(ModalConfirm)
const activitySliderDetail = overlay.create(ActivityItemSliderDetail)
// endregion ////

// region Locale ////
const dir = computed(() => locales[locale.value]?.dir || 'ltr')
const contentCollection = computed<keyof Collections>(() => `contentActivities_${locale.value.length > 0 ? locale.value : defaultLocale}`)
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

  const data = await queryCollection(contentCollection.value)
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
    title: t('page.list.make.install.success.title'),
    description: t(
      'page.list.make.install.success.description', {
        title: activity.title
      }
    ),
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
    title: t('page.list.make.uninstall.success.title'),
    description: t(
      'page.list.make.uninstall.success.description', {
        title: activity.title
      }
    ),
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
        description: t('error.onMounted'),
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
    <template #header-title>
      {{ $t('page.list.seo.title') }}
    </template>
    <template #header-middle>
      <div
        v-show="!isLoading"
        class="relative"
      >
        <B24ButtonGroup no-split class="sm:ps-10">
          <B24Input
            ref="searchInput"
            v-model="searchQuery"
            type="search"
            :icon="SearchIcon"
            :placeholder="$t('page.list.ui.searchInput.placeholder')"
            class="min-w-[110px] max-w-[210px]"
            rounded
          />
          <B24DropdownMenu
            :items="filterBadges"
            :content="{
              align: 'start',
              side: dir === 'ltr' ? 'left' : 'right',
              sideOffset: 2
            }"
            :b24ui="{
              content: 'w-[240px] max-h-[245px]'
            }"
          >
            <B24Button rounded :icon="Settings1Icon" color="link" depth="dark" />
          </B24DropdownMenu>
        </B24ButtonGroup>
        <B24Chip
          v-if="isSomeBadgeFilter"
          inset
          standalone
          class="absolute top-0 ltr:right-2 rtl:left-2"
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

        <div
          v-if="searchResults.length"
          class="mb-sm grid grid-cols-[repeat(auto-fill,minmax(266px,1fr))] gap-y-sm gap-x-sm"
        >
          <template v-for="(activity, activityIndex) in searchResults" :key="activityIndex">
            <div
              class="relative bg-white dark:bg-white/10 p-sm2 cursor-pointer rounded-md flex flex-row gap-sm border-2 transition-shadow shadow hover:shadow-lg hover:border-primary"
              :class="[
                activity?.isInstall ? 'border-green-300 dark:border-green-800' : 'border-base-master/10 dark:border-base-100/20'
              ]"
              @click.stop="async () => { return showSlider(activity) }"
            >
              <div
                v-if="activity?.isInstall"
                class="absolute -top-2 ltr:right-3 rtl:left-3 rounded-full bg-green-400 dark:bg-green-800 size-5 text-white flex items-center justify-center"
              >
                <CheckIcon class="size-md" />
              </div>
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
                      :label="$t(`composables.useSearchInput.badge.${badge}`)"
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
                    :label="$t('page.list.ui.make.install')"
                    color="primary"
                    loading-auto
                    @click.stop="async () => { return makeInstall(activity) }"
                  />
                  <B24Button
                    v-else
                    size="xs"
                    rounded
                    :label="$t('page.list.ui.make.uninstall')"
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
          @clear="makeClearFilter"
        />

        <ProsePre v-if="isShowDebug">
          {{ activities }}
        </ProsePre>
      </template>
    </div>
  </NuxtLayout>
</template>
