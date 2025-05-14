import { defineStore } from 'pinia'
import type { B24Frame } from '@bitrix24/b24jssdk'
import { LoggerBrowser, LoggerType, useB24Helper, LoadDataType, Type } from '@bitrix24/b24jssdk'
import { useNuxtApp } from '#app'
import { markRaw } from 'vue'

// Configure logger
const logger = LoggerBrowser.build(
  'MyPomodoroApp',
  import.meta.env?.DEV === true
)
if (process.env.NODE_ENV === 'development') {
  logger.enable(LoggerType.log)
}

const { initB24Helper, destroyB24Helper, getB24Helper } = useB24Helper()

type TypeOptions = {
	pomodoroPeriods: Record<string, number>
}

const defaultData: TypeOptions = {
	pomodoroPeriods: {
		shortBreak: 5,
		longBreak: 15,
		workDuration: 25,
		repeats: 4
	}
}

const optionsData: TypeOptions = reactive({
	pomodoroPeriods: {
		shortBreak: 5,
		longBreak: 15,
		workDuration: 25,
		repeats: 4
	}
})

export const useGlobalStore = defineStore('global', {
  state: () => ({
    config: null as null | Record<string, any>,
    b24: null as B24Frame | null
  }),
  actions: {
    /**
     * Initializes the Bitrix24 frame using b24jssdk (client-side only)
     */
    async initB24() {
      if (this.b24) return
      try {
        const { $initializeB24Frame } = useNuxtApp()
        const frame = await $initializeB24Frame()
        // Prevent Vue from wrapping the instance in a reactive proxy, so private fields stay intact
        this.b24 = markRaw(frame)
        logger.info('B24Frame initialized:', this.b24)
      } catch (error) {
        logger.error('Failed to initialize B24Frame:', error)
      }
    },

    /**
     * Fetches global configuration via Bitrix24 REST using callMethod
     */
    async fetchConfig() {
      // Ensure B24Frame is ready (only on client)
      await this.initB24()
      if (!this.b24) {
        logger.error('B24Frame not initialized, cannot fetch config')
        return
      }
      try {
        
        logger.info('b24 ready:', this.b24)
        logger.info('b24 options:', this.b24.isInit)

        await initB24Helper(
			this.b24,
			[
				LoadDataType.UserOptions,
			]
		)

        const optionsManager = getB24Helper().userOptions;
        optionsData.pomodoroPeriods = optionsManager.getJsonObject(
		    'pomodoro_config',
		    defaultData.pomodoroPeriods
	    ) as Record<string, number>

        logger.info('b24 user options:', optionsData)
    
        this.config = optionsData.pomodoroPeriods

      } catch (error) {
        logger.error('Error fetching global config via REST:', error)
      }
    },

    /**
     * Saves global configuration via Bitrix24 REST using callMethod
     */
    async saveConfig() {
      await this.initB24()
      if (!this.b24) {
        logger.error('B24Frame not initialized, cannot save config')
        return
      }
      try {

        await getB24Helper().userOptions.save(
			{
				pomodoro_config: getB24Helper().userOptions.encode(
					this.config
				)
			}
		)

        // const configJson = JSON.stringify(this.config)
        // await this.b24.options.userSet('pomodoro_config', configJson)
      } catch (error) {
        logger.error('Error saving global config via REST:', error)
      }
    },

    /**
     * Destroys the Bitrix24 frame when no longer needed
     */
    destroyB24() {
      destroyB24Helper()
      this.b24?.destroy()
      this.b24 = null
    }
  }
})

/**
 * Ensure this store is initialized only on the client.
 * Rename your plugin to `plugins/initGlobal.client.ts` or set `mode: 'client'` in nuxt.config.
 * In that plugin:
 *
 * ```ts
 * import { defineNuxtPlugin } from '#app'
 * import { useGlobalStore } from '~/stores/global'
 *
 * export default defineNuxtPlugin(async () => {
 *   const store = useGlobalStore()
 *   await store.fetchConfig()
 * })
 * ```
 */