<script setup lang="ts">
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import * as z from 'zod'
import type { FormSubmitEvent } from '@bitrix24/b24ui-nuxt'
import Settings1Icon from '@bitrix24/b24icons-vue/main/SettingsIcon'

const { t } = useI18n()

definePageMeta({
  layout: 'slider'
})
useHead({
  title: t('page.forIntegrators.seo.title')
})
const toast = useToast()

// region Init ////
const isShowDebug = ref(true)
const isLoading = ref(true)

const DEFAULT_VALUES = {
  company: 'MyCompanyName',
  site: 'https://demo.com',
  comments: 'Some comments'
} as const

const PHONE_REGEX = /^\+?\d{1,4}?[-.\s]?\(?\d{1,3}?\)?[-.\s]?\d{1,4}[-.\s]?\d{1,4}[-.\s]?\d{1,9}$/
// eslint-disable-next-line regexp/no-misleading-capturing-group,regexp/no-super-linear-backtracking
const URL_REGEX = /^(https?:\/\/)?([\da-z.-]+)\.([a-z.]{2,6})([/\w .-]*)*$/
const WHATSAPP_REGEX = /^https:\/\/(wa\.me|api\.whatsapp\.com)\/\+\d{5,15}$/
const TELEGRAM_REGEX = /^https:\/\/t\.me\/\w{5,32}$/

const schema = z.object({
  logo: z.string().optional(),
  company: z.string()
    .min(2, 'Company name must be at least 2 characters')
    .max(100, 'Company name too long (max 100 characters)'),

  phone: z.string()
    .regex(PHONE_REGEX, 'Invalid phone number format')
    .max(20, 'Phone number too long')
    .optional(),

  email: z.string()
    .email('Invalid email address')
    .max(60, 'Email too long')
    .optional(),

  whatsapp: z.string()
    .regex(WHATSAPP_REGEX, 'Use valid WhatsApp URL (https://wa.me/123456789)')
    .max(60, 'URL too long')
    .optional(),

  telegram: z.string()
    .regex(TELEGRAM_REGEX, 'Use valid Telegram URL (https://t.me/username)')
    .max(60, 'URL too long')
    .optional(),

  site: z.string()
    .regex(URL_REGEX, 'Invalid website URL')
    .max(60, 'URL too long')
    .refine(val => val.startsWith('http://') || val.startsWith('https://'), {
      message: 'URL must start with http:// or https://'
    })
    .optional(),

  comments: z.string()
    .max(2000, 'Comments too long (max 2000 characters)')
    .optional()
})

type Schema = z.input<typeof schema>
// endregion ////

// region Data ////

const state = reactive<Partial<Schema>>(initializeState())

function initializeState(): Partial<Schema> {
  return {
    ...DEFAULT_VALUES,
    logo: undefined,
    phone: undefined,
    email: undefined,
    whatsapp: undefined,
    telegram: undefined
  }
}

async function loadData(): Promise<void> {
  try {
    // @todo get real info
    Object.assign(state, DEFAULT_VALUES)
  } finally {
    isLoading.value = false
  }
}

async function clearData(): Promise<void> {
  Object.assign(state, initializeState())
}
// endregion ////

// region Form Actions ////
async function onSubmit(event: FormSubmitEvent<any>): Promise<void> {
  try {
    // todo make real call api
    console.log('Form submitted:', event.data, state)

    toast.add({
      title: 'Success',
      description: 'The form has been submitted.',
      color: 'success'
    })
  } catch (error) {
    handleError(error, 'Failed to submit')
  }
}
// endregion ////

// region Lifecycle Hooks ////
onMounted(async () => {
  try {
    await loadData()
  } catch (error) {
    handleError(error, 'Failed to load data')
    // showError({ ... }) // @todo unComment
  }
})

onUnmounted(() => {
  // $b24?.destroy()
})
// endregion ////

// region Tools ////
function handleError(error: unknown, context: string): void {
  console.error(context, error)
  toast.add({
    title: 'Error',
    description: (error as Error)?.message || 'Unknown error occurred',
    color: 'danger'
  })
}
// endregion ////
</script>

<template>
  <div class="px-4 bg-white py-4">
    <ActivityListSkeleton v-if="isLoading" />
    <template v-else>
      <!-- Header Section -->
      <header class="mb-6 bg-blue-100 border border-blue-200 p-6 rounded-lg">
        <div class="flex flex-row flex-nowrap items-center justify-start gap-4 mb-4">
          <B24Avatar
            size="lg"
            :b24ui="{
              icon: 'p-1'
            }"
            :icon="Settings1Icon"
          />
          <ProseH2 class="mb-0">
            Company Settings
          </ProseH2>
        </div>

        <div class="space-y-2 text-base-600">
          <ProseP>Manage your company integration settings.</ProseP>
          <ProseP>All changes will be applied immediately.</ProseP>
        </div>
      </header>

      <!-- Preview Section -->
      <section class="mb-8 p-6 bg-base-50 rounded-lg">
        <ProseH2 class="mb-4">
          Preview
        </ProseH2>
        <div class="space-y-2">
          <div v-if="state.logo" class="flex items-center gap-2">
            <span class="font-medium">Logo:</span>
            <img :src="state.logo" alt="Company Logo" class="h-8">
          </div>
          <PreviewField label="Company" :value="state.company" />
          <PreviewField label="Phone" :value="state.phone" />
          <PreviewField label="Email" :value="state.email" />
          <PreviewField label="WhatsApp" :value="state.whatsapp" />
          <PreviewField label="Telegram" :value="state.telegram" />
          <PreviewField label="Website" :value="state.site" />
          <PreviewField label="Comments" :value="state.comments" />
        </div>
      </section>
      <!-- Form Section -->
      <section>
        <B24Form
          ref="form"
          :state="state"
          :schema="schema"
          class="space-y-6"
          @submit="onSubmit"
        >
          <B24FormField label="Logo" name="logo">
            <B24Input
              v-model="state.logo2"
              placeholder="Upload logo"
              class="w-full"
            />
            <ImageUploader
              v-model="state.logo"
              placeholder="Upload logo"
              class="w-full"
            />
          </B24FormField>

          <B24FormField
            label="Company Name"
            name="company"
            required
            :hint="`${state.company.length}/100`"
          >
            <B24Input
              v-model="state.company"
              placeholder="Enter company name"
              class="w-full"
            />
          </B24FormField>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <B24FormField label="Phone" name="phone">
              <B24Input
                v-model="state.phone"
                type="tel"
                placeholder="+1234567890"
                class="w-full"
              />
            </B24FormField>

            <B24FormField label="Email" name="email">
              <B24Input
                v-model="state.email"
                type="email"
                placeholder="contact@example.com"
                class="w-full"
              />
            </B24FormField>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <B24FormField label="WhatsApp" name="whatsapp">
              <B24Input
                v-model="state.whatsapp"
                placeholder="https://wa.me/..."
                class="w-full"
              />
            </B24FormField>

            <B24FormField label="Telegram" name="telegram">
              <B24Input
                v-model="state.telegram"
                placeholder="https://t.me/..."
                class="w-full"
              />
            </B24FormField>
          </div>

          <B24FormField label="Website" name="site">
            <B24Input
              v-model="state.site"
              placeholder="https://example.com"
              class="w-full"
            />
          </B24FormField>

          <B24FormField
            label="Comments"
            name="comments"
            :hint="`${state.comments.length}/2000`"
          >
            <B24Textarea
              v-model="state.comments"
              placeholder="Additional information..."
              class="w-full"
              rows="4"
            />
          </B24FormField>

          <B24Separator class="my-6" />

          <div class="flex justify-between items-center">
            <div class="flex gap-2">
              <B24Button
                type="submit"
                label="Save Changes"
                color="success"
              />
              <B24Button
                label="Reset"
                color="secondary"
                @click="clearData"
              />
            </div>

            <B24Button
              label="Debug"
              color="link"
              @click="isShowDebug = !isShowDebug"
            />
          </div>
        </B24Form>
      </section>
      <ProsePre v-if="isShowDebug" class="mt-8">
        {{ state }}
      </ProsePre>
    </template>
  </div>
</template>
