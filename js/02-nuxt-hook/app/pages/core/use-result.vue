<script setup lang="ts">
import { Result } from '@bitrix24/b24jssdk'
import { computed } from 'vue'

definePageMeta({
  pageTitle: 'Result',
  pageDescription: 'Example of working with the Result object'
})

const route = useRoute()
const pageTitle: ComputedRef<string> = computed(() => (route.meta?.pageTitle ?? '') as string)
const pageDescription: ComputedRef<string> = computed(() => (route.meta?.pageDescription ?? '') as string)
useSeoMeta({
  title: pageTitle.value,
  description: pageDescription.value
})

const { $logger } = useAppInit('Demo: Result')

// This function contains errors ////
function proc1(): Result {
  const result = new Result()
  // eslint-disable-next-line no-constant-condition
  if (1 > 0) {
    return result.addError(new Error('Some error 1'))
  }

  return result.setData({ someKey1: 'This data will not be used' })
}

// This function does not contain an error ////
function proc2(): Result {
  const result = new Result()
  // eslint-disable-next-line no-constant-condition
  if (1 > 2) {
    return result.addError(new Error('Some error 2'))
  }

  return result.setData({ someKey2: 'This data will be used' })
}

const result = new Result()
result.setData({
  someKey0: 'SomeData'
})

if (result.isSuccess) {
  // proc1 returns a non-successful response ////
  const response = proc1()
  if (!response.isSuccess) {
    // this code works ////
    result.addErrors([
      ...response.getErrors()
    ])
  } else {
    result.setData(Object.assign(
      result.getData(),
      response.getData()
    ))
  }
}

if (result.isSuccess) {
  // this code does not work ////
  const response = proc2()
  if (!response.isSuccess) {
    result.addErrors([
      ...response.getErrors()
    ])
  } else {
    result.setData(Object.assign(
      result.getData(),
      response.getData()
    ))
  }
}

if (!result.isSuccess) {
  // this code works ////
  $logger.error(result.getErrorMessages())
} else {
  $logger.log(result.getData())
}
</script>

<template>
  <AdviceCenter>
    <B24Advice
      class="w-full max-w-[550px]"
      :b24ui="{ descriptionWrapper: 'w-full' }"
      :avatar="{ src: '/avatar/assistant.png' }"
    >
      <ProseP>To view the result, open the developer console.</ProseP>
    </B24Advice>
  </AdviceCenter>
</template>
