import { B24Hook, LoggerBrowser } from '@bitrix24/b24jssdk'
import { defineEventHandler } from 'h3'
import type { ProfileResponse, UserProfile } from '#shared/types/base'

export default defineEventHandler(async () => {
  const config = useRuntimeConfig()

  const $logger = LoggerBrowser.build(
    'Demo: user.current',
    import.meta.dev
  )

  const $b24 = B24Hook.fromWebhookUrl(config.b24Hook)
  $b24.setLogger($logger)

  try {
    const profile = await $b24.callMethod('user.current')

    $logger.log(profile.getData().result)

    return {
      success: true,
      profile: { ...(profile.getData().result || {}) } as UserProfile,
      hostName: $b24.getTargetOrigin()
    } as ProfileResponse
  } catch (error: any) {
    return {
      success: false,
      error: error.message || 'Failed to load profile'
    } as ProfileResponse
  }
})
