<script setup lang="ts">
import { ref, reactive, type Ref, computed } from 'vue'
import { DateTime, Interval } from 'luxon'
import {
  LoggerBrowser,
  Result,
  B24Hook,
  useB24Helper,
  LoadDataType,
  EnumCrmEntityTypeId,
  Text,
  useFormatter, B24LangList
} from '@bitrix24/b24jssdk'
import type { IResult, UserBrief } from '@bitrix24/b24jssdk'
import SendIcon from '@bitrix24/b24icons-vue/main/SendIcon'
import ParallelQueueIcon from '@bitrix24/b24icons-vue/main/ParallelQueueIcon'
import SequentialQueueIcon from '@bitrix24/b24icons-vue/main/SequentialQueueIcon'
import SpeedMeterIcon from '@bitrix24/b24icons-vue/main/SpeedMeterIcon'
import SendContactIcon from '@bitrix24/b24icons-vue/crm/SendContactIcon'
import CompanyIcon from '@bitrix24/b24icons-vue/crm/CompanyIcon'
import TrashBinIcon from '@bitrix24/b24icons-vue/main/TrashBinIcon'
import SpinnerIcon from '@bitrix24/b24icons-vue/specialized/SpinnerIcon'

useHead({
  title: 'Testing Rest-Api calls'
})

// region Init ////
const $logger = LoggerBrowser.build(
  'Demo: Testing Rest-Api Calls',
  import.meta.env?.DEV === true
)
const { initB24Helper, getB24Helper } = useB24Helper()
const { formatterNumber } = useFormatter()

let result: IResult = reactive(new Result())

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

const b24CurrentLang: Ref<string> = ref(B24LangList.en)

const status: Ref<IStatus> = ref({
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

/**
 * @todo fix this
 * @memo this work at server side
 */
const config = useRuntimeConfig()
const $b24 = B24Hook.fromWebhookUrl(config.public.b24Hook)
$b24.setLogger(LoggerBrowser.build('Core', true))
// $b24.offClientSideWarning() ////
const b24Domain = $b24.getTargetOrigin()

const $isInitB24Helper = ref(false)
initB24Helper(
  $b24,
  [
    LoadDataType.Profile
  ]
)
  .then(() => {
    $isInitB24Helper.value = true
  })
  .catch((error: Error | string) => {
    result.addError(error)
    $logger.error(error)
  })

const b24Helper = computed(() => {
  if ($isInitB24Helper.value) {
    return getB24Helper()
  }

  return null
})
// endregion ////

// region Actions ////
const stopMakeProcess = () => {
  status.value.isProcess = false
  status.value.time.stop = DateTime.now()
  if (
    status.value.time.stop
    && status.value.time.start
  ) {
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
  result = reactive(new Result())

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

const listCallToMax: Ref<number> = ref(10)
const makeSelectItemsList_v1 = async () => {
  return new Promise((resolve) => {
    reInitStatus()
    status.value.isProcess = true
    status.value.title = 'Testing Sequential Calls'
    status.value.messages.push(`In the loop we call $b24.callMethod one after another ${listCallToMax.value} times.`)
    status.value.messages.push('With a large number of requests, B24 will start to pause between calls.')
    status.value.progress.value = 0
    status.value.progress.max = listCallToMax.value
    status.value.time.start = DateTime.now()

    return resolve(null)
  })
    .then(async () => {
      let iterator = status.value.progress.max || 0
      while (iterator > 0) {
        iterator--
        if (null !== status.value.progress.value) {
          status.value.progress.value++
        }
        $logger.log(`>> Testing Sequential Calls >>> ${status.value.progress.value} | ${status.value.progress.max}`)

        await $b24.callMethod(
          'user.current'
        )
      }
    })
    .then(() => {
      listCallToMax.value = listCallToMax.value * 2
    })
    .catch((error: Error | string) => {
      result.addError(error)
      $logger.error(error)
    })
    .finally(() => {
      stopMakeProcess()
    })
}

const listCallToMaxAll: Ref<number> = ref(10)

async function makeSelectItemsList_v2() {
  return new Promise((resolve) => {
    reInitStatus()
    status.value.isProcess = true
    status.value.title = 'Testing Parallel Calls'
    status.value.messages.push(`We use Promise.all. We send ${listCallToMaxAll.value} calls at once.`)
    status.value.messages.push('With a large number of requests, B24 will start to pause between calls.')
    status.value.progress.value = 0
    status.value.progress.max = listCallToMaxAll.value
    status.value.time.start = DateTime.now()

    return resolve(null)
  })
    .then(async () => {
      const list = []
      let iterator = status.value.progress.max || 0

      while (iterator > 0) {
        iterator--
        list.push(
          $b24.callMethod(
            'user.current',
            {}
          ).finally(() => {
            (status.value.progress.value as number)++
            $logger.log(`>> Testing Parallel Calls >>> ${status.value.progress.value} | ${status.value.progress.max}`)
          })
        )
      }

      await Promise.all(list)
    })
    .then(() => {
      listCallToMaxAll.value = listCallToMaxAll.value * 2
    })
    .catch((error: Error | string) => {
      result.addError(error)
      $logger.error(error)
    })
    .finally(() => {
      stopMakeProcess()
    })
}

async function makeSelectItemsList_v3() {
  return new Promise((resolve) => {
    reInitStatus()
    status.value.isProcess = true
    status.value.title = 'Getting All Elements'
    status.value.messages.push('We call the queries sequentially one after another and get the entire list of elements.')
    status.value.messages.push('This is not an optimal implementation for receiving large amounts of data.')
    status.value.messages.push('With a large number of requests, B24 will start to pause between calls.')
    status.value.progress.value = 0
    status.value.progress.max = 100
    status.value.time.start = DateTime.now()

    return resolve(null)
  })
    .then(async () => {
      return $b24.callListMethod(
        'crm.item.list',
        {
          entityTypeId: EnumCrmEntityTypeId.company
        },
        (progress: number) => {
          status.value.progress.value = progress
          $logger.log(`>> Getting All Elements >>> ${status.value.progress.value}`)
        },
        'items'
      )
        .then((response) => {
          const ttl = response.getData().length
          $logger.log(`>> Getting All Elements >>> ttl: ${ttl}`)
          status.value.resultInfo = `It was chosen: ${formatterNumber?.format(ttl)} elements`
        })
    })
    .catch((error: Error | string) => {
      result.addError(error)
      $logger.error(error)
    })
    .finally(() => {
      stopMakeProcess()
    })
}

async function makeSelectItemsList_v4() {
  return new Promise((resolve) => {
    reInitStatus()
    status.value.isProcess = true
    status.value.title = 'Retrieve Large Volumes of Data'
    status.value.messages.push('Using Bitrix24 recommendations, we make a specific sequence of calls.')
    status.value.messages.push('With a large number of requests, B24 will start to pause between calls.')

    status.value.processInfo = 'processing'
    status.value.progress.animation = true
    status.value.progress.indicator = false
    status.value.progress.value = null
    status.value.time.start = DateTime.now()

    return resolve(null)
  })
    .then(async () => {
      const generator = $b24.fetchListMethod(
        'crm.item.list',
        {
          entityTypeId: EnumCrmEntityTypeId.company,
          select: [
            'id',
            'title'
          ]
        },
        'id',
        'items'
      )

      let ttl = 0

      for await (const entities of generator) {
        for (const entity of entities) {
          ttl++
          $logger.log(`>> Retrieve Large Volumes of Data >>> entity ${ttl} ...`, entity)

          status.value.processInfo = `[id:${entity.id}] ${entity.title}`
        }
      }
      status.value.resultInfo = `It was chosen: ${formatterNumber.format(ttl)} elements`
    })
    .catch((error: Error | string) => {
      result.addError(error)
      $logger.error(error)
    })
    .finally(() => {
      stopMakeProcess()
    })
}

const needAdd = ref(10)

async function makeCallBatch_v1() {
  if (needAdd.value < 1) {
    needAdd.value = 5
  } else if (needAdd.value > 50) {
    needAdd.value = 5
  }

  return new Promise((resolve) => {
    reInitStatus()
    status.value.isProcess = true
    status.value.title = 'Testing the batch processing work'
    status.value.messages.push(`There will be one request. However, it contains ${needAdd.value} commands to create an entities.`)
    status.value.messages.push('With a large number of requests, B24 will start to pause between calls.')

    status.value.progress.animation = true
    status.value.progress.indicator = false
    status.value.progress.value = null
    status.value.time.start = DateTime.now()

    return resolve(null)
  })
    .then(() => {
      const commands = []

      let iterator = 0
      while (iterator < needAdd.value) {
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

      $logger.info('Testing the batch processing work >> send >>> ', commands)
      return $b24.callBatch(
        commands,
        true
      )
    })
    .then((response: Result) => {
      const data: any = response.getData()
      $logger.info('Testing the batch processing work >> response >>> ', data)

      status.value.resultInfo = `It was add: ${needAdd.value} elements`
    })
    .then(() => {
      needAdd.value = needAdd.value + 10
      if (needAdd.value > 50) {
        needAdd.value = 5
      }
    })
    .catch((error: Error | string) => {
      result.addError(error)
      $logger.error(error)
    })
    .finally(() => {
      stopMakeProcess()
    })
}

async function makeSelectItemsList_v6() {
  const promptEntityId = prompt(
    'Please provide your company Id'
  )

  const needEntityId = Number(promptEntityId)

  return new Promise((resolve) => {
    reInitStatus()
    status.value.isProcess = true
    status.value.title = 'Testing the batch fetch work'
    status.value.messages.push(`The entity and its responsible person data will be selected.`)
    status.value.messages.push('With a large number of requests, B24 will start to pause between calls.')

    status.value.progress.animation = true
    status.value.progress.indicator = false
    status.value.progress.value = null
    status.value.time.start = DateTime.now()

    return resolve(null)
  })
    .then(() => {
      if (Number.isNaN(needEntityId)) {
        return Promise.reject(new Error('Wrong entity Id'))
      }

      const commands = {
        getCompany: {
          method: 'crm.item.get',
          params: {
            entityTypeId: EnumCrmEntityTypeId.company,
            id: needEntityId
          }
        },
        getAssigned: {
          method: 'user.get',
          params: {
            ID: '$result[getCompany][item][assignedById]'
          }
        }
      }

      $logger.info('Testing the batch fetch work >> send >>> ', commands)
      return $b24.callBatch(
        commands,
        true
      )
    })
    .then((response: Result) => {
      const data: any = response.getData()
      $logger.info('Testing the batch fetch work >> response >>> ', data)

      const assigned = data.getAssigned[0] as UserBrief | null

      let assignedInfo = ''
      if (assigned) {
        assignedInfo = [
          `id: ${assigned.ID}`,
          assigned.ACTIVE ? 'active' : 'not active',
          assigned.LAST_NAME,
          assigned.NAME
        ].join(' ')
      }

      status.value.resultInfo = `entityId: ${data.getCompany.item?.id || '?'}; assigned: ${assignedInfo}`
    })
    .catch((error: Error | string) => {
      result.addError(error)
      $logger.error(error)
    })
    .finally(() => {
      stopMakeProcess()
    })
}

const makeOpenSliderForUser = async (userId: number) => {
  window.open(
    `${b24Domain}/company/personal/user/${userId}/`
  )

  return Promise.resolve()
}
// endregion ////

// region Error ////
const problemMessageList = (result: IResult) => {
  let problemMessageList: string[] = []
  const problem = result.getErrorMessages()
  if (typeof (problem || '') === 'string') {
    problemMessageList.push(problem.toString())
  } else if (Array.isArray(problem)) {
    problemMessageList = problemMessageList.concat(problem)
  }

  return problemMessageList
}
// endregion ////
</script>

<template>
  <div>
    <div class="flex flex-col sm:flex-row items-center justify-between gap-x-10 gap-y-2">
      <div v-show="result.isSuccess" class="basis-1/4">
        <div
          v-if="$isInitB24Helper"
          class="px-lg2 py-sm2 border border-base-100 rounded-lg hover:shadow-md hover:-translate-y-px col-auto md:col-span-2 lg:col-span-1 bg-white cursor-pointer"
          @click.stop="makeOpenSliderForUser(b24Helper?.profileInfo.data.id || 0)"
        >
          <div class="flex items-center gap-4">
            <B24Avatar
              :src="b24Helper?.profileInfo.data.photo || ''"
              :alt="b24Helper?.profileInfo.data.lastName || 'user' "
            />
            <div class="font-medium">
              <div class="text-nowrap text-xs text-base-500 dark:text-base-400">
                {{ b24Helper?.hostName.replace('https://', '') }}
              </div>
              <div class="text-nowrap hover:underline text-info-link">
                {{
                  [
                    b24Helper?.profileInfo.data.lastName,
                    b24Helper?.profileInfo.data.name
                  ].join(' ')
                }}
              </div>
              <B24Badge
                v-if="b24Helper?.profileInfo.data.isAdmin"
                use-fill
                color="danger"
                label="Administrator"
              />
            </div>
          </div>
        </div>
        <div v-else class="flex items-center justify-between py-sm2 text-info">
          <SpinnerIcon class="m-auto animate-spin stroke-2 size-10" />
        </div>
      </div>
      <div class="flex-1 w-full">
        <B24Alert color="warning">
          <template #description>
            You need to set environment variables in the <ProseCode>.env</ProseCode> file.<br>
            Scopes: <ProseCode>user_brief</ProseCode>, <ProseCode>crm</ProseCode><br><br>
            To view query results, open the developer console.
          </template>
        </B24Alert>
      </div>
    </div>
    <div class="mt-xl flex flex-col sm:flex-row gap-10">
      <div class="flex basis-1/4">
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
      </div>
      <div class="flex-1">
        <div class="p-lg2 border border-base-100 rounded-md col-auto md:col-span-2 lg:col-span-1 bg-white">
          <div>
            <div class="w-full flex items-center justify-between">
              <ProseH1>
                {{ status.title }}
              </ProseH1>
              <B24Button
                :icon="TrashBinIcon"
                label="Clear console"
                @click="clearConsole"
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
          </div>
          <div class="text-md text-base-900">
            <dl class="divide-y divide-base-100">
              <div v-show="null !== status.time.start" class="mt-4 px-2 py-1 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                <dt class="text-sm font-medium leading-6">
                  start:
                </dt>
                <dd class="mt-1 text-sm leading-6 text-base-700 sm:col-span-2 sm:mt-0">
                  {{ (status.time?.start || DateTime.now()).setLocale(b24CurrentLang).toLocaleString(DateTime.TIME_24_WITH_SECONDS) }}
                </dd>
              </div>
              <div v-show="null !== status.time.stop" class="px-2 py-1 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                <dt class="text-sm font-medium leading-6">
                  stop:
                </dt>
                <dd class="mt-1 text-sm leading-6 text-base-700 sm:col-span-2 sm:mt-0">
                  {{ (status.time?.stop || DateTime.now()).setLocale(b24CurrentLang).toLocaleString(DateTime.TIME_24_WITH_SECONDS) }}
                </dd>
              </div>
              <div v-show="null !== status.time.interval" class="px-2 py-1 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                <dt class="text-sm font-medium leading-6">
                  interval:
                </dt>
                <dd class="mt-1 text-sm leading-6 text-base-700 sm:col-span-2 sm:mt-0">
                  {{ formatterNumber.format((status.time?.interval?.length() || 0) / 1_000) }} sec
                </dd>
              </div>
              <div v-show="null !== status.resultInfo" class="px-2 py-1 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
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
                  <span class="text-blue-500">{{ status.progress.value }} / {{
                    status.progress.max
                  }}</span>
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
              <ProseLi v-for="(problem, index) in problemMessageList(result)" :key="index">
                {{ problem }}
              </ProseLi>
            </ProseUl>
          </template>
        </B24Alert>
      </div>
    </div>
  </div>
</template>
