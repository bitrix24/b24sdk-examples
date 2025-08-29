/**
 * Page title && description
 */
export const usePageStore = defineStore(
  'page',
  () => {
    // region State ////
    const title = ref('')
    const description = ref('')
    const isLoading = ref(false)
    // endregion ////

    return {
      title,
      description,
      isLoading
    }
  }
)
