<script setup lang="ts">
import { ref, computed, type Ref, type ComputedRef } from 'vue'
import { LoggerBrowser, LoggerType } from '@bitrix24/b24jssdk'
import TrashBinIcon from '@bitrix24/b24icons-vue/main/TrashBinIcon'
import SendIcon from '@bitrix24/b24icons-vue/main/SendIcon'

useHead({
  title: 'LoggerBrowser'
})

const isDevelopment: Ref<boolean> = ref(import.meta.env?.DEV === true)
const debugMessage: Ref<string> = ref('This is a test message for debugging.')

const $logger = LoggerBrowser.build(
  'Demo: Logger',
  isDevelopment.value
)

function clearConsole(): void {
  console.clear()
}

function makeLog(type: LoggerType): void {
  switch (type) {
    case LoggerType.desktop:
      $logger.desktop(debugMessage.value)
      break
    case LoggerType.log:
      $logger.log(debugMessage.value)
      break
    case LoggerType.info:
      $logger.info(debugMessage.value)
      break
    case LoggerType.warn:
      $logger.warn(debugMessage.value)
      break
    case LoggerType.error:
      $logger.error(debugMessage.value)
      break
    case LoggerType.trace:
      $logger.trace(debugMessage.value)
      break
  }
}

function makeLoggAll(): void {
  for (const type in LoggerType) {
    makeLog(type as LoggerType)
  }
}

function toggleLogger(type: LoggerType): void {
  $logger.info(`toggle done ...`)
  if ($logger.isEnabled(type)) {
    $logger.disable(type)
    return
  }

  $logger.enable(type)
  return
}

type LoggerTypeList = {
  index: string
  description: string
  type: LoggerType
  isEnabled: ComputedRef<boolean>
  state: Ref<boolean>
}

const loggerTypeList: ComputedRef<LoggerTypeList[]> = computed<LoggerTypeList[]>(() => {
  return Object.keys(LoggerType).map((key: string) => {
    const loggerType = LoggerType[key] as LoggerType
    const row = {
      index: key,
      description: '?',
      type: loggerType,
      isEnabled: computed<boolean>(() => $logger.isEnabled(loggerType)),
      state: ref($logger.isEnabled(loggerType))
    } as LoggerTypeList

    switch (row.type) {
      case LoggerType.desktop:
        row.description = 'Used to display messages specific to the desktop application (not typically used in the browser).'
        break
      case LoggerType.log:
        row.description = 'Used to display general information messages (disabled by default).'
        break
      case LoggerType.info:
        row.description = 'Used to display informational messages that are important for understanding the operation of the application (disabled by default).'
        break
      case LoggerType.warn:
        row.description = 'Used to display warnings about potential problems (disabled by default).'
        break
      case LoggerType.error:
        row.description = 'Used to output application errors (enabled by default).'
        break
      case LoggerType.trace:
        row.description = 'Call stack tracing for debugging (enabled by default).'
        break
    }

    return row
  })
})
</script>

<template>
  <div class="flex flex-col items-top justify-top gap-8">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-8">
      <div>
        <ProseH1>
          LoggerBrowser
        </ProseH1>
        <ProseP>Example of working with the LoggerBrowser object.</ProseP>
      </div>
      <B24Advice :avatar="{ src: '/avatar/assistant.png' }">
        To view the result, open the developer console.
      </B24Advice>
    </div>
    <B24Separator />

    <div class="flex flex-col flex-nowrap gap-2">
      <B24Textarea
        v-model="debugMessage"
        placeholder="Enter a message for the log"
      />
      <div class="flex flex-col lg:flex-row gap-2">
        <B24Button
          :icon="SendIcon"
          label="Send log"
          @click="makeLoggAll"
        />
        <B24Button
          :icon="TrashBinIcon"
          label="Clear console"
          @click="clearConsole"
        />
      </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
      <div
        v-for="(type) in loggerTypeList"
        :key="type.index"
        class="px-lg2 py-sm2 border border-base-30 rounded-md shadow-sm hover:shadow-md sm:rounded-md col-auto md:col-span-2 lg:col-span-1 bg-white dark:bg-base-200/10 text-base-500 dark:text-base-200"
      >
        <div class="px-4 sm:px-0">
          <ProseH3 >
            {{ type.index }}
          </ProseH3>
          <ProseP class="text-sm">
            {{ type.description }}
          </ProseP>
        </div>
        <div class="mt-4 border-t border-base-100">
          <dl class="divide-y divide-base-100">
            <div class="px-2 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
              <dt class="text-sm font-medium leading-6">
                State
              </dt>
              <dd class="mt-1 sm:col-span-2 sm:mt-0">
                <B24Switch
                  v-model="type.state.value"
                  @change="toggleLogger(type.type)"
                />
              </dd>
            </div>
            <div class="px-2 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
              <dt class="text-sm font-medium leading-6">
                Action
              </dt>
              <dd class="mt-1 sm:col-span-2 sm:mt-0">
                <B24Button
                  :icon="SendIcon"
                  label="Send"
                  size="sm"
                  :disabled="!type.state.value"
                  @click="makeLog(type.type)"
                />
              </dd>
            </div>
          </dl>
        </div>
      </div>
    </div>
  </div>
</template>
