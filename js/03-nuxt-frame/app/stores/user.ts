/**
 * Some info about User
 * @memo not save to settings
 */
export const useUserStore = defineStore(
  'user',
  () => {
    // region State ////
    const login = ref('')
    const isAdmin = ref(false)
    // endregion ////

    // region Actions ////
    /**
     * Initialize store from batch response data
     * @param data - Raw data from Bitrix24 API
     * @param data.name
     * @param data.lastName
     * @param data.isAdmin
     */
    function initFromBatch(data: {
      name?: string
      lastName?: string
      isAdmin?: boolean
    }) {
      login.value = [data?.name, data?.lastName].filter(Boolean).join(' ') || ' '
      isAdmin.value = data.isAdmin || false
    }
    // endregion ////

    return {
      login,
      isAdmin,
      initFromBatch
    }
  }
)
