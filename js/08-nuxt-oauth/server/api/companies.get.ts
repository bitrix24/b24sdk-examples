import { defineEventHandler, getQuery } from 'h3'
import { useBitrix24 } from '~~/server/composables/useBitrix24'
import { LoggerBrowser, EnumCrmEntityTypeId } from '@bitrix24/b24jssdk'
import type { ListPayload } from '@bitrix24/b24jssdk'
import type { BitrixCompany } from '~/types'

const $logger = LoggerBrowser.build('api/companies.get', true)

export default defineEventHandler(async (event) => {
  const session = await requireUserSession(event)

  const { $b24 } = useBitrix24(event, session, $logger)

  const { page } = getQuery(event)
  const pageNumber = Number.parseInt(page as string) || 1
  const start = (pageNumber - 1) * 50

  try {
    const response = await $b24.callMethod(
      'crm.item.list',
      {
        entityTypeId: EnumCrmEntityTypeId.company,
        select: ['id', 'title'],
        order: { id: 'ASC' }
      },
      start
    )

    $logger.log(
      page,
      'crm.item.list',
      {
        entityTypeId: EnumCrmEntityTypeId.company,
        select: ['id', 'title'],
        order: { id: 'ASC' }
      },
      start
    )

    const data = response.getData() as ListPayload<BitrixCompany>

    const list = data.result.items
    const total = data.total
    const next = data.next
    const time = data.time
    const nextPage = list.length >= 50 ? pageNumber + 1 : null

    $logger.log({
      nextPage,
      next,
      // list,
      total
    })

    return {
      nextPage,
      list,
      total,
      time
    }
  } catch (error) {
    $logger.error('Bitrix24 Error:', error)

    throw createError({
      statusCode: 500,
      message: 'Error from Bitrix24'
    })
  }
})
