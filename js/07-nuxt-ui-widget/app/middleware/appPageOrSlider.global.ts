// app/middleware/appPageOrSlider.global.ts
import { LoggerBrowser } from '@bitrix24/b24jssdk'
import type { RouteLocationNormalized } from 'vue-router'
import { defineNuxtRouteMiddleware, navigateTo, useNuxtApp } from '#imports'

const logger = LoggerBrowser.build('middleware:app.page.or.slider.global', import.meta.env.DEV)

/**
 * Root path for the app
 */
const baseDir = '/'

// Set the logic for skipping the middleware
function isSkipPath(path: string) {
  return !path.startsWith(baseDir)
    || path.includes(`${baseDir}eula`)
    || path.includes(`${baseDir}render`)
}

export default defineNuxtRouteMiddleware(async (to: RouteLocationNormalized) => {
  // If on server, skip middleware
  if (import.meta.server) return

  logger.log('>> middleware start, to:', to.path)

  if (isSkipPath(to.path)) {
    logger.log('>> skip B24Frame for this path')
    return
  }

  try {
    // Initialize SDK
    const { $initializeB24Frame } = useNuxtApp()
    const b24 = await $initializeB24Frame()

    logger.log('>> placement options:', b24.placement.options)

    const page = b24.placement.options?.page as string | undefined
    if (page) {
      // Example mapping page → route
      let target: string | null = null

      switch (page) {
        case 'handler':
          target = `${baseDir}handler`
          break
      }

      if (target && to.path !== target) {
        logger.log(`>> navigating to ${target}`)
        return navigateTo(target)
      }
    }

    logger.log('>> middleware end — stay on', to.path)
  } catch (err: any) {
    logger.error('>> middleware error:', err)
    // we can show the error page if something goes wrong
    return navigateTo('/error')
  }
})