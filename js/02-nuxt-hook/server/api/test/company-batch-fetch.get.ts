import { B24Hook, EnumCrmEntityTypeId, LoggerBrowser } from '@bitrix24/b24jssdk'
import { defineEventHandler } from 'h3'
import type { CompanyInfoResponse } from '#shared/types/base'

export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig()

  const $logger = LoggerBrowser.build(
    'Demo: test:company-batch-fetch',
    import.meta.dev
  )

  const $b24 = B24Hook.fromWebhookUrl(config.b24Hook)
  $b24.setLogger($logger)

  const companyId = Number(getQuery(event).companyId)

  if (!companyId) {
    return {
      success: false,
      error: 'Company ID is required'
    } as CompanyInfoResponse
  }

  try {
    const commands = {
      getCompany: {
        method: 'crm.item.get',
        params: {
          entityTypeId: EnumCrmEntityTypeId.company,
          id: companyId
        }
      },
      getAssigned: {
        method: 'user.get',
        params: {
          ID: '$result[getCompany][item][assignedById]'
        }
      }
    }

    const response = await $b24.callBatch(commands, true)
    const data = response.getData()

    const item = data.getCompany.item
    const assigned = data.getAssigned[0] || null

    $logger.log({
      item,
      assigned
    })
    return {
      success: true,
      company: {
        id: Number(item.id),
        title: item.title,
        createdTime: item.createdTime,
        detailUrl: `${$b24.getTargetOrigin()}/crm/company/details/${item.id}/`
      },
      assigned
    } as CompanyInfoResponse
  } catch (error: any) {
    return {
      success: false,
      error: error.message
    } as CompanyInfoResponse
  }
})
