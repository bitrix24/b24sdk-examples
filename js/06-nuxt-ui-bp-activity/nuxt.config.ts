import tailwindcss from '@tailwindcss/vite'
import { readFileSync } from 'node:fs'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  /**
   * @memo App work under frame
   * Nuxt DevTools: Failed to check parent window
   * SecurityError: Failed to read a named property '__NUXT_DEVTOOLS_DISABLE__' from 'Window'
   */

  modules: [
    '@bitrix24/b24ui-nuxt',
    // `@bitrix24/b24jssdk-nuxt`,
    '@nuxt/eslint',
    '@nuxt/content'
  ],
  ssr: false, devtools: { enabled: false },

  css: ['~/assets/css/main.css'],
  devServer: {
    port: 3000,
    // host: 'custom.mydomain.local',
    // https: {
    //   key: '.../source/ssl/custom.mydomain.local-key.pem',
    //   cert: '.../source/ssl/custom.mydomain.local.pem'
    // },
    loadingTemplate: () => {
      return readFileSync('./template/devServer-loading.html', 'utf-8')
    }
  },

  future: {
    compatibilityVersion: 4
  },

  compatibilityDate: '2024-11-27',

  vite: {
    plugins: [
      tailwindcss()
    ]
  }
})
