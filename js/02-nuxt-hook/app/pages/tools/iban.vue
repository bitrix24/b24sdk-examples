<script setup lang="ts">
import { useFormatter } from '@bitrix24/b24jssdk'

definePageMeta({
  pageTitle: 'IBAN',
  pageDescription: 'IBAN formatting demonstration.'
})

const { formatterIban } = useFormatter()

const listCases = ref([
  'IE29AIBK93115212345678',
  'ES9121000418450200051332',
  'GB29NWBK60161331926819',
  'PL61109010140000071219812874',
  'FR1420041010050500013M02606',
  'IT60X0542811101000000123456',
  'PT50000201231234567890154',
  'NL91ABNA0417164300',
  'IR710570029971601460641001',
  'AZ77VTBA00000000001234567890',
  'GE60NB0000000123456789',
  'JO71CBJO0000000000001234567890',
  'BY09ALFA30132120160130270000'
])
</script>

<template>
  <div>
    <AdviceBanner>
      <B24Advice
        class=" w-full max-w-[550px]"
        :b24ui="{ descriptionWrapper: 'w-full' }"
        :avatar="{ src: '/avatar/assistant.png' }"
      >
        Hm..
      </B24Advice>
    </AdviceBanner>
    <dl class="divide-y divide-(--ui-color-divider-vibrant-accent-more)">
      <div
        v-for="(caseData, caseIndex) in listCases"
        :key="caseIndex"
        class="px-2 py-1 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0 sm:items-center"
      >
        <dt>
          <ProseH6 class="mb-0">
            {{ caseData }}
          </ProseH6>
        </dt>
        <dd class="">
          <ProseCode class="mb-0 text-nowrap">
            {{ formatterIban.printFormat(caseData, ' ') }}
          </ProseCode>
        </dd>
      </div>
    </dl>
  </div>
</template>
