import { B24Hook, LoggerBrowser } from '@bitrix24/b24jssdk'
import { defineEventHandler } from 'h3'
import type { BaseResponse } from '#shared/types/base'

export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()

  const $logger = LoggerBrowser.build(
    'Demo: test:parallel',
    import.meta.dev
  )

  const $b24 = B24Hook.fromWebhookUrl(config.b24Hook)
  $b24.setLogger($logger)

  const count = Number(getQuery(event).count) || 10

  try {
    const promises = []
    for (let i = 0; i < count; i++) {
      promises.push($b24.callMethod('user.current'))
    }
    await Promise.all(promises)
    $logger.log(`done`)

    return {
      success: true
    } as BaseResponse
  } catch (error: any) {
    return {
      success: false,
      error: error.message || 'Failed to load parallel'
    } as BaseResponse
  }
})
