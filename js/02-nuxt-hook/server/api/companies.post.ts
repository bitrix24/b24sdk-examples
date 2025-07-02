import { B24Hook, EnumCrmEntityTypeId, LoggerBrowser, Text } from '@bitrix24/b24jssdk'
import { defineEventHandler, readBody } from 'h3'

export default defineEventHandler(async (event) => {
  const body = await readBody(event)

  const config = useRuntimeConfig()
  const $logger = LoggerBrowser.build(
    'Demo: crm.item.add',
    import.meta.dev
  )

  const $b24 = B24Hook.fromWebhookUrl(config.public.b24Hook)
  $b24.setLogger($logger)

  const needAdd = Math.min(50, Math.max(1, body.needAdd || 10))

  const commands = Array(needAdd).fill(null).map(() => ({
    method: 'crm.item.add',
    params: {
      entityTypeId: EnumCrmEntityTypeId.company,
      fields: {
        title: Text.getUuidRfc4122(),
        comments: '[B]Auto generate[/B] from server API'
      }
    }
  }))

  try {
    const response = await $b24.callBatch(commands, true)

    $logger.info('response >> ', response.getData())

    return { success: true }
  } catch (error: any) {
    return {
      success: false,
      message: error.message || 'Failed to create companies'
    }
  }
})