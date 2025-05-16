import type { B24Frame } from '@bitrix24/b24jssdk'

/**
 * Some data for user
 */
export const useUserSettingsStore = defineStore(
  'userSettings',
  () => {
    let $b24: null | B24Frame = null

    // region State ////
    const setting1 = ref<string>('')
    const setting2 = ref<string>('')
    const isFocusSessionStarted = ref<boolean>(false)
    const shortBreak = ref<number>(5)
    const longBreak = ref<number>(15)
    const workDuration = ref<number>(25)
    const repeats = ref<number>(4)
    // endregion ////

    // region Actions ////
    function setB24(b24: B24Frame) {
      $b24 = b24
    }

    /**
     * Initialize store from batch response data
     * @param data - Raw data from Bitrix24 API
     * @param data.setting1
     * @param data.setting2
     * @param data.isFocusSessionStarted
     * @param data.shortBreak
     * @param data.longBreak
     * @param data.workDuration
     * @param data.repeats
     */
    const initFromBatch = (data: {
      setting1?: string
      setting2?: string
      isFocusSessionStarted?: boolean
      shortBreak?: number
      longBreak?: number
      workDuration?: number
      repeats?: number
    }) => {
      setting1.value = data.setting1 || ''
      setting2.value = data.setting2 || ''
      isFocusSessionStarted.value = data?.isFocusSessionStarted ? data?.isFocusSessionStarted : false
      shortBreak.value = data.shortBreak || 5
      longBreak.value = data.longBreak || 15
      workDuration.value = data.workDuration || 25
      repeats.value = data.repeats || 4
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
          setting1: setting1.value,
          setting2: setting2.value,
          isFocusSessionStarted: isFocusSessionStarted.value ? 'Y' : 'N',
          shortBreak: shortBreak.value,
          longBreak: longBreak.value,
          workDuration: workDuration.value,
          repeats: repeats.value
        }
      )
    }

    /**
     * Clear params
     */
    const clear = async () => {
      setting1.value = ''
      setting2.value = ''
      isFocusSessionStarted.value = false
      shortBreak.value = 5
      longBreak.value = 15
      workDuration.value = 25
      repeats.value = 4

      return saveSettings()
    }
    // endregion ////

    return {
      setting1,
      setting2,
      isFocusSessionStarted,
      shortBreak,
      longBreak,
      workDuration,
      repeats,
      setB24,
      initFromBatch,
      saveSettings,
      clear
    }
  }
)
