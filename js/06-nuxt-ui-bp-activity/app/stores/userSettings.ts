/**
 * Some data for user
 * @memo save all at localStorage ( persist: true )
 * @todo save all to b24
 */
export const useUserSettingsStore = defineStore('userSettings', {
  state: () => ({
    searchQuery: '',
    filterParams: {
      category: 'all',
      sortBy: 'date'
    }
  }),
  actions: {
    updateFilterParams(params: Partial<typeof this.filterParams>) {
      this.filterParams = { ...this.filterParams, ...params }
    }
  },
  persist: true
})
