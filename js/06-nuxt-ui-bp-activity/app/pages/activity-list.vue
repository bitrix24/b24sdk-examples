<script setup lang="ts">
import salesMarketingData from '~/data/sales-marketing'
import communicationData from '~/data/communication'
import type { IAction } from '~/types'
import { ModalLoader } from '#components'

import FileCheckIcon from '@bitrix24/b24icons-vue/main/FileCheckIcon'

definePageMeta({
  layout: 'header-panel',
  title: 'Activity list'
})

const toast = useToast()
const modal = useModal()

const tabs = ref([
  {
    label: 'Sales and Marketing',
    content: 'Content for Sales and Marketing.',
    value: 'tab-sale',
    items: [...salesMarketingData]
  },
  {
    label: 'Communication',
    content: 'Content for Communication.',
    value: 'tab-communication',
    items: [...communicationData]
  }
])

async function makeInstall(action: IAction): Promise<void> {
  console.log(action)

  return SleepAction()
    .then(() => {
      action.isInstall = true

      toast.add({
        title: 'Success!',
        description: `Installed activity ${action.caption}`,
        icon: action?.icon || FileCheckIcon,
        color: 'success'
      })
    })
}

const active = ref('tab-sale')

/**
 * Long process
 *
 * @return {Promise<void>}
 * @constructor
 */
async function SleepAction(): Promise<void> {
  modal.open(ModalLoader, {})

  return new Promise<void>(resolve => setTimeout(() => {
    modal.close()

    resolve()
  }, 1000))
}
</script>

<template>
  <div>
    <div class="pb-28 ">
      <B24Tabs
        v-model="active"
        :items="tabs"
        orientation="horizontal"
        class="px-4"
        :b24ui="{
          list: 'min-w-[200px]'
        }"
      >
        <template #content="{ item }">
          <div class="grid grid-cols-[repeat(auto-fill,minmax(266px,1fr))] gap-y-sm gap-x-sm">
            <template v-for="(subItem, subItemIndex) in item.items" :key="subItemIndex">
              <div
                class="bg-white dark:bg-white/10 py-sm2 px-xs2 cursor-pointer rounded-md flex flex-row gap-sm border-2 transition-shadow shadow hover:shadow-lg relative hover:border-primary"
                :class="[
                  subItem?.isInstall ? 'border-green-400 dark:border-green-600' : 'border-base-master/10 dark:border-base-100/20'
                ]"
              >
                <B24Avatar
                  v-if="subItem.icon"
                  :icon="subItem.icon"
                  size="xl"
                  class="border-2"
                  :class="[
                    subItem?.isInstall ? 'border-green-400' : 'border-blue-400',
                    item.value === 'tab-sale' ? 'pb-1' : '',
                    item.value === 'tab-communication' ? 'pr-1' : ''
                  ]"
                  :b24ui="{
                    root: subItem?.isInstall ? 'bg-green-150 dark:bg-green-400' : 'bg-blue-150 dark:bg-blue-400',
                    icon: subItem?.isInstall ? 'text-green-400 dark:text-green-900' : 'text-blue-400 dark:text-blue-900'
                  }"
                />
                <div class="max-w-11/12">
                  <div class="font-b24-secondary text-black dark:text-base-150 min-h-8 text-h6 leading-4 mb-xs font-semibold line-clamp-2">
                    {{ subItem.caption }}
                  </div>
                  <div class="font-b24-primary text-sm text-base-500 line-clamp-2">
                    <div>{{ subItem.description }}</div>
                  </div>
                  <div class="mt-2 flex flex-row gap-1 items-center justify-end">
                    <B24Button
                      v-if="!subItem.isInstall"
                      size="xs"
                      rounded
                      label="Install"
                      color="primary"
                      loading-auto
                      @click="async () => { return makeInstall(subItem) }"
                    />
                    <B24Badge
                      v-else
                      size="lg"
                      color="collab"
                      use-fill
                      label="Installed"
                    />
                  </div>
                </div>
              </div>
            </template>
          </div>
        </template>
      </B24Tabs>

      <footer class="fixed bottom-0 z-1 w-full bg-white dark:bg-base-master shadow-top-md p-2 pr-(--scrollbar-width)">
        <div class="relative flex items-center justify-center">
          <B24Button
            rounded
            label="Close"
            color="collab"
            to="/"
          />
        </div>
      </footer>
    </div>
  </div>
</template>
