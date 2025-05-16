<script setup lang="ts">
import { ref } from 'vue'
import type { RadioGroupItem } from '@bitrix24/b24ui-nuxt'
import { sleepAction } from '~/utils/sleep'
import { useUserSettingsStore } from '~/stores/userSettings'
import { storeToRefs } from 'pinia'
import SavePromptIcon from '@bitrix24/b24icons-vue/main/SavePromptIcon'
import Clock2Icon from '@bitrix24/b24icons-vue/main/Clock2Icon'
import LoaderClockIcon from '@bitrix24/b24icons-vue/animated/LoaderClockIcon'
import ChevronDownIcon from '@bitrix24/b24icons-vue/actions/ChevronDownIcon'

useHead({
  bodyAttrs: {
    class: 'slider-bg min-h-screen'
  }
})

// region Init ////
const { $logger, initApp, processErrorGlobal } = useAppInit('Slider-Pomodoro')
const { $initializeB24Frame } = useNuxtApp()
const $b24 = await $initializeB24Frame()

$logger.info('Hi from slider')

const toast = useToast()

const isLoading = ref(true)

const breakOptions = ref<RadioGroupItem[]>([
  { label: '5 min', data: 5 },
  { label: '10 min', data: 10 },
  { label: '15 min', data: 15 }
])

const workOptions = ref<RadioGroupItem[]>([
  { label: '25 min', data: 25 },
  { label: '50 min', data: 50 },
  { label: '90 min', data: 90 }
])

const userSettings = useUserSettingsStore()
const { isFocusSessionStarted, shortBreak, longBreak, workDuration, repeats } = storeToRefs(userSettings)
const secondsSimple = 60
// endregion ////

// region Actions ////
async function startFocusSession() {
  isFocusSessionStarted.value = true
  await userSettings.saveSettings()

  toast.add({
    title: 'Focus Session Started 🚀',
    description: `workDuration: ${workDuration.value} shortBreak: ${shortBreak.value}`,
    icon: SavePromptIcon,
    color: 'success'
  })

  // await sleepAction(2_000)
  // await closeSlider()
}

async function stopFocusSession() {
  isFocusSessionStarted.value = false
  await userSettings.saveSettings()

  toast.add({
    title: 'Focus Session Stopped',
    icon: SavePromptIcon,
    color: 'danger'
  })
}

async function saveSettings() {
  await userSettings.saveSettings()

  toast.add({
    title: 'Settings saved',
    icon: SavePromptIcon,
    color: 'success'
  })
}

async function closeSlider() {
  $logger.log('Slider closed — let\'s go!')
  await $b24.parent.closeApplication()
}
// endregion ////

// region Lifecycle Hooks ////
onMounted(async () => {
  try {
    isLoading.value = true

    await initApp($b24)

    isLoading.value = false
  } catch (error) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: false,
      clearErrorHref: '/activity-list'
    })
  }
})
// endregion ////
</script>

<template>
  <div class="w-full py-10 px-4 gap-4 flex flex-col items-center justify-start">
    <template v-if="isLoading">
      <div class="my-8 max-lg:ps-3">
        <div class="relative bg-white dark:bg-white/10 p-sm2 rounded-md flex flex-col gap-sm border-2 transition-shadow shadow border-base-master/10 dark:border-base-100/20">
          <B24Skeleton class="h-5xl min-w-[110px] max-w-[200px] rounded-full" />
          <B24Skeleton class="h-5xl min-w-[110px] max-w-[400px] rounded-full" />
          <B24Skeleton class="h-5xl min-w-[110px] max-w-[300px] rounded-full" />
        </div>
      </div>
    </template>
    <template v-else>
      <div class="w-full flex-1 flex flex-col items-center gap-4">
        <!-- Title -->
        <ProseH1 class="text-center mb-0">
          Focus Session
        </ProseH1>

        <!-- Icon + Timer -->
        <div class="w-full flex flex-row items-center justify-center gap-3">
            <B24Countdown
              v-if="isFocusSessionStarted"
              :seconds="secondsSimple"
              :icon="LoaderClockIcon"
              class="justify-end items-end"
              :b24ui="{
                leadingIcon: 'size-5 text-base-900',
                label: 'text-8xl text-base-900',
              }"
              @end="stopFocusSession"
            />
        </div>
        <B24Button
          v-if="!isFocusSessionStarted"
          color="primary"
          depth="dark"
          label="Start"
          loading-auto
          @click.stop="startFocusSession"
        />
        <B24Button
          v-else
          color="danger"
          label="Stop"
          loading-auto
          @click.stop="stopFocusSession"
        />
      </div>

      <B24Collapsible class="w-full flex-1">
        <B24Button
          class="group"
          label="Settings"
          color="link"
          normal-case
          :icon="ChevronDownIcon"
          :b24ui="{
            leadingIcon: 'group-data-[state=open]:rotate-180 transition-transform duration-200'
          }"
        />

        <template #content>
          <!-- Form Section -->
          <form
            class="space-y-4 flex flex-col bg-white shadow-md rounded-xl p-6"
            @submit.prevent=""
          >
            <B24FormField label="Long Break">
              <B24RadioGroup
                v-model="longBreak"
                :items="breakOptions"
                orientation="horizontal"
                variant="list"
                value-key="data"
              />
            </B24FormField>
            <B24FormField label="Work Session">
              <B24RadioGroup
                v-model="workDuration"
                :items="workOptions"
                orientation="horizontal"
                variant="list"
                value-key="data"
              />
            </B24FormField>
            <B24FormField label="Number of Repeats">
              <B24InputNumber v-model="repeats" :min="1" :max="10" />
            </B24FormField>
            <div class="pt-4 flex justify-center">
              <B24Button
                color="success"
                class="mx-auto"
                label="Save"
                loading-auto
                @click="saveSettings"
              />
            </div>
          </form>
        </template>
      </B24Collapsible>
    </template>
  </div>
</template>
