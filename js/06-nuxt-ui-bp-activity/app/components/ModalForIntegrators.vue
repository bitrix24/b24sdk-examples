<script setup lang="ts">
/**
 * @todo fix lang
 */
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import { z } from 'zod'
import type { FormError } from '@bitrix24/b24ui-nuxt'
import { stepSchemas, type Schema } from '~/types/validationSchemasForIntegrators'
import IncertImageIcon from '@bitrix24/b24icons-vue/editor/IncertImageIcon'
import ChevronToTheLeftIcon from '@bitrix24/b24icons-vue/actions/ChevronToTheLeftIcon'

// region Init ////
// const { t } = useI18n()
const emit = defineEmits<{ close: [boolean] }>()
const toast = useToast()
// endregion ////

// region Component State ////
const step = ref(1)
const stepsMax = ref(4)
const isLoading = ref(true)
const isStepValid = ref(false)
const validationErrors = ref<z.ZodFormattedError<Schema> | null>(null)
const fileUploadError = ref<string | undefined>(undefined)
const state = reactive<Schema>({
  logo: '',
  company: '',
  phone: '',
  email: '',
  whatsapp: '',
  telegram: '',
  site: '',
  comments: ''
})
const progress = computed(() => step.value === stepsMax.value ? 100 : (100 / stepsMax.value) * step.value)

async function loadData(): Promise<void> {
  try {
    isLoading.value = true
    // @todo get real info
    Object.assign(state, {})
  } finally {
    isLoading.value = false
  }
}

const stepTitle = computed(() => {
  switch (step.value) {
    case 1:
      return 'Let\'s upload your company logo and specify its name.'
    case 2:
      return 'Shall we indicate the phone number and e-mail?'
    case 3:
      return 'Will we show messengers? And the website?'
    case 4:
      return 'Anything to add?'
    default:
      return '?'
  }
})
// endregion ////

// region Form Actions ////
function nextStep() {
  validate()
  if (!isStepValid.value) {
    toast.add({
      title: 'Validation Error',
      description: 'Please check form fields',
      color: 'danger'
    })

    return
  }

  if (step.value < stepsMax.value) {
    step.value += 1

    validate()
  }
}

function prevStep() {
  if (step.value > 1) {
    step.value -= 1
    validate()
  }
}

async function onSubmit() {
  try {
    validate()
    if (!isStepValid.value) {
      toast.add({
        title: 'Validation Error',
        description: 'Please check form fields',
        color: 'danger'
      })

      return
    }

    // Submit logic here
    console.log('Form submitted:', state)
    toast.add({
      title: 'Success',
      description: 'The form has been submitted.',
      color: 'success'
    })

    emit('close', true)
  } catch (error) {
    console.error(error)

    toast.add({
      title: 'onSubmit Error',
      description: 'Please check log',
      color: 'danger'
    })
  }
}

const itemsPreview = computed(() => [
  {
    label: 'Phone',
    code: 'phone',
    description: state.phone?.slice(0, 60)
  },
  {
    label: 'E-mail',
    code: 'email',
    description: state.email?.slice(0, 60)
  },
  {
    label: 'Telegram',
    code: 'telegram',
    description: state.telegram?.slice(0, 60)
  },
  {
    label: 'WhatsApp',
    code: 'whatsapp',
    description: state.whatsapp?.slice(0, 60)
  },
  {
    label: 'Website',
    code: 'site',
    description: state.site?.slice(0, 60)
  },
  {
    label: 'Comments',
    code: 'comments',
    description: state.comments?.slice(0, 200)
  }
])
// endregion ////

// region Error Helpers ////
function validate(): FormError[] {
  try {
    const currentSchema = stepSchemas[step.value as keyof typeof stepSchemas]
    currentSchema.parse(state)
    isStepValid.value = true
    validationErrors.value = null
  } catch (error) {
    isStepValid.value = false
    if (
      error instanceof z.ZodError
    ) {
      validationErrors.value = error.format()
    }
  }

  if (!validationErrors.value) return []

  const errors = Object.entries(validationErrors.value).flatMap(([fieldName, fieldErrors]) => {
    const messages = (fieldErrors as { _errors?: string[] })._errors || []
    return messages.map(message => ({
      name: fieldName as keyof Schema,
      message
    }))
  })
  return errors
}

const handleFileUpload = () => {
  fileUploadError.value = undefined
}

const handleErrorFileUploader = (payload: string) => {
  fileUploadError.value = payload
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
  <B24Modal
    :title="stepTitle"
    :description="`Step ${step} from ${stepsMax}`"
    :dismissible="false"
    :b24ui="{ content: 'max-w-[64rem] h-full' }"
    :close="{ onClick: () => emit('close', false) }"
  >
    <template #body>
      <B24Progress
        v-model="progress"
        size="xs"
      />
      <div class="p-4 bg-white dark:bg-white/10 ">
        <!-- Content Section -->
        <section class="mb-8 p-6 grid grid-cols-1 md:grid-cols-2 gap-6 overflow-hidden w-full">
          <!-- Form Section -->
          <B24Form
            ref="form"
            dd-validate-on="['blur', 'change', 'input']"
            :validate="validate"
            :state="state"
            class="space-y-6"
            @submit.prevent="onSubmit"
          >
            <template v-if="step === 1">
              <B24FormField
                label="Company Name"
                name="company"
                required
                :hint="`${state.company.length}/100`"
              >
                <B24Input
                  v-model="state.company"
                  autofocus
                  placeholder="Enter company name"
                  class="w-full"
                />
              </B24FormField>
              <B24FormField
                name="logo"
                :error="fileUploadError"
              >
                <ImageUploader
                  v-model="state.logo"
                  placeholder="Upload logo"
                  class="w-full"
                  @image-upload="handleFileUpload"
                  @image-error="handleErrorFileUploader"
                />
              </B24FormField>
            </template>
            <template v-else-if="step === 2">
              <B24FormField
                label="Phone"
                name="phone"
              >
                <B24Input
                  v-model="state.phone"
                  type="tel"
                  autofocus
                  placeholder="+1234567890"
                  class="w-full"
                />
              </B24FormField>

              <B24FormField
                label="Email"
                name="email"
              >
                <B24Input
                  v-model="state.email"
                  type="email"
                  placeholder="contact@example.com"
                  class="w-full"
                />
              </B24FormField>
            </template>
            <template v-else-if="step === 3">
              <B24FormField
                label="Telegram"
                name="telegram"
                autofocus
                :hint="`${(state?.telegram || '').length}/60`"
              >
                <B24Input
                  v-model="state.telegram"
                  placeholder="https://t.me/..."
                  class="w-full"
                />
              </B24FormField>

              <B24FormField
                label="WhatsApp"
                name="whatsapp"
                :hint="`${(state?.whatsapp || '').length}/60`"
              >
                <B24Input
                  v-model="state.whatsapp"
                  placeholder="https://wa.me/..."
                  class="w-full"
                />
              </B24FormField>

              <B24FormField
                label="Website"
                name="site"
                :hint="`${(state?.site || '').length}/60`"
              >
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
                :hint="`${(state?.comments || '').length}/200`"
              >
                <B24Textarea
                  v-model="state.comments"
                  placeholder="Please provide important information..."
                  class="w-full"
                  :rows="7"
                />
              </B24FormField>
            </template>
          </B24Form>

          <!-- Preview Section -->
          <div class="">
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
                :b24ui="{ container: 'mt-0  sm:grid-cols-[min(20%,10rem)_auto]' }"
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
          :disabled="!isStepValid"
          @click="nextStep"
        />
        <B24Button
          v-else
          rounded
          size="sm"
          label="Save"
          color="success"
          :disabled="!isStepValid"
          @click="onSubmit"
        />
      </div>
    </template>
  </B24Modal>
</template>
