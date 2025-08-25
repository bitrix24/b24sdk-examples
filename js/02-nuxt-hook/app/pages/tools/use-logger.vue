<script setup lang="ts">
import { ref, computed, type Ref, type ComputedRef } from 'vue'
import { LoggerType } from '@bitrix24/b24jssdk'
import TrashBinIcon from '@bitrix24/b24icons-vue/main/TrashBinIcon'
import SendIcon from '@bitrix24/b24icons-vue/main/SendIcon'

definePageMeta({
  pageTitle: 'LoggerBrowser',
  pageDescription: 'Example of working with the LoggerBrowser object.'
})

const { $logger } = useAppInit('Demo: Logger')
const debugMessage: Ref<string> = ref('This is a test message for debugging.')

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
    const loggerType = LoggerType[key as 'desktop' | 'log' | 'info' | 'warn' | 'error' | 'trace']
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
  <div>
    <AdviceBanner>
      <B24Advice
        class=" w-full max-w-[550px]"
        :b24ui="{ descriptionWrapper: 'w-full' }"
        :avatar="{ src: '/avatar/assistant.png' }"
      >
        To view the result, open the developer console.
      </B24Advice>
    </AdviceBanner>

    <FormWrapper class="mb-[15px]">
      <B24Textarea
        v-model="debugMessage"
        name="debugMessage"
        placeholder="Enter a message for the log"
      />
      <div class="flex flex-col lg:flex-row gap-2">
        <B24Button
          color="air-primary"
          :icon="SendIcon"
          label="Send log"
          @click="makeLoggAll"
        />
        <B24Button
          color="air-tertiary-no-accent"
          :icon="TrashBinIcon"
          label="Clear console"
          @click="clearConsole"
        />
      </div>
    </FormWrapper>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
      <div
        v-for="(type) in loggerTypeList"
        :key="type.index"
        class="px-lg2 py-sm2 rounded-md col-auto md:col-span-2 lg:col-span-1 style-blurred-bg"
      >
        <div>
          <ProseH3>
            {{ type.index }}
          </ProseH3>
          <ProseP small accent="less">
            {{ type.description }}
          </ProseP>
        </div>
        <div class="mt-4">
          <dl class="divide-y divide-(--ui-color-divider-vibrant-accent-more)">
            <div class="py-2 grid grid-cols-3 gap-4 px-0 items-center">
              <dt>
                <ProseP small accent="less-more">
                  State
                </ProseP>
              </dt>
              <dd class="mt-0 col-span-2">
                <B24Switch
                  v-model="type.state.value"
                  @change="toggleLogger(type.type)"
                />
              </dd>
            </div>
            <div class="py-2 grid grid-cols-3 gap-4 px-0 items-center">
              <dt>
                <ProseP small accent="less-more">
                  Action
                </ProseP>
              </dt>
              <dd class="mt-0 col-span-2">
                <B24Button
                  :icon="SendIcon"
                  label="Send"
                  color="air-secondary"
                  size="xs"
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
