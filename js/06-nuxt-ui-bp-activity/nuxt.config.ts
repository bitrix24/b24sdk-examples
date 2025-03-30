import tailwindcss from '@tailwindcss/vite'
import { readFileSync } from 'node:fs'

const locales = JSON.parse(process.env.NUXT_PUBLIC_CONTENT_LOCALES || '[]')

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: [
    '@bitrix24/b24ui-nuxt',
    // `@bitrix24/b24jssdk-nuxt`,
    '@nuxt/eslint',
    '@nuxt/content',
    '@nuxtjs/i18n',
    '@pinia/nuxt',
    // @todo remove this - need use b24 app settings ////
    '@pinia-plugin-persistedstate/nuxt'
  ],
  ssr: false,
  /**
   * @memo App work under frame
   * Nuxt DevTools: Failed to check parent window
   * SecurityError: Failed to read a named property '__NUXT_DEVTOOLS_DISABLE__' from 'Window'
   */
  devtools: { enabled: false },

  css: ['~/assets/css/main.css'],
  runtimeConfig: {
    public: {
      contentLocales: locales
    }
  },
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
  },
  i18n: {
    detectBrowserLanguage: false,
    strategy: 'no_prefix',
    lazy: true,
    defaultLocale: 'en',
    locales: locales
  },
  // @todo remove this - need use b24 app settings ////
  piniaPersistedstate: {
    cookieOptions: {
      sameSite: 'strict'
    },
    storage: 'localStorage'
  }
})
