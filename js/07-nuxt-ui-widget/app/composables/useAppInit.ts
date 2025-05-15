import { LoggerBrowser, AjaxError } from '@bitrix24/b24jssdk'
import type { B24Frame } from '@bitrix24/b24jssdk'

export interface ProcessErrorData {
  description?: string
  isShowClearError?: boolean
  clearErrorHref?: string
  clearErrorTitle?: string
  homePageIsHide?: boolean
  homePageHref?: string
  homePageTitle?: string
}

/**
 * Composable handling application initialization
 * Coordinates data loading via batch request
 */
export const useAppInit = () => {
  const $logger = LoggerBrowser.build(
    'App',
    import.meta.env?.DEV === true
  )

  const initApp = async ($b24: B24Frame) => {
    // some action ////
    $logger.log('Make init actions', $b24)
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

  return {
    $logger,
    initApp,
    processErrorGlobal
  }
}
