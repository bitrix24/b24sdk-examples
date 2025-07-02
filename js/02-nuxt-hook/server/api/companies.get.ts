import { B24Hook, EnumCrmEntityTypeId, LoggerBrowser } from '@bitrix24/b24jssdk'
import { defineEventHandler } from 'h3'

export default defineEventHandler(async () => {
  const config = useRuntimeConfig()

  const $logger = LoggerBrowser.build(
    'Demo: crm.item.list',
    import.meta.dev
  )

  const $b24 = B24Hook.fromWebhookUrl(config.public.b24Hook)
  $b24.setLogger($logger)

  try {
    const response = await $b24.callBatch({
      CompanyList: {
        method: 'crm.item.list',
        params: {
          entityTypeId: EnumCrmEntityTypeId.company,
          order: { id: 'desc' },
          select: ['id', 'title', 'createdTime']
        }
      }
    }, true)

    const items = (response.getData().CompanyList.items || []).map((item: any) => ({
      id: Number(item.id),
      title: item.title,
      createdTime: item.createdTime,
      detailUrl: `${$b24.getTargetOrigin()}/crm/company/details/${item.id}/`
    }))

    $logger.log(items)
    return {
      success: true,
      items
    }
  } catch (error: any) {
    return {
      success: false,
      message: error.message || 'Failed to load companies'
    }
  }
})
