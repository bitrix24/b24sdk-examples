// app/plugins/initGlobal.ts
import { defineNuxtPlugin } from '#app'
import { useGlobalStore } from '~/stores/global'

console.log('⚡ initGlobal.client.ts running on:', process.client ? 'CLIENT' : 'SERVER')

export default defineNuxtPlugin(async () => {
    console.log('🛠  initGlobal.client.ts: initializing global store')
    const store = useGlobalStore()      // ← without transfering nuxtApp.$pinia
    if (!store.config) {
        await store.fetchConfig()
    }
})