<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { sleepAction } from '~/utils/sleep'
import Logo from '~/components/Logo.vue'

export interface IStep {
  action: () => Promise<void>
  caption?: string
  data?: Record<string, any>
}

useHead({
  title: 'Install'
})

// region Init ////
// const config = useRuntimeConfig()
// const appUrl = config.public.appUrl
/**
 * @memo get appUrl from url
 */
function getBaseUrl(): string {
  const currentUrl = window.location.href
  const lastSlashIndex = currentUrl.lastIndexOf('/')
  return currentUrl.substring(0, lastSlashIndex + 1)
}

const appUrl = getBaseUrl()
console.warn(appUrl)

const { processErrorGlobal } = useAppInit()
const { $initializeB24Frame } = useNuxtApp()
const $b24 = await $initializeB24Frame()

const confetti = useConfetti()

const isShowDebug = ref(false)

const progressColor = ref<string>('primary' as const)
const progressValue = ref<null | number>(null)
// endregion ////

// region Steps ////
const steps = ref<Record<string, IStep>>({
  init: {
    caption: 'Initialization',
    action: makeInit
  },
  placement: {
    caption: 'App Installation',
    action: async () => {
      /**
       * @memo remove this pause -> it`s only for demo
       */
      await sleepAction(2_000)

      /**
       * Registering placement
       */
      // await $b24.callBatch([
      //   {
      //     method: 'placement.unbind',
      //     params: {
      //       PLACEMENT: 'PAGE_BACKGROUND_WORKER'
      //     }
      //   },
      //   {
      //     method: 'placement.bind',
      //     params: {
      //       PLACEMENT: 'PAGE_BACKGROUND_WORKER',
      //       HANDLER: `${appUrl}/handler/background`,
      //       TITLE: 'Background worker',
      //       OPTIONS: {
      //         errorHandlerUrl: `${appUrl}/handler/background-some-problem`
      //       }
      //     }
      //   }
      // ])
    }
  },
  userFields: {
    caption: 'User Field Type Installation',
    action: async () => {
      const typeId = `type_demo_${import.meta.dev ? 'dev' : 'prod'}`
      await $b24.callBatch([
        {
          method: 'userfieldtype.delete',
          params: {
            USER_TYPE_ID: typeId
          }
        },
        {
          method: 'userfieldtype.add',
          params: {
            USER_TYPE_ID: typeId,
            HANDLER: `${appUrl}handler/uf.demo.html`,
            TITLE: `[${import.meta.dev ? 'dev' : 'prod'}] Type Demo`,
            DESCRIPTION: `Test userfield type for documentation with updated description`,
            OPTIONS: {
              height: 254
            }
          }
        }
      ], false)
    }
  },
  finish: {
    caption: 'Installation Complete',
    action: makeFinish
  }
})
const stepCode = ref<string>('init' as const)
// endregion ////

// region Actions ////
async function makeInit(): Promise<void> {
  await $b24.parent.setTitle('App: install')
  return sleepAction()
}

async function makeFinish(): Promise<void> {
  progressColor.value = 'success'
  progressValue.value = 100

  confetti.fire()
  await sleepAction(2000)

  await $b24.installFinish()
}

const stepsData = computed(() => {
  return Object.entries(steps.value).map(([index, row]) => {
    return {
      step: index,
      data: row?.data
    }
  })
})
// endregion ////

// region Lifecycle Hooks ////
onMounted(async () => {
  try {
    for (const [key, step] of Object.entries(steps.value)) {
      stepCode.value = key
      await step.action()
    }
  } catch (error: any) {
    processErrorGlobal(error, {
      homePageIsHide: true,
      isShowClearError: false
    })
  }
})
// endregion ////
</script>

<template>
  <div class="mx-3 flex flex-col items-center justify-center gap-1 h-dvh">
    <Logo
      class="size-52"
      :class="[
        stepCode === 'finish' ? 'text-green-500' : 'text-base-500'
      ]"
    />
    <B24Progress v-model="progressValue" size="xs" animation="elastic" color="primary" class="w-1/2 sm:w-1/3" />
    <div class="mt-6 flex flex-col items-center justify-center gap-2">
      <div class="text-h1 text-nowrap">
        Installation
      </div>
      <div class="text-base-500">
        {{ steps[stepCode]?.caption || '...' }}
      </div>
    </div>

    <ProsePre v-if="isShowDebug">
      {{ stepsData }}
    </ProsePre>
  </div>
</template>
