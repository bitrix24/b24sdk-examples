import tailwindcss from '@tailwindcss/vite'
import { readFileSync } from 'node:fs'

const locales = JSON.parse(process.env.NUXT_PUBLIC_CONTENT_LOCALES || '[]')

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: [
    '@bitrix24/b24ui-nuxt',
    '@nuxt/eslint',
    '@nuxt/content',
    '@nuxtjs/i18n',
    '@pinia/nuxt',
    '@bitrix24/b24jssdk-nuxt'
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
    host: '0.0.0.0',
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
  content: {
    /**
     * @memo: Under Docker::Nginx not use
     */
    watch: {
      enabled: false
    }
  },
  i18n: {
    bundle: {
      optimizeTranslationDirective: false
    },
    detectBrowserLanguage: false,
    strategy: 'no_prefix',
    lazy: true,
    defaultLocale: 'en',
    locales: locales
  }
})
