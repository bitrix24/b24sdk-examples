/**
 * Some info about App
 * @memo not save this settings (persist: false)
 * @todo get all from b24
 */
export const useAppSettingsStore = defineStore('appSettings', {
  state: () => ({
    version: '0.0.1',
    isTrial: true
  }),
  actions: {
    setLicenseStatus(isTrial: boolean) {
      this.isTrial = isTrial
    }
  },
  persist: false
})
