import { LoggerBrowser, AjaxError } from '@bitrix24/b24jssdk'
import type { SidebarLayoutInstance } from '@bitrix24/b24ui-nuxt'

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

/**
 * Composable handling application initialization
 * Coordinates data loading via batch request
 */
export const useAppInit = (loggerTitle?: string) => {
  const $logger = LoggerBrowser.build(
    loggerTitle ?? 'App',
    import.meta.dev
  )

  const initApp = async () => {
    $logger.log('Init data >>')
  }

  function processErrorGlobal(
    error: unknown | string | Error,
    pocessErrorData?: ProcessErrorData
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
        clearErrorHref: '/'
      }, (pocessErrorData || {})),
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
    setRootSideBarApi,
    getRootSideBarApi,
    processErrorGlobal
  }
}
