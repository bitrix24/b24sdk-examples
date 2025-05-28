<script setup lang="ts">
import { ref } from 'vue'
import type { RadioGroupItem } from '@bitrix24/b24ui-nuxt'
import { sleepAction } from '~/utils/sleep'
import SavePromptIcon from '@bitrix24/b24icons-vue/main/SavePromptIcon'
import Clock2Icon from '@bitrix24/b24icons-vue/main/Clock2Icon'

useHead({
    bodyAttrs: {
        class: 'slider-bg  py-10 px-4 min-h-screen flex items-start justify-center'
    }
})

// region Init ////
const { $logger, initApp, processErrorGlobal } = useAppInit()
const { $initializeB24Frame } = useNuxtApp()
const $b24 = await $initializeB24Frame()

const toast = useToast()

const isLoading = ref(true)

const breakOptions = ref<RadioGroupItem[]>([
    { label: '5 min', value: '5' },
    { label: '10 min', value: '10' },
    { label: '15 min', value: '15' }
])

const workOptions = ref<RadioGroupItem[]>([
    { label: '25 min', value: '25' },
    { label: '50 min', value: '50' },
    { label: '90 min', value: '90' }
])

const shortBreak = ref(5)
const longBreak = ref(15)
const workDuration = ref(25)
const repeats = ref(4)
// endregion ////

// region Actions ////
async function startFocusSession() {
    $logger.log(
        'Focus Session Started 🚀',
        {
            workDuration: workDuration.value,
            shortBreak: shortBreak.value
        }
    )
    // here you can start the timer, close the slider, etc.

    toast.add({
        title: 'Focus Session Started 🚀',
        icon: SavePromptIcon,
        color: 'success'
    })

    await sleepAction(2_000)
    await closeSlider()
}

async function closeSlider() {
    $logger.log('Slider closed — let\'s go!')
    await $b24.parent.closeApplication()
}

async function saveSettings() {
    $logger.log('Settings saved', {
        longBreak: longBreak.value,
        workDuration: workDuration.value,
        repeats: repeats.value
    })

    toast.add({
        title: 'Settings saved',
        icon: SavePromptIcon,
        color: 'success'
    })

    await sleepAction(2_000)

    await closeSlider()
}
// endregion ////

// region Lifecycle Hooks ////
onMounted(async () => {
    try {
        isLoading.value = true

        await initApp($b24)

        isLoading.value = false
    } catch (error) {
        processErrorGlobal(error, {
            homePageIsHide: true,
            isShowClearError: false,
            clearErrorHref: '/'
        })
    }
})
// endregion ////
</script>

<template>
    <div class="w-full max-w-[300px]">
        <template v-if="isLoading">
            <div class="my-8 max-lg:ps-3">
                <div
                    class="relative bg-white dark:bg-white/10 p-sm2 rounded-md flex flex-row gap-sm border-2 transition-shadow shadow border-base-master/10 dark:border-base-100/20">
                    <B24Skeleton class="h-5xl min-w-[110px] max-w-[400px] rounded-full" />
                    <B24Skeleton class="h-5xl min-w-[110px] max-w-[400px] rounded-full" />
                    <B24Skeleton class="h-5xl min-w-[110px] max-w-[400px] rounded-full" />
                </div>
            </div>
        </template>
        <template v-else>
            <!-- Title -->
            <ProseH1 class="text-center">
                Focus Session
            </ProseH1>

            <!-- Icon + Timer -->
            <div class="flex flex-col items-center mb-4 gap-2">
                <!-- Icon -->
                <Clock2Icon name="Animated::LoaderClockIcon" class="w-14 h-14" />

                <!-- Timer -->
                <ProseH3>
                    25:00
                </ProseH3>

                <B24Button color="primary" depth="dark" class="mb-4 mx-auto" label="Start Focus Session" loading-auto
                    @click.stop="startFocusSession" />
            </div>

            <!-- Form Section -->
            <form class="space-y-4 flex flex-col bg-white shadow-md rounded-xl p-6" @submit.prevent="">
                <B24FormField label="Long Break">
                    <B24RadioGroup v-model="longBreak" :items="breakOptions" orientation="horizontal" variant="list"
                        value-key="value" />
                </B24FormField>
                <B24FormField label="Work Session">
                    <B24RadioGroup v-model="workDuration" :items="workOptions" orientation="horizontal" variant="list"
                        value-key="value" />
                </B24FormField>
                <B24FormField label="Number of Repeats">
                    <B24InputNumber v-model="repeats" :min="1" :max="10" />
                </B24FormField>
                <div class="pt-4 flex justify-center">
                    <B24Button color="success" class="mx-auto" label="Save" loading-auto @click="saveSettings" />
                </div>
            </form>
        </template>
    </div>
</template>
