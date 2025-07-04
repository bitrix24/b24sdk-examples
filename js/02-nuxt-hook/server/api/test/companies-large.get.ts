import { B24Hook, EnumCrmEntityTypeId, LoggerBrowser } from '@bitrix24/b24jssdk'
import { defineEventHandler } from 'h3'
import type { CompaniesResponse, Company } from '#shared/types/base'

export default defineEventHandler(async () => {
  const config = useRuntimeConfig()

  const $logger = LoggerBrowser.build(
    'Demo: test:companies-large',
    import.meta.dev
  )

  const $b24 = B24Hook.fromWebhookUrl(config.b24Hook)
  $b24.setLogger($logger)

  try {
    const items: Company[] = []
    const generator = $b24.fetchListMethod(
      'crm.item.list',
      {
        entityTypeId: EnumCrmEntityTypeId.company,
        select: ['id', 'title']
      },
      'id',
      'items'
    )

    let ttl = 0
    for await (const entities of generator) {
      for (const item of entities) {
        ttl++
        items.push({
          id: Number(item.id),
          title: item.title,
          createdTime: item.createdTime,
          detailUrl: `${$b24.getTargetOrigin()}/crm/company/details/${item.id}/`
        })
        $logger.log(`step: ${ttl}`)
      }
    }

    $logger.log(items)

    return {
      success: true,
      items
    } as CompaniesResponse
  } catch (error: any) {
    return {
      success: false,
      error: error.message
    } as CompaniesResponse
  }
})
