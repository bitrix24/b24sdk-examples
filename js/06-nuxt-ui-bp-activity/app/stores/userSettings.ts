/**
 * Some data for user
 * @memo save all at localStorage ( persist: true )
 * @todo save all to b24
 */
export const useUserSettingsStore = defineStore(
  'userSettings',
  () => {
    // region State ////
    const searchQuery = ref('')
    const filterParams = reactive({
      category: 'all',
      sortBy: 'date'
    })
    // endregion ////

    // region Actions ////
    /**
     * Initialize store from batch response data
     * @param data - Raw data from Bitrix24 API
     * @param data.searchQuery
     * @param data.filterParams
     */
    const initFromBatch = (data: {
      searchQuery?: string
      filterParams?: typeof filterParams
    }) => {
      searchQuery.value = data.searchQuery || ''
      Object.assign(filterParams, data.filterParams || {})
    }

    /**
     * Update filter parameters
     * @param params - Partial filter parameters
     */
    const updateFilterParams = (params: Partial<typeof filterParams>) => {
      Object.assign(filterParams, params)
    }

    /**
     * Save settings to Bitrix24
     */
    const saveSettings = async () => {
      // Implementation for direct update
    }
    // endregion ////

    return {
      searchQuery,
      filterParams,
      initFromBatch,
      updateFilterParams,
      saveSettings
    }
  }
)
