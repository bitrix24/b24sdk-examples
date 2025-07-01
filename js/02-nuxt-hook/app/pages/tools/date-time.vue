<script setup lang="ts">
import { DateTime } from 'luxon'
import { Text } from '@bitrix24/b24jssdk'

useHead({
  title: 'DateTime'
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
    <B24Alert color="warning">
      <template #description>
        Using the <ProseA href="https://moment.github.io/luxon/" target="_blank">
          <ProseCode>Luxon</ProseCode>
        </ProseA> library
      </template>
    </B24Alert>
    <div class="mt-xl text-md text-base-900 border border-base-30 rounded-md px-lg2 py-sm2 shadow-sm bg-white dark:bg-base-200/10">
      <dl class="divide-y divide-base-300">
        <div
          v-for="(caseData, caseIndex) in listCases"
          :key="caseIndex"
          class="px-2 py-1 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0"
        >
          <dt>
            <ProseH6>
              {{ caseData.caption }}
            </ProseH6>
          </dt>
          <dd>
            <ProseCode v-if="caseData.data instanceof DateTime">
              {{ caseData.data.isValid ? caseData.data : caseData.data.invalidReason }}
            </ProseCode>
            <ProseCode v-else>
              {{ caseData.data }}
            </ProseCode>
          </dd>
        </div>
      </dl>
    </div>
  </div>
</template>
