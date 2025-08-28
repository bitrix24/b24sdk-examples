import { LoggerBrowser, AjaxError, LoadDataType, useB24Helper } from '@bitrix24/b24jssdk'
import type { B24Frame } from '@bitrix24/b24jssdk'
import type { SidebarLayoutInstance } from '@bitrix24/b24ui-nuxt'
import {computed, ref} from "vue";

export interface ProcessErrorData {
  description?: string
  isShowClearError?: boolean
  clearErrorHref?: string
  clearErrorTitle?: string
  homePageIsHide?: boolean
  homePageHref?: string
  homePageTitle?: string
}

let sidebarLayoutInstance: null | SidebarLayoutInstance['api'] = null

const { initB24Helper, getB24Helper } = useB24Helper()
const isInitB24Helper = ref(false)

/**
 * Composable handling application initialization
 * Coordinates data loading via batch request
 */
export const useAppInit = (loggerTitle?: string) => {
  const $logger = LoggerBrowser.build(
    loggerTitle ?? 'App',
    import.meta.dev
  )

  // Stores
  const appSettings = useAppSettingsStore()
  const userSettings = useUserSettingsStore()
  const user = useUserStore()

  /**
   * Initialize application data
   * Performs batch request and updates all stores
   */
  async function initApp($b24: B24Frame) {
    /**
     * @todo init data from helper
     */
    await initB24Helper(
      $b24,
      [
        LoadDataType.App,
        LoadDataType.Currency,
        LoadDataType.Profile
      ]
    )
    isInitB24Helper.value = true

    const commands = {
      appInfo: { method: 'app.info' },
      appSettings: { method: 'app.option.get' },
      userSettings: { method: 'user.option.get' },
      profileData: { method: 'profile' }
    }

    const response = await $b24.callBatch(commands)

    const data = response.getData()
    $logger.log('Init data >>', data)

    // Update stores with received data
    user.initFromBatch({
      NAME: data.profileData?.NAME,
      LAST_NAME: data.profileData?.LAST_NAME,
      ADMIN: data.profileData?.ADMIN === true
    })

    appSettings.setB24($b24)
    appSettings.initFromBatch({
      version: data.appInfo?.VERSION || '0.0.1',
      isTrial: data.appInfo?.STATUS === 'T',
      integrator: data.appSettings?.integrator || {},
      configSettings: data.appSettings?.configSettings || {}
    })

    userSettings.setB24($b24)
    userSettings.initFromBatch({
      configSettings: data.userSettings?.configSettings || {}
    })

    $logger.info('stop')
  }

  /**
   * Reloads data
   *
   * @todo reinit all data at Store
   */
  async function reloadData() {
    await b24Helper.value?.loadData([
      LoadDataType.App,
      LoadDataType.Currency,
      LoadDataType.Profile
    ])
  }

  const b24Helper = computed(() => {
    if (isInitB24Helper.value) {
      return getB24Helper()
    }

    return null
  })

  function processErrorGlobal(
    error: unknown | string | Error,
    processErrorData?: ProcessErrorData
  ) {
    $logger.error(error)

    let title = 'Error'
    let description = ''

    if (error instanceof AjaxError) {
      title = `[${error.name}] ${error.code} (${error.status})`
      description = `${error.message}`
    } else if (error instanceof Error) {
      description = error.message
    } else {
      description = error as string
    }

    showError({
      statusCode: 404,
      statusMessage: title,
      data: Object.assign({
        description: description,
        homePageIsHide: true,
        isShowClearError: true,
        clearErrorHref: '/main'
      }, (processErrorData ?? {})),
      cause: error,
      fatal: true
    })
  }

  function setRootSideBarApi(instance: SidebarLayoutInstance['api']) {
    sidebarLayoutInstance = instance
  }

  const getRootSideBarApi = (): null | SidebarLayoutInstance['api'] => {
    if (!sidebarLayoutInstance) {
      return null
    }

    return sidebarLayoutInstance as unknown as SidebarLayoutInstance['api']
  }

  return {
    $logger,
    initApp,
    reloadData,
    b24Helper,
    setRootSideBarApi,
    getRootSideBarApi,
    processErrorGlobal
  }
}
