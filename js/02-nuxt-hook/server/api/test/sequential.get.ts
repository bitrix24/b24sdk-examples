import { B24Hook, LoggerBrowser } from '@bitrix24/b24jssdk'
import { defineEventHandler } from 'h3'
import type { BaseResponse } from '#shared/types/base'

export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()

  const $logger = LoggerBrowser.build(
    'Demo: test:sequential',
    import.meta.dev
  )

  const $b24 = B24Hook.fromWebhookUrl(config.b24Hook)
  $b24.setLogger($logger)

  const count = Number(getQuery(event).count) || 10

  try {
    for (let i = 0; i < count; i++) {
      await $b24.callMethod('user.current')
      $logger.log(`step: ${i + 1}`)
    }
    $logger.log(`done`)

    return {
      success: true
    } as BaseResponse
  } catch (error: any) {
    return {
      success: false,
      error: error.message || 'Failed to load sequential'
    } as BaseResponse
  }
})
