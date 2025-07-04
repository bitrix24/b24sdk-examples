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
    const response = await $b24.callBatch({
      currentUser: { method: 'user.current' },
      profile: { method: 'profile' }
    })

    const data = response.getData()
    $logger.log(data)

    return {
      success: true,
      profile: {
        ...(data.currentUser || {}),
        ...({
          ADMIN: (data.profile || {}).ADMIN
        })
      } as UserProfile,
      hostName: $b24.getTargetOrigin()
    } as ProfileResponse
  } catch (error: any) {
    return {
      success: false,
      error: error.message || 'Failed to load profile'
    } as ProfileResponse
  }
})
