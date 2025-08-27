import type { B24Frame } from '@bitrix24/b24jssdk'

/**
 * Some data for user
 */
export const useUserSettingsStore = defineStore(
  'userSettings',
  () => {
    let $b24: null | B24Frame = null

    // region State ////
    const configSettings = reactive({
      someValue_1: 30,
      someValue_2: 'some text'
    })
    // endregion ////

    // region Actions ////
    function setB24(b24: B24Frame) {
      $b24 = b24
    }

    /**
     * Initialize store from batch response data
     * @param data - Raw data from Bitrix24 API
     * @param data.configSettings
     */
    const initFromBatch = (data: {
      configSettings?: typeof configSettings
    }) => {
      if (data.configSettings) {
        Object.assign(configSettings, data.configSettings)
      }
    }

    /**
     * Save settings to Bitrix24
     */
    const saveSettings = async () => {
      if ($b24 === null) {
        console.error('B24 non init. Use userSettings.setB24()')
        return
      }

      return $b24.callMethod(
        'user.option.set',
        {
          configSettings: { ...configSettings }
        }
      )
    }

    // endregion ////

    return {
      configSettings,
      setB24,
      initFromBatch,
      saveSettings
    }
  }
)
