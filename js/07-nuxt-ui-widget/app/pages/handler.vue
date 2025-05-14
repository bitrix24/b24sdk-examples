<template>
    <div
        class="min-h-screen bg-gray-50 flex items-start justify-center py-10 px-4"
        style="background-image: url('/images/back.png'); background-size: cover; background-position: center;"
    >
        <!-- Wrapper -->
        <div class="w-full max-w-[300px]">
            <!-- Title -->
            <h1 class="text-2xl font-bold text-gray-900 mb-2 text-center">Focus Session</h1>

            <!-- Icon + Timer -->
            <div class="flex flex-col items-center mb-4 gap-2">
                <!-- Icon -->
                <B24ClockIcon name="Animated::LoaderClockIcon" class="w-14 h-14" />

                <!-- Timer -->
                <div class="text-8xl font-extrabold font-mono text-indigo-600">25:00</div>

                <!-- Button to start -->
                <B24Button
                    color="primary"
                    depth="dark"
                    class="mb-4 mx-auto"
                    @click="startFocusSession"
                >
                    Start Focus Session
                </B24Button>
            </div>

            <!-- Form Section -->
            <form @submit.prevent="" class="space-y-4 flex flex-col bg-white shadow-md rounded-xl p-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Long Break</label>
                      <B24RadioGroup
                        v-model="longBreak"
                        :items="breakOptions"
                        :orientation="orientation"
                        :variant="variant"
                        value-key="value"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Work Session</label>
                      <B24RadioGroup
                        v-model="workDuration"
                        :items="workOptions"
                        :orientation="orientation"
                        :variant="variant"
                        value-key="value"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Number of Repeats</label>
                    <B24InputNumber v-model="repeats" :min="1" :max="10" />
                </div>

                <!-- Button -->
                <div class="pt-4 flex justify-center">
                    <!-- Save-button -->
                    <B24Button
                        color="success"
                        class="mx-auto"
                        @click="saveSettings"
                    >
                        Save
                    </B24Button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';


import { B24Icon as B24ClockIcon } from '@bitrix24/b24icons-vue'

export interface ExampleProps {
  orientation?: any
  variant?: any
}

withDefaults(defineProps<ExampleProps>(), {
  orientation: 'horizontal',
  variant: 'list'
})

const settingsOpen = ref(false);

const breakOptions = ref([
    { label: '5 min', value: 5 },
    { label: '10 min', value: 10 },
    { label: '15 min', value: 15 }

]);

const workOptions = ref([
    { label: '25 min', value: 25 },
    { label: '50 min', value: 50 },
    { label: '90 min', value: 90 }
]);

const shortBreak = ref<number>(5);
const longBreak = ref<number>(15);
const workDuration = ref<number>(25);
const repeats = ref<number>(4);

function startFocusSession() {
    console.log('Focus Session Started 🚀', { workDuration: workDuration.value, shortBreak: shortBreak.value });
    // here you can start the timer, close the slider, etc.
}

function closeSlider() {
    console.log('Slider closed — let\'s go!');
    // logic for closing the slider
}

function saveSettings() {
    console.log('Settings saved', {
        longBreak: longBreak.value,
        workDuration: workDuration.value,
        repeats: repeats.value
    });
    closeSlider();
}


</script>
