<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { DateTime, Interval } from 'luxon'
import { B24LangList, useFormatter } from '@bitrix24/b24jssdk'
import SendIcon from '@bitrix24/b24icons-vue/main/SendIcon'
import ParallelQueueIcon from '@bitrix24/b24icons-vue/main/ParallelQueueIcon'
import SequentialQueueIcon from '@bitrix24/b24icons-vue/main/SequentialQueueIcon'
import SpeedMeterIcon from '@bitrix24/b24icons-vue/main/SpeedMeterIcon'
import SendContactIcon from '@bitrix24/b24icons-vue/crm/SendContactIcon'
import CompanyIcon from '@bitrix24/b24icons-vue/crm/CompanyIcon'
import TrashBinIcon from '@bitrix24/b24icons-vue/main/TrashBinIcon'
import SpinnerIcon from '@bitrix24/b24icons-vue/specialized/SpinnerIcon'
import type { BaseResponse, ProfileResponse, UserProfile, CompaniesResponse, CompanyInfoResponse } from '#shared/types/base'

useHead({
  title: 'Testing Rest-Api Calls'
})

// region Init ////
const { $logger } = useAppInit('Demo: Testing Rest-Api Calls')

const { formatterNumber } = useFormatter()

const b24CurrentLang = ref<string>(B24LangList.en)
const result = reactive({
  isSuccess: true,
  errors: [] as string[]
})

interface IStatus {
  isProcess: boolean
  title: string
  messages: string[]
  processInfo: null | string
  resultInfo: null | string
  progress: {
    animation: boolean
    indicator: boolean
    value: null | number
    max: null | number
  }
  time: {
    start: null | DateTime
    stop: null | DateTime
    interval: null | Interval
  }
}

const profileData = ref<null | UserProfile>(null)
const hostName = ref('')

const status = ref<IStatus>({
  isProcess: false,
  title: 'Specify what we will test',
  messages: [],
  processInfo: null,
  resultInfo: null,
  progress: {
    animation: false,
    indicator: true,
    value: 0,
    max: 0
  },
  time: {
    start: null,
    stop: null,
    interval: null
  }
} as IStatus)

// Loading user profile via server API
onMounted(async () => {
  try {
    const data = await $fetch<ProfileResponse>('/api/profile')
    if (data.success && data.profile && data.hostName) {
      profileData.value = data.profile
      hostName.value = data.hostName
    } else {
      result.isSuccess = false
      result.errors = [data.error || 'Failed to load profile']
    }
  } catch (error: any) {
    result.isSuccess = false
    result.errors = [error.data?.error || error.message || 'Failed to load profile']
  }
})
// endregion ////

// region Tools ////
const stopMakeProcess = () => {
  status.value.isProcess = false
  status.value.time.stop = DateTime.now()
  if (status.value.time.stop && status.value.time.start) {
    status.value.time.interval = Interval.fromDateTimes(
      status.value.time.start,
      status.value.time.stop
    )
  }
  status.value.processInfo = null
}

const clearConsole = () => {
  console.clear()
}

const reInitStatus = () => {
  result.isSuccess = true
  result.errors = []

  status.value.isProcess = false
  status.value.title = 'Specify what we will test'
  status.value.messages = []
  status.value.processInfo = null
  status.value.resultInfo = null
  status.value.progress.animation = false
  status.value.progress.indicator = true
  status.value.progress.value = 0
  status.value.progress.max = 0
  status.value.time.start = null
  status.value.time.stop = null
  status.value.time.interval = null
}

const problemMessageList = computed(() => {
  return result.errors
})
// endregion ////

// region Actions ////
const listCallToMax: Ref<number> = ref(10)
const makeSelectItemsList_v1 = async () => {
  reInitStatus()
  status.value.isProcess = true
  status.value.title = 'Testing Sequential Calls'
  status.value.messages = [
    `In the loop we call $b24.callMethod one after another ${listCallToMax.value} times.`,
    'With a large number of requests, B24 will start to pause between calls.'
  ]
  status.value.progress.value = 0
  status.value.progress.max = listCallToMax.value
  status.value.time.start = DateTime.now()

  try {
    const response = await $fetch<BaseResponse>(`/api/test/sequential?count=${listCallToMax.value}`)
    $logger.log(response)
    listCallToMax.value = listCallToMax.value * 2
  } catch (error: any) {
    result.isSuccess = false
    result.errors = [error.data?.error || error.message]
  } finally {
    stopMakeProcess()
  }
}

const listCallToMaxAll: Ref<number> = ref(10)
async function makeSelectItemsList_v2() {
  reInitStatus()
  status.value.isProcess = true
  status.value.title = 'Testing Parallel Calls'
  status.value.messages = [
    `We use Promise.all. We send ${listCallToMaxAll.value} calls at once.`,
    'With a large number of requests, B24 will start to pause between calls.'
  ]
  status.value.progress.value = 0
  status.value.progress.max = listCallToMaxAll.value
  status.value.time.start = DateTime.now()

  try {
    const response = await $fetch<BaseResponse>(`/api/test/parallel?count=${listCallToMaxAll.value}`)
    $logger.log(response)
    listCallToMaxAll.value = listCallToMaxAll.value * 2
  } catch (error: any) {
    result.isSuccess = false
    result.errors = [error.data?.error || error.message]
  } finally {
    stopMakeProcess()
  }
}

async function makeSelectItemsList_v3() {
  reInitStatus()
  status.value.isProcess = true
  status.value.title = 'Getting All Elements'
  status.value.messages = [
    'We call the queries sequentially one after another and get the entire list of elements.',
    'This is not an optimal implementation for receiving large amounts of data.',
    'With a large number of requests, B24 will start to pause between calls.'
  ]
  status.value.progress.value = 0
  status.value.progress.max = 100
  status.value.time.start = DateTime.now()

  try {
    const response = await $fetch<CompaniesResponse>('/api/test/companies-all')
    $logger.log(response)
    if (response.success) {
      const ttl = (response?.items || []).length
      status.value.resultInfo = `It was chosen: ${ttl} elements`
    } else {
      throw new Error(response.error)
    }
  } catch (error: any) {
    result.isSuccess = false
    result.errors = [error.data?.error || error.message]
  } finally {
    stopMakeProcess()
  }
}

async function makeSelectItemsList_v4() {
  reInitStatus()
  status.value.isProcess = true
  status.value.title = 'Retrieve Large Volumes of Data'
  status.value.messages = [
    'Using Bitrix24 recommendations, we make a specific sequence of calls.',
    'With a large number of requests, B24 will start to pause between calls.'
  ]
  status.value.processInfo = 'processing'
  status.value.progress.animation = true
  status.value.progress.indicator = false
  status.value.progress.value = null
  status.value.time.start = DateTime.now()

  try {
    const response = await $fetch<CompaniesResponse>('/api/test/companies-large')
    $logger.log(response)
    if (response.success) {
      const ttl = (response.items || []).length
      status.value.resultInfo = `It was chosen: ${ttl} elements`
    } else {
      throw new Error(response.error)
    }
  } catch (error: any) {
    result.isSuccess = false
    result.errors = [error.data?.error || error.message]
  } finally {
    stopMakeProcess()
  }
}

async function makeSelectItemsList_v4_1() {
  reInitStatus()
  status.value.isProcess = true
  status.value.title = 'callMethod(crm.deal.list)'
  status.value.messages = []
  status.value.processInfo = 'processing'
  status.value.progress.animation = true
  status.value.progress.indicator = false
  status.value.progress.value = null
  status.value.time.start = DateTime.now()

  try {
    const response = await $fetch<CompaniesResponse>('/api/test/callMethod')
    $logger.log(response)
    if (response.success) {
      const ttl = (response.items || []).length
      status.value.resultInfo = `It was chosen: ${ttl} elements`
    } else {
      throw new Error(response.error)
    }
  } catch (error: any) {
    result.isSuccess = false
    result.errors = [error.data?.error || error.message]
  } finally {
    stopMakeProcess()
  }
}

async function makeSelectItemsList_v4_2() {
  reInitStatus()
  status.value.isProcess = true
  status.value.title = 'callListMethod(crm.deal.list)'
  status.value.messages = []
  status.value.processInfo = 'processing'
  status.value.progress.animation = true
  status.value.progress.indicator = false
  status.value.progress.value = null
  status.value.time.start = DateTime.now()

  try {
    const response = await $fetch<CompaniesResponse>('/api/test/callListMethod')
    $logger.log(response)
    if (response.success) {
      const ttl = (response.items || []).length
      status.value.resultInfo = `It was chosen: ${ttl} elements`
    } else {
      throw new Error(response.error)
    }
  } catch (error: any) {
    result.isSuccess = false
    result.errors = [error.data?.error || error.message]
  } finally {
    stopMakeProcess()
  }
}

async function makeSelectItemsList_v4_3() {
  reInitStatus()
  status.value.isProcess = true
  status.value.title = 'fetchListMethod(crm.deal.list)'
  status.value.messages = []
  status.value.processInfo = 'processing'
  status.value.progress.animation = true
  status.value.progress.indicator = false
  status.value.progress.value = null
  status.value.time.start = DateTime.now()

  try {
    const response = await $fetch<CompaniesResponse>('/api/test/fetchListMethod')
    $logger.log(response)
    if (response.success) {
      const ttl = (response.items || []).length
      status.value.resultInfo = `It was chosen: ${ttl} elements`
    } else {
      throw new Error(response.error)
    }
  } catch (error: any) {
    result.isSuccess = false
    result.errors = [error.data?.error || error.message]
  } finally {
    stopMakeProcess()
  }
}

const needAdd = ref(10)
async function makeCallBatch_v1() {
  if (needAdd.value < 1) {
    needAdd.value = 5
  } else if (needAdd.value > 50) {
    needAdd.value = 5
  }

  reInitStatus()
  status.value.isProcess = true
  status.value.title = 'Testing the batch processing work'
  status.value.messages = [
    `There will be one request. However, it contains ${needAdd.value} commands to create an entities.`,
    'With a large number of requests, B24 will start to pause between calls.'
  ]
  status.value.progress.animation = true
  status.value.progress.indicator = false
  status.value.progress.value = null
  status.value.time.start = DateTime.now()

  try {
    const response = await $fetch<BaseResponse>('/api/test/companies-batch-add', {
      method: 'POST',
      body: { count: needAdd.value }
    })
    $logger.log(response)
    status.value.resultInfo = `It was add: ${needAdd.value} elements`
    needAdd.value = needAdd.value + 10
    if (needAdd.value > 50) {
      needAdd.value = 5
    }
  } catch (error: any) {
    result.isSuccess = false
    result.errors = [error.data?.error || error.message]
  } finally {
    stopMakeProcess()
  }
}

async function makeSelectItemsList_v6() {
  const promptEntityId = prompt('Please provide your company Id')
  const needEntityId = Number(promptEntityId)

  reInitStatus()
  status.value.isProcess = true
  status.value.title = 'Testing the batch fetch work'
  status.value.messages = [
    `The entity and its responsible person data will be selected.`,
    'With a large number of requests, B24 will start to pause between calls.'
  ]
  status.value.progress.animation = true
  status.value.progress.indicator = false
  status.value.progress.value = null
  status.value.time.start = DateTime.now()

  try {
    if (Number.isNaN(needEntityId)) {
      throw new TypeError('Wrong entity Id')
    }

    const response = await $fetch<CompanyInfoResponse>(`/api/test/company-batch-fetch?companyId=${needEntityId}`)
    $logger.log(response)
    if (response.success) {
      const assigned = response.assigned
      let assignedInfo = ''
      if (assigned) {
        assignedInfo = [
          `id: ${assigned.ID}`,
          assigned.ACTIVE ? 'active' : 'not active',
          assigned.LAST_NAME,
          assigned.NAME
        ].join(' ')
      }
      status.value.resultInfo = `entityId: ${response.company?.id || '?'}; assigned: ${assignedInfo}`
    } else {
      throw new Error(response.error)
    }
  } catch (error: any) {
    result.isSuccess = false
    result.errors = [error.data?.error || error.message]
  } finally {
    stopMakeProcess()
  }
}

const makeOpenSliderForUser = (userId: number) => {
  window.open(`${hostName.value}/company/personal/user/${userId}/`)
}
// endregion ////
</script>

<template>
  <div class="flex flex-col items-top justify-top gap-8">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-8">
      <div>
        <ProseH1>
          Testing Rest-Api Calls
        </ProseH1>
        <ProseP>Shows a sample of data.</ProseP>
      </div>
      <B24Advice
        class="w-full max-w-[550px]"
        :b24ui="{ descriptionWrapper: 'w-full' }"
        :avatar="{ src: '/avatar/assistant.png' }"
      >
        <div>
          All sensitive operations are processed on the server side.<br>
          Scopes: <ProseCode>user_brief</ProseCode>, <ProseCode>crm</ProseCode><br><br>
          To view query results, open the developer console.
        </div>
      </B24Advice>
    </div>
    <B24Separator />

    <div class="flex flex-col sm:flex-row gap-10">
      <div class="flex flex-col gap-10 basis-1/4">
        <B24ButtonGroup orientation="vertical" class="w-full">
          <B24Chip :text="listCallToMax">
            <B24Button
              class="w-full"
              :icon="SequentialQueueIcon"
              label="one by one"
              :disabled="status.isProcess"
              @click="makeSelectItemsList_v1"
            />
          </B24Chip>
          <B24Chip :text="listCallToMaxAll">
            <B24Button
              class="w-full"
              :icon="ParallelQueueIcon"
              label="parallel"
              :disabled="status.isProcess"
              @click="makeSelectItemsList_v2"
            />
          </B24Chip>
          <B24Button
            class="w-full"
            :icon="SendContactIcon"
            label="get all elements"
            :disabled="status.isProcess"
            @click="makeSelectItemsList_v3"
          />
          <B24Button
            class="w-full"
            :icon="SpeedMeterIcon"
            label="get large volumes"
            :disabled="status.isProcess"
            @click="makeSelectItemsList_v4"
          />
          <B24Chip :text="needAdd">
            <B24Button
              class="w-full"
              :icon="CompanyIcon"
              label="batch creation"
              :disabled="status.isProcess"
              @click="makeCallBatch_v1"
            />
          </B24Chip>
          <B24Button
            class="w-full"
            :icon="SendIcon"
            label="batch fetch"
            :disabled="status.isProcess"
            @click="makeSelectItemsList_v6"
          />
        </B24ButtonGroup>
        <B24ButtonGroup  orientation="vertical" class="w-ful">
          <B24Button
            class="w-full"
            :icon="SpeedMeterIcon"
            label="callMethod(crm.deal.list)"
            :disabled="status.isProcess"
            @click="makeSelectItemsList_v4_1"
          />
          <B24Button
            class="w-full"
            :icon="SpeedMeterIcon"
            label="callListMethod(crm.deal.list)"
            :disabled="status.isProcess"
            @click="makeSelectItemsList_v4_2"
          />
          <B24Button
            class="w-full"
            :icon="SpeedMeterIcon"
            label="fetchListMethod(crm.deal.list)"
            :disabled="status.isProcess"
            @click="makeSelectItemsList_v4_3"
          />
        </B24ButtonGroup>
      </div>
      <div class="flex-1">
        <div class="p-lg2 border border-base-100 rounded-md col-auto md:col-span-2 lg:col-span-1 bg-white">
          <B24Advice
            class="w-full"
            :b24ui="{ descriptionWrapper: 'w-full' }"
            :avatar="{ src: profileData?.PERSONAL_PHOTO }"
          >
            <div
              v-if="profileData"
              class="col-auto md:col-span-2 lg:col-span-1 cursor-pointer"
              @click.stop="makeOpenSliderForUser(profileData?.ID || 0)"
            >
              <div class="w-full flex items-center justify-between">
                <ProseH1>
                  {{ status.title }}
                </ProseH1>
                <B24Button
                  color="link"
                  depth="dark"
                  size="sm"
                  rounded
                  :icon="TrashBinIcon"
                  label="Clear console"
                  @click.stop="clearConsole"
                />
              </div>
              <div v-show="status.messages.length > 0" class="mt-2">
                <ProseP
                  v-for="(message, index) in status.messages"
                  :key="index"
                  class="w-full text-sm text-base-500"
                >
                  {{ message }}
                </ProseP>
              </div>
              <B24Separator class="my-4" />
              <div class="font-medium">
                <div class="text-nowrap text-xs text-base-500 dark:text-base-400">
                  {{ hostName.replace('https://', '') }}
                </div>
                <div class="text-nowrap hover:underline text-info-link">
                  {{ [profileData?.LAST_NAME, profileData?.NAME].join(' ') }}
                </div>
                <B24Badge
                  v-if="profileData?.ADMIN"
                  use-fill
                  color="danger"
                  label="Administrator"
                />
              </div>
            </div>
            <div v-else class="flex items-center justify-between py-sm2 text-info">
              <SpinnerIcon class="m-auto animate-spin stroke-2 size-10" />
            </div>
          </B24Advice>
          <div class="text-md text-base-900">
            <dl class="divide-y divide-base-100">
              <div
                v-show="null !== status.time.start"
                class="mt-4 px-2 py-1 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0"
              >
                <dt class="text-sm font-medium leading-6">
                  start:
                </dt>
                <dd class="mt-1 text-sm leading-6 text-base-700 sm:col-span-2 sm:mt-0">
                  {{ (status.time?.start || DateTime.now()).setLocale(b24CurrentLang).toLocaleString(DateTime.TIME_24_WITH_SECONDS) }}
                </dd>
              </div>
              <div
                v-show="null !== status.time.stop"
                class="px-2 py-1 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0"
              >
                <dt class="text-sm font-medium leading-6">
                  stop:
                </dt>
                <dd class="mt-1 text-sm leading-6 text-base-700 sm:col-span-2 sm:mt-0">
                  {{ (status.time?.stop || DateTime.now()).setLocale(b24CurrentLang).toLocaleString(DateTime.TIME_24_WITH_SECONDS) }}
                </dd>
              </div>
              <div
                v-show="null !== status.time.interval"
                class="px-2 py-1 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0"
              >
                <dt class="text-sm font-medium leading-6">
                  interval:
                </dt>
                <dd class="mt-1 text-sm leading-6 text-base-700 sm:col-span-2 sm:mt-0">
                  {{ formatterNumber.format((status.time?.interval?.length() || 0) / 1_000) }} sec
                </dd>
              </div>
              <div
                v-show="null !== status.resultInfo"
                class="px-2 py-1 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0"
              >
                <dt class="text-sm font-medium leading-6">
                  &nbsp;
                </dt>
                <dd class="mt-1 text-sm leading-6 text-base-700 sm:col-span-2 sm:mt-0">
                  {{ status.resultInfo }}
                </dd>
              </div>
            </dl>
          </div>
          <div v-show="status.isProcess" class="mt-4">
            <div v-show="status.processInfo" class="mb-2 text-4xs text-blue-500">
              {{ status.processInfo }}
            </div>
            <B24Progress
              :animation="status.progress.animation ? 'loading' : undefined"
              :indicator="status.progress.indicator"
              :value="status.progress?.value || undefined"
              :max="status.progress?.max || 0"
            >
              <template
                v-if="status.progress.indicator"
                #indicator
              >
                <div class="text-right min-w-[60px] text-xs w-full">
                  <span class="text-blue-500">{{ status.progress.value }} / {{ status.progress.max }}</span>
                </div>
              </template>
            </B24Progress>
          </div>
        </div>
        <B24Alert
          v-show="!result.isSuccess"
          title="Error"
          class="mt-lg"
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
    </div>
  </div>
</template>
