import { B24Hook, EnumCrmEntityTypeId, Text, LoggerBrowser } from '@bitrix24/b24jssdk'
import { defineEventHandler, readBody } from 'h3'
import type { BaseResponse } from '#shared/types/base'

export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()

  const $logger = LoggerBrowser.build(
    'Demo: test:companies-batch-add',
    import.meta.dev
  )

  const $b24 = B24Hook.fromWebhookUrl(config.b24Hook)
  $b24.setLogger($logger)

  const body = await readBody(event)
  const count = body.count || 10

  try {
    const commands = Array(count).fill(null).map(() => ({
      method: 'crm.item.add',
      params: {
        entityTypeId: EnumCrmEntityTypeId.company,
        fields: {
          title: Text.getUuidRfc4122(),
          comments: '[B]Auto generate[/B] from server API'
        }
      }
    }))

    const response = await $b24.callBatch(commands, true)
    $logger.log(`done`, response.getData())

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
