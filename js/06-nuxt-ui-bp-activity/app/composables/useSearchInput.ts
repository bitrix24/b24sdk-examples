import { useDebounce } from '@vueuse/core'
import { nextTick, onMounted, ref, watch } from 'vue'
import { labelMapBadge } from '~/composables/useLabelMapBadge'
import { labelMapTab } from '~/composables/useLabelMapTab'
import { scrollToTop } from '~/utils/scrollToTop'
import type { FilterSetting, IActivity } from '~/types'
import { EActivityBadge, EActivityCategory } from '~/types'

const useSearchInput = () => {
  // region Init ////
  const activities = ref<IActivity[]>([])

  const searchInput = ref<HTMLElement | null>(null)
  const searchQuery = ref<string>('')
  const searchQueryDebounced = useDebounce<string>(searchQuery, 200)
  // endregion ////

  // region Tabs ////
  const tabs = ref([
    {
      label: 'All',
      value: 'all'
    },
    ...Object.values(EActivityCategory).map((value) => {
      return {
        label: labelMapTab[value],
        value
      }
    })
  ])
  const tabActive = ref<'all' | EActivityCategory>('all')
  // endregion ////

  // region Badge ////
  const filterBadgeMap = ref<Map<EActivityBadge, boolean>>(new Map(
    Object.values(EActivityBadge).map(key => [key, false])
  ))

  const filterBadges = computed<FilterSetting[]>(() => {
    return Object.values(EActivityBadge).map((badge) => {
      return {
        label: labelMapBadge[badge],
        type: 'checkbox' as const,
        checked: filterBadgeMap.value.get(badge),
        onUpdateChecked(checked: boolean) {
          filterBadgeMap.value.set(badge, checked)
          if (
            badge === EActivityBadge.Install
            && checked
            && filterBadgeMap.value.get(EActivityBadge.NotInstall)
          ) {
            filterBadgeMap.value.set(EActivityBadge.NotInstall, false)
          } else if (
            badge === EActivityBadge.NotInstall
            && checked
            && filterBadgeMap.value.get(EActivityBadge.Install)
          ) {
            filterBadgeMap.value.set(EActivityBadge.Install, false)
          }
        },
        onSelect(e: Event) {
          e.preventDefault()

          nextTick(() => {
            scrollToTop()
          })
        }
      }
    })
  })

  const isSomeBadgeFilter = computed<boolean>(() => {
    for (const value of filterBadgeMap.value.values()) {
      if (value) {
        return true
      }
    }

    return false
  })
  // endregion ////

  // region Actions ////
  const makeClearFilter = () => {
    searchQuery.value = ''

    Object.values(EActivityBadge).forEach((badge) => {
      filterBadgeMap.value.set(badge, false)
    })

    nextTick(() => {
      scrollToTop()
    })
  }
  // endregion ////

  // region Data ////
  const activitiesList = computed(() => {
    let list = activities.value.filter((activity) => {
      if (tabActive.value === 'all') {
        return true
      }

      return activity.categories?.includes(tabActive.value)
    })

    const activeFilters = Array.from(filterBadgeMap.value.entries())
      .filter(([_, isActive]) => isActive)
      .map(([badge]) => badge)

    if (activeFilters.length > 0) {
      list = list.filter((activity) => {
        return activeFilters.every((badge) => {
          if (badge === EActivityBadge.Install) {
            return activity.isInstall === true
          } else if (badge === EActivityBadge.NotInstall) {
            return activity.isInstall !== true
          }

          return activity.badges?.includes(badge)
        })
      })
    }

    return list
  })
  // endregion ////

  // region Watch ////
  watch(searchQueryDebounced, (searchString) => {
    /**
     * @todo save app.props
     */
    const newUrl = new URL(window.location.href)

    if (searchString === '') {
      newUrl.searchParams.delete('search')
    } else {
      newUrl.searchParams.set('search', searchString)
    }

    nextTick(() => {
      window.history.replaceState({}, '', newUrl)

      scrollToTop()
    })
  })
  // endregion ////

  // region Mounted / Unmounted ////
  onMounted(() => {
    const searchParams = new URLSearchParams(window.location.search)

    if (searchParams.has('search')) {
      searchQuery.value = searchParams.get('search') || ''
    }
  })
  // endregion ////

  return {
    activities,
    searchInput,
    searchQuery,
    searchQueryDebounced,
    filterBadgeMap,
    filterBadges,
    isSomeBadgeFilter,
    makeClearFilter,
    tabs,
    tabActive,
    activitiesList
  }
}

export default useSearchInput
