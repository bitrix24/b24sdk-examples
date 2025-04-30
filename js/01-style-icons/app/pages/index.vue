<script setup lang="ts">
import { type Ref, ref } from 'vue'
import type { DropdownMenuItem } from '@bitrix24/b24ui-nuxt'
import { sleepAction } from '~/utils/sleep'
import { ModalLoader } from '#components'
import WheelIcon from '@bitrix24/b24icons-vue/common-service/WheelIcon'
import CreditDebitCardIcon from '@bitrix24/b24icons-vue/main/CreditDebitCardIcon'
import PulseCircleIcon from '@bitrix24/b24icons-vue/main/PulseCircleIcon'
import ChevronDownIcon from '@bitrix24/b24icons-vue/actions/ChevronDownIcon'
import Search2Icon from '@bitrix24/b24icons-vue/main/Search2Icon'
import CheckIcon from '@bitrix24/b24icons-vue/main/CheckIcon'

definePageMeta({ layout: false })

useHead({ title: 'Local app' })

const isLoading = ref(true)
const toast = useToast()
const overlay = useOverlay()
const modalLoader = overlay.create(ModalLoader)

const someActions = ref<DropdownMenuItem[]>([
  {
    label: 'Billing',
    icon: CreditDebitCardIcon,
    onSelect() {
      toast.add({ title: '[Billing].click' })
    }
  },
  {
    label: 'Support',
    icon: PulseCircleIcon,
    to: 'https://bitrix24.github.io/b24ui/',
    target: '_blank'
  }
])

// region Action ////
const makeCreate = async (): Promise<void> => {
  await sleepAction(500)
  toast.add({ title: '[Create].click' })
  return
}

const makeSearch = async (): Promise<void> => {
  await sleepAction(500)
  toast.add({ title: '[Search].click' })
  return
}

const makeSave = async (): Promise<void> => {
  modalLoader.open()
  await sleepAction(1000)
  modalLoader.close()

  toast.add({ title: '[Save].click' })
  return
}

const makeApply = async (): Promise<void> => {
  await sleepAction(1000)
  toast.add({ title: '[Apply].click' })
  return
}

const makeClosePage = async (): Promise<void> => {
  toast.add({ title: '[Cancel].click' })
}
// endregion ////

interface Item {
  code: string
  icon: string
  title: string
  description: string
  isActive: boolean
}

interface Group {
  code: string
  title: string
  items: Item[]
}

function getElements(
  groupIndex: number,
  array: Item[],
  count: number
): Item[] {
  return JSON.parse(JSON.stringify(array)).slice(0, count).map((item: Item) => {
    item.code = `group-${groupIndex}-${item.code}`
    return item
  })
}

const listAllItems: Item[] = [
  {
    code: 'item-1',
    icon: 'contacts',
    title: 'Irure duis magna in sunt',
    description: 'Nisi nostrud excepteur exercitation irure dolore enim reprehenderit commodo',
    isActive: false
  },
  {
    code: 'item-2',
    icon: 'contacts',
    title: 'Cupidatat ut sit ea eu',
    description: 'Lorem ipsum dolor sit amet sed labore fugiat ut excepteur amet incididunt',
    isActive: false
  },
  {
    code: 'item-3',
    icon: 'contacts',
    title: 'Consequat aliquip velit anim non',
    description: 'Reprehenderit fugiat ut laboris consequat mollit. Est aute dolore aliqua ut lorem eu aliqua',
    isActive: true
  },
  {
    code: 'item-4',
    icon: 'contacts',
    title: 'Ut ut veniam id dolor',
    description: 'Nostrud deserunt sed sed sed. Aliqua mollit laboris anim laborum mollit',
    isActive: false
  },
  {
    code: 'item-5',
    icon: 'contacts',
    title: 'Tempor veniam elit nostrud voluptate',
    description: 'Do culpa lorem consequat ullamco. Duis sunt ut proident eiusmod incididunt tempor enim id officia voluptate ea',
    isActive: true
  }
]

let groupIndex = 0
const groups: Ref<Group[]> = ref([
  {
    code: `group-${++groupIndex}`,
    title: 'Lorem ipsum dolor sit amet in anim dolor nulla excepteur elit eu.',
    items: getElements(groupIndex, listAllItems, Math.min(4, listAllItems.length))
  } as Group,
  {
    code: `group-${++groupIndex}`,
    title: 'Lorem ipsum dolor sit amet labore',
    items: getElements(groupIndex, listAllItems, Math.min(5, listAllItems.length))
  } as Group,
  {
    code: `group-${++groupIndex}`,
    title: 'Commodo aliquip reprehenderit adipiscing',
    items: getElements(groupIndex, listAllItems, Math.min(2, listAllItems.length))
  } as Group,
  {
    code: `group-${++groupIndex}`,
    title: 'Occaecat laboris laborum ut',
    items: getElements(groupIndex, listAllItems, Math.min(4, listAllItems.length))
  } as Group,
  {
    code: `group-${++groupIndex}`,
    title: 'Commodo aliquip laboris laborum ut',
    items: getElements(groupIndex, listAllItems, Math.min(3, listAllItems.length))
  } as Group,
  {
    code: `group-${++groupIndex}`,
    title: 'Ipsum dolor',
    items: getElements(groupIndex, listAllItems, Math.min(1, listAllItems.length))
  } as Group
])
</script>

<template>
  <NuxtLayout name="dashboard">
    <template #header-title>
      Condense the page title for clarity.
    </template>

    <template #header-navbar>
      <B24NavbarSection class="max-lg:hidden">
        <B24DropdownMenu :items="someActions">
          <B24Button
            color="link"
            size="sm"
            normal-case
            :icon="WheelIcon"
            :b24ui="{
              base: 'font-light',
              leadingIcon: 'text-info-text'
            }"
          >
            <div class="flex flex-col items-start justify-center">
              <div class="text-nowrap text-3xs leading-tight opacity-70 text-base-500 items-center">
                use case
              </div>
              <div class="text-nowrap text-sm text-primary-link transition-opacity hover:opacity-80 border-b border-dashed border-b-primary-link">
                Some action
              </div>
            </div>
          </B24Button>
        </B24DropdownMenu>
      </B24NavbarSection>
      <B24NavbarSection>
        <B24ButtonGroup>
          <B24Button
            color="success"
            label="Create"
            use-clock
            loading-auto
            @click="makeCreate"
          />
          <B24DropdownMenu
            :items="someActions"
            :content="{
              align: 'end',
              side: 'bottom',
              sideOffset: 8
            }"
          >
            <B24Button color="success" :icon="ChevronDownIcon" />
          </B24DropdownMenu>
        </B24ButtonGroup>
        <B24ButtonGroup no-split>
          <B24Input
            class="max-lg:40 lg:w-60"
            name="search"
            placeholder="Search&hellip;"
            aria-label="Search"
            type="search"
          />
          <B24Button
            :icon="Search2Icon"
            color="primary"
            loading-auto
            use-clock
            @click="makeSearch"
          />
        </B24ButtonGroup>
      </B24NavbarSection>
    </template>

    <div
      v-for="(group) in groups"
      :key="group.code"
      class="mb-md"
    >
      <div class="mb-sm font-b24-secondary text-h4 font-light leading-8 text-base-900">
        {{ group.title }}
      </div>
      <div class="grid grid-cols-[repeat(auto-fill,minmax(266px,1fr))] gap-y-sm gap-x-xs">
        <div
          v-for="(item) in group.items"
          :key="item.code"
          class="bg-white py-sm2 px-xs2 cursor-pointer rounded-md flex flex-row gap-sm border-2 transition-shadow shadow hover:shadow-lg relative"
          :class="item.isActive ? 'border-success-text' : 'border-white hover:border-primary'"
        >
          <div
            v-if="item.isActive"
            class="absolute -top-2 -right-2 rounded-full bg-success-text size-5 text-success-on flex items-center justify-center"
          >
            <CheckIcon class="size-md" />
          </div>
          <div class="rounded-full bg-blue-200 size-14 min-w-14 min-h-14 flex items-center justify-center">
            <component
              :is="WheelIcon"
              class="size-12 text-info-text"
            />
          </div>
          <div class="max-w-11/12">
            <div
              class="font-b24-secondary text-black text-h6 leading-4 mb-xs font-semibold line-clamp-2"
              :title="item.title"
            >
              {{ item.title }}
            </div>
            <div
              class="font-b24-primary text-sm text-base-500 line-clamp-2"
              :title="item.description"
            >
              <div>{{ item.description }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <template #footer-navbar-center>
      <B24Button
        color="success"
        label="Save"
        use-clock
        loading-auto
        @click="makeSave"
      />
      <B24Button
        color="primary"
        label="Apply"
        use-clock
        loading-auto
        @click="makeApply"
      />
      <B24Button
        color="link"
        label="Cancel"
        use-clock
        loading-auto
        @click="makeClosePage"
      />
    </template>
    <template #footer-navbar-right>
      <div class="text-base-400 text-xs">
        Updated 8 minutes ago
      </div>
    </template>
  </NuxtLayout>
</template>
