<script setup lang="ts">
import { DateTime } from 'luxon'
import { Text } from '@bitrix24/b24jssdk'

definePageMeta({
  pageTitle: 'DateTime',
  pageDescription: 'A couple of examples of date and time processing.'
})

type TypeCase = {
  caption: string
  data: string | DateTime
}

const listCases: TypeCase[] = [
  {
    caption: 'Text.getDateForLog()',
    data: Text.getDateForLog()
  },
  {
    caption: 'Text.toDateTime(\'2012-04-12\', \'y-MM-dd\')',
    data: Text.toDateTime('2012-04-12', 'y-MM-dd')
  },
  {
    caption: 'Text.toDateTime(\'12.04.2012\', \'dd.MM.y\')',
    data: Text.toDateTime('12.04.2012', 'dd.MM.y')
  },
  {
    caption: 'Text.toDateTime(\'2012-04-12 14:05:56\', \'y-MM-dd HH:mm:ss\')',
    data: Text.toDateTime('2012-04-12 14:05:56', 'y-MM-dd HH:mm:ss')
  },
  {
    caption: 'Text.toDateTime(\'2012-04-12T14:05:56+03:00\')',
    data: Text.toDateTime('2012-04-12T14:05:56+03:00')
  }
]
</script>

<template>
  <div>
    <AdviceBanner>
      <B24Advice
        class="w-full max-w-[550px]"
        :b24ui="{ descriptionWrapper: 'w-full' }"
        :avatar="{ src: '/avatar/assistant.png' }"
      >
        Using the <ProseA href="https://moment.github.io/luxon/" target="_blank">
          <ProseCode color="air-primary-copilot">
            Luxon
          </ProseCode>
        </ProseA> library
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
            {{ caseData.caption }}
          </ProseH6>
        </dt>
        <dd>
          <ProseCode v-if="caseData.data instanceof DateTime" class="mb-0 text-nowrap">
            {{ caseData.data.isValid ? caseData.data : caseData.data.invalidReason }}
          </ProseCode>
          <ProseCode v-else>
            {{ caseData.data }}
          </ProseCode>
        </dd>
      </div>
    </dl>
  </div>
</template>
