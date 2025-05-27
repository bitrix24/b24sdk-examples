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

const counting = ref(false)

const startCountdown = () => {
    counting.value = true
}

const onCountdownEnd = () => {
    counting.value = false
}

const shortBreak = ref(5)
const longBreak = ref(15)
const workDuration = ref(25)
const repeats = ref(4)
// endregion ////

// region Actions ////
async function beginPause() {
    $logger.log(
        'Pause Begun 🚀',
        {
            workDuration: workDuration.value,
            shortBreak: shortBreak.value
        }
    )
    // here you can start the timer, close the slider, etc.

    toast.add({
        title: 'Pause Begun 🚀',
        icon: SavePromptIcon,
        color: 'success'
    })

    await sleepAction(2_000)
}

async function closeSlider() {
    $logger.log('Slider closed — let\'s go!')
    await $b24.parent.closeApplication()
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
            clearErrorHref: '/activity-list'
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
                It's time to take a break!
            </ProseH1>

            <!-- Icon + Timer -->
            <div class="flex flex-col items-center mb-4 gap-2">

                <B24Countdown size="96" seconds="60" :icon="Clock2Icon" />

                <B24Countdown seconds="360" :need-start-immediately="false" :show-minutes="true">
                    <template #leading>
                        <span class="mr-2 text-xl text-gray-400">⏰</span>
                    </template>
                    <template #default="{ formatTime }">
                        <span class="text-[5rem] font-bold text-primary-text">{{ formatTime }}</span>
                    </template>
                </B24Countdown>

                <B24Button color="primary" depth="dark" class="mb-4 mx-auto" label="Take some rest" loading-auto
                    @click.stop="beginPause" />

                <ProseP class="text-center">The slider will remain open until the pause is complete.</ProseP>
            </div>

        </template>
    </div>
</template>
