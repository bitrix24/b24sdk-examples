<script setup lang="ts">
import { DateTime } from 'luxon'
import { ref, reactive, computed } from 'vue'
import { LoggerBrowser, B24LangList } from '@bitrix24/b24jssdk'
import Search1Icon from '@bitrix24/b24icons-vue/main/Search1Icon'
import AlertIcon from '@bitrix24/b24icons-vue/button/AlertIcon'
import FileDownloadIcon from '@bitrix24/b24icons-vue/main/FileDownloadIcon'
import CompanyIcon from '@bitrix24/b24icons-vue/crm/CompanyIcon'
import type { BaseResponse, CompaniesResponse, Company } from '#shared/types/base'

useHead({
  title: 'List of companies'
})

const $logger = LoggerBrowser.build(
  'Demo: crm-item-ist',
  import.meta.dev
)

const b24CurrentLang = ref<string>(B24LangList.en)
const isProcessing = ref(false)
const dataList = ref<Company[]>([])
const result = reactive({
  isSuccess: true,
  errors: [] as string[]
})

const problemMessageList = computed(() => result.errors)

const openSlider = (url: string) => {
  window.open(url)
}

const actionCompanyList = async () => {
  result.isSuccess = true
  result.errors = []
  isProcessing.value = true

  try {
    const response = await $fetch<CompaniesResponse>('/api/companies')

    if (!response.success || !response.items) {
      throw new Error(response.error || 'Failed to load companies')
    }

    dataList.value = response.items.map((item: any) => ({
      id: item.id,
      title: item.title,
      createdTime: DateTime.fromISO(item.createdTime),
      detailUrl: item.detailUrl
    }))
  } catch (error: any) {
    result.isSuccess = false
    result.errors = [error.message || 'Failed to load companies']
  } finally {
    isProcessing.value = false
  }
}

const actionCompanyAdd = async (needAdd = 10) => {
  result.isSuccess = true
  result.errors = []
  isProcessing.value = true

  try {
    const response = await $fetch<BaseResponse>('/api/companies', {
      method: 'POST',
      body: { needAdd }
    })

    if (!response.success) {
      throw new Error(response.error || 'Unknown error occurred')
    }

    await actionCompanyList()
  } catch (error: any) {
    result.isSuccess = false
    result.errors = [error.message || 'Failed to add companies']
  } finally {
    isProcessing.value = false
  }
}

// Initial data loading
actionCompanyList()
</script>

<template>
  <ClientOnly>
    <div class="mb-8 flex flex-col sm:flex-row items-center justify-between gap-8">
      <div>
        <ProseH1>
          List of companies
        </ProseH1>
        <ProseP>Shows a selection of data from CRM by company.</ProseP>
      </div>
      <B24Advice :avatar="{ src: '/avatar/assistant.png' }">
        You need to set environment variables in the <ProseCode>.env</ProseCode> file
      </B24Advice>
    </div>
    <B24Separator />

    <div class="mt-8 flex flex-col lg:flex-row gap-2">
      <B24Button
        color="primary"
        :icon="CompanyIcon"
        label="Add some companies"
        loading-auto
        @click="actionCompanyAdd(50)"
      />
      <B24Button
        :icon="FileDownloadIcon"
        label="Load 50 latest companies"
        loading-auto
        @click="actionCompanyList"
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
                  @click="openSlider(company.detailUrl)"
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
  </ClientOnly>
</template>
