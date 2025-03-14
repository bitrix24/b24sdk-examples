<script setup lang="ts">
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import * as z from 'zod'
import IncertImageIcon from '@bitrix24/b24icons-vue/editor/IncertImageIcon'
import ChevronToTheLeftIcon from '@bitrix24/b24icons-vue/actions/ChevronToTheLeftIcon'

const { t } = useI18n()

const emit = defineEmits<{ close: [boolean] }>()

definePageMeta({
  layout: 'slider'
})
useHead({
  title: t('page.forIntegrators.seo.title')
})
const toast = useToast()

// region Init ////
const isLoading = ref(true)

const schema = z.object({
  logo: z.string().optional(),
  company: z.string()
    .min(2, 'Company name must be at least 2 characters')
    .max(100, 'Company name too long (max 100 characters)'),

  phone: z.string()
    .max(20, 'Phone number too long')
    .optional()
    .transform(val => val?.replace(/[\s.-]/g, '')),

  email: z.string()
    // .email('Invalid e-mail address')
    .max(60, 'E-mail too long')
    .optional(),

  whatsapp: z.string()
    .max(60, 'Whatsapp too long')
    .optional(),

  telegram: z.string()
    .max(60, 'Telegram too long')
    .optional(),

  site: z.string()
    .max(60, 'URL too long')
    .optional(),

  comments: z.string()
    .max(2000, 'Comments too long (max 2000 characters)')
    .optional()
})

type Schema = z.input<typeof schema>
// endregion ////

// region Data ////
const state = reactive<Partial<Schema>>(initializeState())

const itemsPreview = computed(() => [
  {
    label: 'Phone',
    code: 'phone',
    description: state.phone
  },
  {
    label: 'E-mail',
    code: 'email',
    description: state.email
  },
  {
    label: 'WhatsApp',
    code: 'whatsapp',
    description: state.whatsapp
  },
  {
    label: 'Telegram',
    code: 'telegram',
    description: state.telegram
  },
  {
    label: 'Website',
    code: 'site',
    description: state.site
  },
  {
    label: 'Comments',
    code: 'comments',
    description: state.comments
  }
])

function initializeState(): Partial<Schema> {
  return {
    logo: '',
    company: '',
    phone: '',
    email: '',
    whatsapp: '',
    telegram: '',
    site: '',
    comments: ''
  }
}

async function loadData(): Promise<void> {
  try {
    // @todo get real info
    Object.assign(state)
  } finally {
    isLoading.value = false
  }
}

async function clearData(): Promise<void> {
  Object.assign(state, initializeState())
}
// endregion ////

// region Form Actions ////
async function onSubmit(): Promise<void> {
  try {
    // todo make real call api
    console.log('Form submitted:', state)

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

const stepsMax = ref(4)
const step = ref(1)
const progress = computed(() => {
  return step.value === stepsMax.value
    ? 100
    : (100 / stepsMax.value) * step.value
})

async function nextStep() {
  if (step.value + 1 > stepsMax.value) {
    step.value = stepsMax.value
  } else {
    step.value += 1
  }
}

async function prevStep() {
  if (step.value - 1 < 1) {
    step.value = 1
  } else {
    step.value -= 1
  }
}
</script>

<template>
  <B24Modal
    title="Integrator Settings"
    description="Manage your company integration settings."
    :dismissible="false"
    :b24ui="{ content: 'max-w-[64rem]' }"
    :close="{ onClick: () => emit('close', false) }"
  >
    <template #body>
      <B24Progress
        v-model="progress"
        size="sm"
      />
      <div class="p-4 bg-white dark:bg-white/10 ">
        <!-- Content Section -->
        <section class="mb-8 p-6 grid grid-cols-1 md:grid-cols-2 gap-6 overflow-hidden w-full">
          <!-- Form Section -->
          <B24Form
            ref="form"
            :state="state"
            :schema="schema"
            class="space-y-6 min-w-[320px]"
            @submit="onSubmit"
          >
            <template v-if="step === 1">
              <ImageUploader
                v-model="state.logo"
                placeholder="Upload logo"
                class="w-full"
              />
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
            </template>
            <template v-else-if="step === 2">
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
            </template>
            <template v-else-if="step === 3">
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

              <B24FormField label="Website" name="site">
                <B24Input
                  v-model="state.site"
                  placeholder="https://example.com"
                  class="w-full"
                />
              </B24FormField>
            </template>
            <template v-else>
              <B24FormField
                label="Comments"
                name="comments"
                :hint="`${state.comments.length}/2000`"
              >
                <B24Textarea
                  v-model="state.comments"
                  placeholder="Additional information..."
                  class="w-full"
                  :rows="4"
                />
              </B24FormField>
            </template>
          </B24Form>

          <!-- Preview Section -->
          <div class="w-[320px] hidden md:block ">
            <div class="w-full bg-base-50 dark:bg-base-dark py-3 px-3 rounded-md flex items-center justify-center">
              <B24Button
                class="w-full ps-0 pe-0"
                label="Open"
                color="link"
                use-dropdown
                normal-case
                :b24ui="{ baseLine: 'w-full shrink-0' }"
              >
                <div class="flex flex-row items-center justify-start gap-4 w-full shrink-1">
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
                  <div class="min-w-0">
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

            <div class="w-full mt-2 bg-white dark:bg-base-dark shadow-lg rounded-2xs ring ring-base-300 dark:ring-base-800">
              <B24DescriptionList
                size="sm"
                class="px-3 rounded-lg overflow-hidden"
                :items="itemsPreview"
                :b24ui="{ container: 'mt-0' }"
              >
                <template #description="{ item }">
                  <template v-if="item.code !== 'comments'">
                    <ProseA :href="item.code === 'phone' ? item.description.replace(/[^\d+]/g, '') : item.description">
                      {{ item.description }}
                    </ProseA>
                  </template>
                  <template v-else>
                    {{ item.description }}
                  </template>
                </template>
              </B24DescriptionList>
            </div>
          </div>
        </section>
      </div>
    </template>

    <template #footer>
      <div class="px-4 w-full flex gap-2 items-center justify-between">
        <B24Button
          v-if="step > 1"
          label="Prev"
          color="link"
          size="xs"
          :icon="ChevronToTheLeftIcon"
          @click="prevStep"
        />
        <div v-else class="shrink-0" />

        <B24Button
          v-if="step < stepsMax"
          rounded
          size="sm"
          label="Next"
          color="primary"
          @click="nextStep"
        />
        <B24Button
          v-else
          rounded
          size="sm"
          label="Save"
          color="primary"
          @click="onSubmit"
        />
      </div>
    </template>
  </B24Modal>
</template>
