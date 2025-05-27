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
    const setting1 = ref<string>('')
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
     * @param data.setting1
     */
    function initFromBatch(data: {
      version?: string
      isTrial?: boolean
      setting1?: string
    }) {
      version.value = data.version || '0.0.1'
      isTrial.value = data.isTrial ?? true
      setting1.value = data.setting1 || ''
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
          setting1: setting1.value
        }
      )
    }
    // endregion ////

    return {
      version,
      isTrial,
      setting1,
      setB24,
      initFromBatch,
      saveSettings
    }
  }
)
