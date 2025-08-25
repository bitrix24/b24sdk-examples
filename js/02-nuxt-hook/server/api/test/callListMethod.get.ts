import { B24Hook, LoggerBrowser } from '@bitrix24/b24jssdk'
import { defineEventHandler } from 'h3'
import type { BaseResponse } from '#shared/types/base'
import type { DateTime } from 'luxon'

interface CrmSomeEntity {
  id: number
  title: string
  createdTime: DateTime
  detailUrl: string
}

export default defineEventHandler(async () => {
  const config = useRuntimeConfig()

  const $logger = LoggerBrowser.build(
    'Demo: test:callListMethod(crm.deal.list.get)',
    import.meta.dev
  )

  const $b24 = B24Hook.fromWebhookUrl(config.b24Hook)
  $b24.setLogger($logger)

  const now = new Date()
  const sixMonthAgo = new Date()
  sixMonthAgo.setMonth(now.getMonth() - 6)

  try {
    const response = await $b24.callListMethod(
      'crm.deal.list',
      {
        filter: {
          '=%TITLE': 'd%',
          'CATEGORY_ID': 1,
          'TYPE_ID': 'COMPLEX',
          'STAGE_ID': 'C1:NEW',
          '>OPPORTUNITY': 10000,
          '<=OPPORTUNITY': 20000,
          'IS_MANUAL_OPPORTUNITY': 'Y',
          '@ASSIGNED_BY_ID': [1, 6],
          '>DATE_CREATE': sixMonthAgo
        },
        select: [
          'ID',
          'TITLE',
          'TYPE_ID',
          'CATEGORY_ID',
          'STAGE_ID',
          'OPPORTUNITY',
          'IS_MANUAL_OPPORTUNITY',
          'ASSIGNED_BY_ID',
          'DATE_CREATE'
        ],
        order: {
          TITLE: 'ASC',
          OPPORTUNITY: 'ASC'
        }
      },
      (progress: number) => {
        $logger.log(`step: ${progress}`)
      }
    )

    const items: CrmSomeEntity[] = (response.getData() || []).map((item: any) => ({
      id: Number(item.ID),
      title: item.TITLE,
      createdTime: item.DATE_CREATE,
      detailUrl: `${$b24.getTargetOrigin()}/crm/deal/details/${item.ID}/`
    }))

    $logger.log(items)

    return {
      success: true,
      items
    } as BaseResponse & { items?: CrmSomeEntity[] }
  } catch (error: any) {
    $logger.error(error)

    return {
      success: false,
      error: error.message
    } as BaseResponse
  }
})
