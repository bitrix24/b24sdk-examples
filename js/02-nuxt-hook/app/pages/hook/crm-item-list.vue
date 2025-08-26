<script setup lang="ts">
import { DateTime } from 'luxon'
import { ref, reactive, computed } from 'vue'
import { B24LangList } from '@bitrix24/b24jssdk'
import FileDownloadIcon from '@bitrix24/b24icons-vue/main/FileDownloadIcon'
import CompanyIcon from '@bitrix24/b24icons-vue/crm/CompanyIcon'
import type { BaseResponse, CompaniesResponse, Company } from '#shared/types/base'
import { useAppInit } from '~/composables/useAppInit'

definePageMeta({
  layout: false,
  pageTitle: 'List of companies',
  pageDescription: 'Shows a selection of data from CRM by company.'
})

const { $logger } = useAppInit('Demo: crm-item-ist')

const b24CurrentLang = ref<string>(B24LangList.en)
const isProcessing = ref(false)

const { getRootSideBarApi } = useAppInit()
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
  getRootSideBarApi()?.setLoading(true)

  try {
    const response = await $fetch<CompaniesResponse>('/api/companies')
    $logger.log(response)
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
    getRootSideBarApi()?.setLoading(false)
  }
}

const actionCompanyAdd = async (needAdd = 10) => {
  result.isSuccess = true
  result.errors = []
  isProcessing.value = true
  getRootSideBarApi()?.setLoading(true)

  try {
    const response = await $fetch<BaseResponse>('/api/companies', {
      method: 'POST',
      body: { needAdd }
    })
    $logger.log(response)
    if (!response.success) {
      throw new Error(response.error || 'Unknown error occurred')
    }

    await actionCompanyList()
  } catch (error: any) {
    result.isSuccess = false
    result.errors = [error.message || 'Failed to add companies']
  } finally {
    isProcessing.value = false
    getRootSideBarApi()?.setLoading(false)
  }
}
</script>

<template>
  <ClientOnly>
    <NuxtLayout name="default">
      <template #header-actions>
        <B24Button
          :icon="FileDownloadIcon"
          label="Load 50 latest companies"
          color="air-primary"
          loading-auto
          @click="actionCompanyList"
        />
        <B24Button
          color="air-secondary"
          :icon="CompanyIcon"
          label="Add some companies"
          loading-auto
          @click="actionCompanyAdd(50)"
        />
      </template>

      <AdviceBanner>
        <B24Advice
          class="w-full max-w-[550px]"
          :b24ui="{ descriptionWrapper: 'w-full' }"
          :avatar="{ src: '/avatar/assistant.png' }"
        >
          You need to set environment variables in the <ProseCode color="air-primary-copilot">
            .env
          </ProseCode> file
        </B24Advice>
      </AdviceBanner>
      <div
        v-show="result.isSuccess"
      >
        <AdviceCenter
          v-if="!isProcessing && dataList.length < 1"
        >
          <B24Advice
            class="w-full max-w-[550px]"
            :b24ui="{ descriptionWrapper: 'w-full' }"
            :avatar="{ src: '/avatar/assistant.png' }"
          >
            <ProseP class="text-nowrap">
              No data available!
            </ProseP>
          </B24Advice>
        </AdviceCenter>
        <B24TableWrapper
          v-else-if="!isProcessing"
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
        title="Some Problem"
        color="air-primary-alert"
        inverted
      >
        <template #description>
          <ProseUl>
            <ProseLi v-for="(problem, index) in problemMessageList" :key="index">
              {{ problem }}
            </ProseLi>
          </ProseUl>
        </template>
      </B24Alert>
    </NuxtLayout>
  </ClientOnly>
</template>
