<script setup lang="ts">
/**
 * @todo fix lang
 */
import { computed } from 'vue'
import IncertImageIcon from '@bitrix24/b24icons-vue/editor/IncertImageIcon'

// const { t } = useI18n()
const appSettings = useAppSettingsStore()
const state = computed(() => {
  return appSettings.integrator
})

const itemsPreview = computed(() => {
  const result = []

  if (state.value.phone.length) {
    result.push({
      label: 'Phone',
      code: 'phone',
      description: state.value.phone
    })
  }

  if (state.value.phone.length) {
    result.push({
      label: 'E-mail',
      code: 'email',
      description: state.value.email
    })
  }

  if (state.value.telegram.length) {
    result.push({
      label: 'Telegram',
      code: 'telegram',
      description: state.value.telegram
    })
  }

  if (state.value.whatsapp.length) {
    result.push({
      label: 'WhatsApp',
      code: 'whatsapp',
      description: state.value.whatsapp
    })
  }

  if (state.value.site.length) {
    result.push({
      label: 'Website',
      code: 'site',
      description: state.value.site
    })
  }

  if (state.value.comments.length) {
    result.push({
      label: 'Comments',
      code: 'comments',
      description: state.value.comments
    })
  }

  return result
})
</script>

<template>
  <B24Popover
    :arrow="{ width: 20, height: 10 }"
    :b24ui="{ content: 'w-72 max-h-96 overflow-y-auto' }"
  >
    <div class="mx-xs py-xs px-xs hover:bg-base-200 dark:hover:bg-base-dark rounded-md flex items-center justify-center">
      <B24Button
        class="w-full ps-0 pe-0"
        label="Open"
        color="link"
        use-dropdown
        normal-case
        :b24ui="{ baseLine: 'w-full shrink-0' }"
      >
        <div class="flex flex-row items-center justify-start gap-2 w-full shrink-1">
          <div class="shrink-0">
            <img
              v-if="state.logo"
              :src="state.logo"
              alt="Company Logo"
              class="rounded-md border border-base-200 dark:border-base-700 w-[40px] min-h-[40px] object-cover"
            >
            <IncertImageIcon
              v-else
              class="rounded-md border text-base-200 dark:text-base-800 border-base-200 dark:border-base-700 size-10 object-cover"
            />
          </div>
          <div class="min-w-0 hidden md:inline">
            <div class="flex flex-col items-start justify-start gap-0.5 overflow-hidden">
              <div class="text-3xs leading-tight opacity-70 text-base-500">
                {{ $t('component.integrator.info.caption') }}
              </div>
              <div class="text-2xs leading-tight text-start text-base-700 line-clamp-1">
                {{ state.company || $t('component.integrator.info.empty') }}
              </div>
            </div>
          </div>
        </div>
      </B24Button>
    </div>

    <template #content>
      <div class="bg-white dark:bg-base-dark shadow-lg rounded-2xs ring ring-base-300 dark:ring-base-800">
        <B24DescriptionList
          size="sm"
          class="px-3 rounded-lg overflow-hidden"
          :items="itemsPreview"
          :b24ui="{ container: 'mt-0 sm:grid-cols-[min(35%,5rem)_auto]' }"
        >
          <template #description="{ item }">
            <span class="block break-words">
              <template v-if="item.code === 'phone'">
                <ProseA :href="`tel:${(item?.description || '').replace(/[^\d+]/g, '')}`">
                  {{ item.description }}
                </ProseA>
              </template>
              <template v-else-if="item.code === 'email'">
                <ProseA :href="`mailto:${item.description}`">
                  {{ item.description }}
                </ProseA>
              </template>
              <template v-else-if="item.code !== 'comments'">
                <ProseA :href="item.description">
                  {{ item.description }}
                </ProseA>
              </template>
              <template v-else>
                {{ item.description }}
              </template>
            </span>
          </template>
        </B24DescriptionList>
      </div>
    </template>
  </B24Popover>
</template>
