import type { B24Frame } from '@bitrix24/b24jssdk'

/**
 * Some info about App
 */
export const useAppSettingsStore = defineStore(
  'appSettings',
  () => {
    let $b24: null | B24Frame = null

    // region State ////
    const version = ref('0.0.0')
    const isTrial = ref(true)
    const integrator = reactive({
      logo: '',
      company: '',
      phone: '',
      email: '',
      whatsapp: '',
      telegram: '',
      site: '',
      comments: ''
    })
    const configSettings = reactive({
      deviceHistoryCleanupDays: 30
    })
    // endregion ////

    // region Actions ////
    function setB24(b24: B24Frame) {
      $b24 = b24
    }

    /**
     * Initialize store from batch response data
     * @param data - Raw data from Bitrix24 API
     * @param data.version
     * @param data.isTrial
     * @param data.integrator
     * @param data.configSettings
     */
    function initFromBatch(data: {
      version?: string
      isTrial?: boolean
      integrator?: typeof integrator
      configSettings?: typeof configSettings
    }) {
      isTrial.value = data.isTrial ?? true
      if (data.version) {
        version.value = data.version
      }
      if (data.integrator) {
        Object.assign(integrator, data.integrator)
      }
      if (data.configSettings) {
        Object.assign(configSettings, data.configSettings)
      }
    }

    /**
     * Save settings to Bitrix24
     */
    const saveSettings = async () => {
      if ($b24 === null) {
        console.error('B24 non init. Use appSettings.setB24()')
        return
      }

      return $b24.callMethod(
        'app.option.set',
        {
          integrator: { ...integrator },
          configSettings: { ...configSettings }
        }
      )
    }

    const updateIntegrator = (params: Partial<typeof integrator>) => {
      Object.assign(integrator, params)
      saveSettings()
    }

    /**
     * @need fix lang
     */
    const integratorPreview = computed(() => {
      const result = []
      if (integrator.phone.length) {
        result.push({
          label: 'Phone',
          code: 'phone',
          description: integrator.phone
        })
      }
      if (integrator.email.length) {
        result.push({
          label: 'E-mail',
          code: 'email',
          description: integrator.email
        })
      }
      if (integrator.telegram.length) {
        result.push({
          label: 'Telegram',
          code: 'telegram',
          description: integrator.telegram
        })
      }
      if (integrator.whatsapp.length) {
        result.push({
          label: 'WhatsApp',
          code: 'whatsapp',
          description: integrator.whatsapp
        })
      }
      if (integrator.site.length) {
        result.push({
          label: 'Website',
          code: 'site',
          description: integrator.site
        })
      }
      if (integrator.comments.length) {
        result.push({
          label: 'Comments',
          code: 'comments',
          description: integrator.comments
        })
      }

      return result
    })
    // endregion ////

    return {
      version,
      isTrial,
      setB24,
      initFromBatch,
      saveSettings,
      configSettings,
      integrator,
      updateIntegrator,
      integratorPreview
    }
  }
)
