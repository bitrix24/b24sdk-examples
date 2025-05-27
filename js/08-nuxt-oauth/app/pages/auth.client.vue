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

const $logger = LoggerBrowser.build('Auth.step1', true)
const b24Url = ref('')

async function goToB24() {
  let authorizationServer: string = ''

  try {
    const queryUrl = new URL(b24Url.value)
    authorizationServer = queryUrl.origin
  } catch (error) {
    $logger.error(error)
    toast.add({
      description: 'Wrong url',
      color: 'danger'
    })
    return
  }

  navigateTo({
    path: '/auth/bitrix24',
    query: { authorizationServer }
  }, {
    external: true
  })
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
