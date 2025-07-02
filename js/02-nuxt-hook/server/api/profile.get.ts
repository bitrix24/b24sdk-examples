import { B24Hook, LoggerBrowser } from '@bitrix24/b24jssdk'
import { defineEventHandler } from 'h3'

export default defineEventHandler(async () => {
  const config = useRuntimeConfig()

  const $logger = LoggerBrowser.build(
    'Demo: User info',
    import.meta.dev
  )

  const $b24 = B24Hook.fromWebhookUrl(config.public.b24Hook)
  $b24.setLogger($logger)

  try {
    const response = await $b24.callMethod('user.current')

    $logger.log(response.getData())
    return {
      success: true,
      profile: response.getData(),
      hostName: $b24.getTargetOrigin()
    }
  } catch (error: any) {
    return {
      success: false,
      message: error.message || 'Failed to load user'
    }
  }
})
