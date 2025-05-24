<script setup lang="ts">
import { ref } from 'vue'
import { LoggerBrowser } from '@bitrix24/b24jssdk'
import Bitrix24Icon from '@bitrix24/b24icons-vue/common-service/Bitrix24Icon'

useHead({
  title: 'Bitrix24 UI - Login'
})

definePageMeta({
  middleware: ['auth']
})

const toast = useToast()
const config = useRuntimeConfig()

const $logger = LoggerBrowser.build('Auth.step1', true)
const b24Url = ref('https://b24-08ime0.bitrix24.by')

const generateAuthUrl = (
  origin: string,
  state: string
) => {
  const url = new URL(`${origin}/oauth/authorize/`)
  url.search = new URLSearchParams({
    client_id: config.public.appClientId,
    state
  }).toString()
  return url.toString()
}

async function goToB24() {
  let origin: string = ''

  try {
    const queryUrl = new URL(b24Url.value)
    origin = queryUrl.origin
  } catch (error) {
    $logger.error(error)
    toast.add({
      description: 'Wrong url',
      color: 'danger'
    })
    return
  }

  /**
   * @memo It is worth using your token received from the server part for greater reliability
   * But let's not complicate the example
   * @see app/middleware/auth.ts
   */
  const stateInfo = '123'
  const url = generateAuthUrl(origin, stateInfo)
  $logger.info('Go to:', url.toString())

  window.location.href = url.toString()
}
</script>

<template>
  <div class="flex flex-col items-center justify-center gap-1 h-screen">
    <div class="flex flex-wrap items-center gap-2">
      <B24ButtonGroup no-split>
        <B24Badge color="default" :icon="Bitrix24Icon" :b24ui="{ base: 'pr-1', leadingIcon: 'size-10', label: 'hidden' }" />
        <B24Input v-model="b24Url" class="w-[300px]" placeholder="https://demo.bitrix24.com" />
        <B24Button label="Go" color="collab" loading-auto use-clock @click.stop="goToB24" />
      </B24ButtonGroup>
    </div>
  </div>
</template>
