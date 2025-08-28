import tailwindcss from '@tailwindcss/vite'
import { readFileSync } from 'node:fs'
import { contentLocales } from './i18n/i18n.map'

/**
 * @see https://nuxt.com/docs/api/configuration/nuxt-config
 */
export default defineNuxtConfig({
  modules: [
    '@bitrix24/b24ui-nuxt',
    '@bitrix24/b24jssdk-nuxt',
    '@nuxt/eslint',
    '@nuxtjs/i18n',
    '@pinia/nuxt'
  ],
  devtools: { enabled: false },

  css: ['~/assets/css/main.css'],
  /**
   * @see https://nuxt.com/docs/guide/going-further/runtime-config#example
   */
  runtimeConfig: {
    public: {
      b24FormId: '',
      b24FormSecret: '',
      b24FormLoaderScript: '',
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
  compatibilityDate: '2025-07-16',
  vite: {
    plugins: [
      tailwindcss()
    ],
    server: {
      // allow incoming requests from this host
      allowedHosts: [
        '******.ngrok-free.app',
        'seasonally-punctual-agama.cloudpub.ru',
        'lazily-volcanic-moose.cloudpub.ru'
      ],
      // and don't forget CORS, if needed:
      cors: true
    }
  },
  i18n: {
    detectBrowserLanguage: false,
    strategy: 'no_prefix',
    langDir: 'locales',
    locales: contentLocales,
    /**
     * @memo defaultLocale mast be last at locales[]
     * @see https://i18n.nuxtjs.org/docs/v7/strategies#no_prefix
     */
    defaultLocale: 'en'
  }
})
