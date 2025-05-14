<template>
    <div class="flex flex-col gap-4">
        Nothong to see here
    </div>    
</template>

<script setup lang="ts">
import { ref } from 'vue';

import { onMounted, onUnmounted } from 'vue'

const globalStore = useGlobalStore()

onMounted(async () => {
    try
    {
        globalStore.b24?.slider.openSliderAppPage({
			page: 'handler',
			bx24_width: 320,
			bx24_label: {
				bgColor: 'violet',
				text: 'Pomodoro',
				color: '#ffffff',
			},
			bx24_title: 'Pomodoro',
        })
    }
    catch (error)
    {
        console.error(error)
    }
})

</script>
