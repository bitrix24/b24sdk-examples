/**
 * Some info about App
 * @todo not save this settings (persist: false)
 * @todo get all from b24
 */
export const useAppSettingsStore = defineStore('appSettings', {
  state: () => ({
    version: '0.0.1',
    isTrial: true,
    integrator: {
      logo: '',
      company: '',
      phone: '',
      email: '',
      whatsapp: '',
      telegram: '',
      site: '',
      comments: ''
    }
  }),
  actions: {
    setLicenseStatus(isTrial: boolean) {
      this.isTrial = isTrial
    },
    updateIntegrator(integrator: Partial<typeof this.integrator>) {
      this.integrator = { ...this.integrator, ...integrator }
    }
  },
  persist: true
})
