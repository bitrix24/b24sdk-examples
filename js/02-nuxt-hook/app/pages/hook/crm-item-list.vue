<script setup lang="ts">
import { DateTime } from 'luxon'
import { ref, reactive, computed, type Ref } from 'vue'
import Search1Icon from '@bitrix24/b24icons-vue/main/Search1Icon'
import AlertIcon from '@bitrix24/b24icons-vue/button/AlertIcon'
import FileDownloadIcon from '@bitrix24/b24icons-vue/main/FileDownloadIcon'
import CompanyIcon from '@bitrix24/b24icons-vue/crm/CompanyIcon'

import {
  LoggerBrowser,
  Result,
  B24Hook,
  EnumCrmEntityTypeId,
  Text, B24LangList
} from '@bitrix24/b24jssdk'
import type { IResult, ISODate } from '@bitrix24/b24jssdk'

useHead({
  title: 'List of companies'
})

const $logger = LoggerBrowser.build(
  'Demo: crm.items.list',
  import.meta.env?.DEV === true
)

/**
 * @todo fix this
 * @memo this work at server side
 */
const config = useRuntimeConfig()
const $b24 = B24Hook.fromWebhookUrl(config.public.b24Hook)
$b24.setLogger($logger)
const b24Domain = $b24.getTargetOrigin()

let result: IResult = reactive(new Result())
const isProcessLoadB24: Ref<boolean> = ref(false)
const dataList: Ref<{
  id: number
  title: string
  createdTime: DateTime
}[]> = ref([])
const problemMessageList = computed(() => {
  let problemMessageList: string[] = []
  const problem = result.getErrorMessages()
  if (typeof (problem || '') === 'string') {
    problemMessageList.push(problem.toString())
  } else if (Array.isArray(problem)) {
    problemMessageList = problemMessageList.concat(problem)
  }

  return problemMessageList
})

const openSlider = async (id: number): Promise<void> => {
  window.open(
    `${b24Domain}/crm/company/details/${id}/`
  )

  return Promise.resolve()
}
// $b24.offClientSideWarning() ////

const b24CurrentLang: Ref<string> = ref(B24LangList.en)

const actionCompanyAdd = async (needAdd: number = 10): Promise<void> => {
  result = reactive(new Result())

  const commands = []

  if (needAdd < 1) {
    needAdd = 1
  } else if (needAdd > 50) {
    needAdd = 50
  }

  let iterator = 0
  while (iterator < needAdd) {
    iterator++
    commands.push({
      method: 'crm.item.add',
      params: {
        entityTypeId: EnumCrmEntityTypeId.company,
        fields: {
          title: Text.getUuidRfc4122(),
          comments: '[B]Auto generate[/B] from [URL=https://bitrix24.github.io/b24jssdk/]@bitrix24/b24jssdk-playground[/URL]'
        }
      }
    })
  }

  let data: any
  isProcessLoadB24.value = true

  return $b24.callBatch(
    commands,
    true
  )
    .then((response: Result) => {
      data = response.getData()
      $logger.info('response >> ', data)

      return actionCompanyList()
    })
    .catch((error: Error | string) => {
      result.addError(error)
      $logger.error(error)
    })
    .finally(() => {
      isProcessLoadB24.value = false
      $logger.info('load >> stop ')
    })
}

const actionCompanyList = async (): Promise<void> => {
  result = reactive(new Result())

  const commands = {
    CompanyList: {
      method: 'crm.item.list',
      params: {
        entityTypeId: EnumCrmEntityTypeId.company,
        order: { id: 'desc' },
        select: [
          'id',
          'title',
          'createdTime'
        ]
      }
    }
  }

  let data: any

  isProcessLoadB24.value = true

  return $b24.callBatch(
    commands,
    true
  )
    .then((response: Result) => {
      data = response.getData()
      $logger.info('response >> ', data)

      dataList.value = (data.CompanyList.items || []).map((item: any) => {
        return {
          id: Number(item.id),
          title: item.title,
          createdTime: Text.toDateTime(item.createdTime as ISODate)
        }
      })
    })
    .catch((error: Error | string) => {
      result.addError(error)
      $logger.error(error)
    })
    .finally(() => {
      isProcessLoadB24.value = false
      $logger.info('load >> stop ')
    })
}

actionCompanyList()
</script>

<template>
  <div>
    <B24Alert color="warning">
      <template #description>
        You need to set environment variables in the <ProseCode>.env</ProseCode> file
      </template>
    </B24Alert>
    <div class="mt-6 flex flex-col lg:flex-row gap-2">
      <B24Button
        color="primary"
        :icon="CompanyIcon"
        :disabled="isProcessLoadB24"
        label="Add some companies"
        @click="actionCompanyAdd(50)"
      />
      <B24Button
        :icon="FileDownloadIcon"
        :disabled="isProcessLoadB24"
        label="Load 50 latest companies"
        @click="actionCompanyList"
      />
    </div>
    <div
      v-show="result.isSuccess"
      class="mt-5 flex flex-col gap-1.5"
    >
      <div
        v-if="!isProcessLoadB24 && dataList.length < 1"
        class="flex flex-col flex-nowrap justify-between items-center my-4 text-base-500"
      >
        <Search1Icon class="h-10 w-10" />
        <div>No data available</div>
      </div>
      <div v-if="isProcessLoadB24" class="flex flex-col flex-nowrap justify-between items-center my-4 text-base-500">
        <AlertIcon class="h-10 w-10" />
        <div>Processing ...</div>
      </div>
      <B24TableWrapper
        v-else
        class="overflow-x-auto w-full"
        zebra
        row-hover
      >
        <table>
          <thead>
            <tr>
              <th>
                Id
              </th>
              <th>
                Title
              </th>
              <th>
                Created
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(company, indexCompany) in dataList"
              :key="indexCompany"
            >
              <td>
                {{ company.id }}
              </td>
              <td>
                <B24Link
                  is-action
                  @click="openSlider(company.id)"
                >
                  {{ company.title }}
                </B24Link>
              </td>
              <td>
                {{ company.createdTime.setLocale(b24CurrentLang).toLocaleString(DateTime.DATETIME_SHORT) }}
              </td>
            </tr>
          </tbody>
        </table>
      </B24TableWrapper>
    </div>
    <B24Alert
      v-show="!result.isSuccess"
      title="Error"
      class="mt-4"
      color="danger"
    >
      <template #description>
        <ProseUl class="text-txt-md">
          <ProseLi v-for="(problem, index) in problemMessageList" :key="index">
            {{ problem }}
          </ProseLi>
        </ProseUl>
      </template>
    </B24Alert>
  </div>
</template>
