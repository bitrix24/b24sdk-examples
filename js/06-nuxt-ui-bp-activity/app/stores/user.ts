/**
 * Some info about User
 * @memo not save this settings (persist: false)
 * @todo get all from b24
 */
export const useUserStore = defineStore('user', {
  state: () => ({
    login: 'SomeUserLogin',
    isAdmin: true
  }),
  actions: {
  },
  persist: false
})
