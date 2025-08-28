<script setup lang="ts">
import { ref } from 'vue'
import type { NuxtError } from '#app'

useHead({
  bodyAttrs: { class: 'edge-dark' }
})

const props = defineProps({
  // eslint-disable-next-line vue/require-default-prop
  error: Object as () => NuxtError
})

const errorBase = useError()
console.error(errorBase.value)

const errorData = ref({
  code: props.error?.statusCode ?? 400,
  title: props.error?.statusMessage ?? 'Error',
  description: (props.error?.data as any)?.description || errorBase?.value?.message || '',
  clearErrorIsShow: (props.error?.data as any)?.isShowClearError === true,
  clearErrorHref: (props.error?.data as any)?.clearErrorHref ?? '/',
  clearErrorTitle: (props.error?.data as any)?.clearErrorTitle ?? 'Clear errors',
  homePageIsHide: (props.error?.data as any)?.homePageIsHide === true,
  homePageHref: (props.error?.data as any)?.homePageHref ?? '/',
  homePageTitle: (props.error?.data as any)?.homePageTitle ?? 'Go back home'
})

const handleError = () => clearError({ redirect: errorData.value.clearErrorHref })
</script>

<template>
  <div class="flex flex-col">
    <div class="my-[24px] py-[24px] flex flex-col justify-center items-center h-[calc(100vh-60px)] backdrop-blur-sm bg-(--ui-color-design-outline-na-bg)">
      <ProseP small accent="less">
        [code: {{ errorData.code }}]
      </ProseP>
      <ProseH1>
        {{ errorData.title }}
      </ProseH1>
      <ProseP
        v-show="errorData.description"
        accent="default"
      >
        {{ errorData.description }}
      </ProseP>
      <div class="my-4 flex flex-col sm:flex-row items-center justify-center gap-[10px] ">
        <B24Button
          v-if="!errorData.homePageIsHide"
          color="air-primary"
          :to="errorData.homePageHref"
          :label="errorData.homePageTitle"
        />
        <B24Button
          v-if="errorData.clearErrorIsShow"
          color="air-secondary"
          :label="errorData.clearErrorTitle"
          @click="handleError"
        />
      </div>
    </div>
  </div>
</template>
