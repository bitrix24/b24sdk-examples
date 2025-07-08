<script setup lang="ts">
import { DateTime } from 'luxon'
import { ref, reactive, computed } from 'vue'
import { B24Hook, B24LangList, Text } from '@bitrix24/b24jssdk'
import type { ISODate } from '@bitrix24/b24jssdk'
import Search1Icon from '@bitrix24/b24icons-vue/main/Search1Icon'
import AlertIcon from '@bitrix24/b24icons-vue/button/AlertIcon'
import FileDownloadIcon from '@bitrix24/b24icons-vue/main/FileDownloadIcon'
import CompanyIcon from '@bitrix24/b24icons-vue/crm/CompanyIcon'
import type { BaseResponse, Entity } from '#shared/types/base'

useHead({
  title: 'Testing Contacts and Companies'
})

const { $logger } = useAppInit('Demo: testing-contacts-companies')

const b24CurrentLang = ref<string>(B24LangList.en)
const isProcessing = ref(false)
const dataList = ref<Entity[]>([])
const result = reactive({
  isSuccess: true,
  errors: [] as string[]
})

const problemMessageList = computed(() => result.errors)

const actionEntityList = async () => {
  result.isSuccess = true
  result.errors = []
  isProcessing.value = true

  try {
    await fetchData()
  } catch (error: any) {
    result.isSuccess = false
    result.errors = [error.message || 'Failed to load']
  } finally {
    isProcessing.value = false
  }
}

const config = useRuntimeConfig()
const $b24 = B24Hook.fromWebhookUrl(config.public.b24Hook)
$b24.setLogger($logger)
$b24.offClientSideWarning()

const entityType = ref<'contact' | 'company'>('company')
const fetchData = async () => {
  try {
    dataList.value = []

    const PAGE_SIZE = 50
    const method = entityType.value === 'contact'
      ? 'crm.contact.list'
      : 'crm.company.list'

    const selectFields = entityType.value === 'contact'
      ? ['ID', 'NAME', 'LAST_NAME', 'DATE_CREATE']
      : ['ID', 'TITLE', 'DATE_CREATE']

    let start = 0
    let totalItems = 0
    let hasMore = true

    while (hasMore) {
      $logger.info(`Loading ${start + 1}-${start + PAGE_SIZE} ${entityType.value === 'contact' ? 'contacts' : 'companies'}`)

      try {
        const response = await $b24.callMethod(
          method,
          {
            order: { DATE_CREATE: 'DESC' },
            select: selectFields
          },
          start
        )

        const data = response.getData().result
        const total = response.getTotal()
        hasMore = response.isMore()

        if (totalItems === 0 && total) {
          totalItems = total
          $logger.log(`Found ${entityType.value === 'contact' ? 'contacts' : 'companies'}: ${total}`)
        }

        if (data.length > 0) {
          const formatted = data.map((item: any) => {
            if (entityType.value === 'contact') {
              return {
                id: Number(item.ID),
                title: `${item?.NAME || '-'} ${item?.LAST_NAME || '-'}`.trim(),
                createdTime: Text.toDateTime(item.DATE_CREATE as ISODate)
              }
            } else {
              return {
                id: Number(item.ID),
                title: item.TITLE,
                createdTime: Text.toDateTime(item.DATE_CREATE as ISODate)
              }
            }
          })
          dataList.value = [...dataList.value, ...formatted]
        }

        start += PAGE_SIZE

        $logger.log(`Success: start=${start - PAGE_SIZE}, received: ${data.length}`)
      } catch (error: any) {
        $logger.error(`Error requesting start=${start}:`, error)
        hasMore = false
      }
    }

    $logger.warn(`Completed! Loaded: ${dataList.value.length} ${entityType.value === 'contact' ? 'contacts' : 'companies'}`)
  } catch (error) {
    $logger.error('Error loading data:', error)
  }
}

const actionEntityAdd = async () => {
  result.isSuccess = true
  result.errors = []
  isProcessing.value = true

  try {
    const method = entityType.value === 'contact'
      ? '/api/contacts'
      : '/api/companies'

    const response = await $fetch<BaseResponse>(method, {
      method: 'POST',
      body: { needAdd: 100 }
    })
    $logger.log(response)
    if (!response.success) {
      throw new Error(response.error || 'Unknown error occurred')
    }

    await actionEntityList()
  } catch (error: any) {
    result.isSuccess = false
    result.errors = [error.message || 'Failed to add']
  } finally {
    isProcessing.value = false
  }
}
</script>

<template>
  <ClientOnly>
    <div class="mb-8 flex flex-col sm:flex-row items-center justify-between gap-8">
      <div>
        <ProseH1>
          Testing Contacts and Companies
        </ProseH1>
        <ProseP>We make a selection of <ProseCode>crm.contact.list</ProseCode> and <ProseCode>crm.company.list</ProseCode>.</ProseP>
      </div>
      <B24Advice
        class="w-full max-w-[550px]"
        :b24ui="{ descriptionWrapper: 'w-full' }"
        :avatar="{ src: '/avatar/assistant.png' }"
      >
        You need to set environment variables in the <ProseCode>.env</ProseCode> file
      </B24Advice>
    </div>
    <B24Separator />

    <div class="mt-8 flex flex-col lg:flex-row gap-2">
      <B24RadioGroup
        v-model="entityType"
        :items="['contact', 'company']"
        size="sm"
        variant="table"
        orientation="horizontal"
        @update:model-value="actionEntityList"
      />
      <B24Button
        :icon="FileDownloadIcon"
        label="Load"
        color="primary"
        size="md"
        rounded
        loading-auto
        @click="actionEntityList"
      />
      <B24Button
        color="link"
        depth="dark"
        :icon="CompanyIcon"
        label="Add"
        size="md"
        rounded
        loading-auto
        @click="actionEntityAdd"
      />
    </div>
    <div
      v-show="result.isSuccess"
      class="mt-5 flex flex-col gap-1.5"
    >
      <div
        v-if="!isProcessing && dataList.length < 1"
        class="flex flex-col flex-nowrap justify-between items-center my-4 text-base-500"
      >
        <Search1Icon class="h-10 w-10" />
        <div>No data available</div>
      </div>
      <div v-if="isProcessing" class="flex flex-col flex-nowrap justify-between items-center my-4 text-base-500">
        <AlertIcon class="h-10 w-10" />
        <div>Processing ...</div>
      </div>
      <B24TableWrapper
        v-else-if="dataList.length > 0"
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
              v-for="(entity, indexEntity) in dataList"
              :key="indexEntity"
            >
              <td>
                {{ entity.id }}
              </td>
              <td>
                {{ entity.title }}
              </td>
              <td>
                {{ entity.createdTime.setLocale(b24CurrentLang).toLocaleString(DateTime.DATETIME_SHORT) }}
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
  </ClientOnly>
</template>
