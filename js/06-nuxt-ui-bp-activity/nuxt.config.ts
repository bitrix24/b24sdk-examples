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
    '@nuxt/content',
    '@nuxtjs/i18n'
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
  },
  i18n: {
    detectBrowserLanguage: false,
    strategy: 'no_prefix',
    lazy: true,
    langDir: 'locales',
    defaultLocale: 'en',
    locales: [
      {
        code: 'en',
        name: 'English',
        file: 'en.json'
      },
      {
        code: 'de',
        name: 'Deutsch',
        file: 'de.json'
      },
      {
        code: 'la',
        name: 'Español',
        file: 'la.json'
      },

      {
        code: 'br',
        name: 'Português (Brasil)',
        file: 'br.json'
      },
      {
        code: 'fr',
        name: 'Français',
        file: 'fr.json'
      },
      {
        code: 'it',
        name: 'Italiano',
        file: 'it.json'
      },

      {
        code: 'pl',
        name: 'Polski',
        file: 'pl.json'
      },
      {
        code: 'ru',
        name: 'Polski',
        file: 'ru.json'
      },
      {
        code: 'ua',
        name: 'Українська',
        file: 'ua.json'
      },

      {
        code: 'tr',
        name: 'Türkçe',
        file: 'tr.json'
      },
      {
        code: 'sc',
        name: '中文（简体）',
        file: 'sc.json'
      },
      {
        code: 'tc',
        name: '中文（繁體）',
        file: 'tc.json'
      },

      {
        code: 'ja',
        name: '日本語',
        file: 'ja.json'
      },
      {
        code: 'vn',
        name: 'Tiếng Việt',
        file: 'vn.json'
      },
      {
        code: 'id',
        name: 'Bahasa Indonesia',
        file: 'id.json'
      },
      {
        code: 'ms',
        name: 'Bahasa Melayu',
        file: 'ms.json'
      },
      {
        code: 'th',
        name: 'ภาษาไทย',
        file: 'th.json'
      },
      {
        code: 'ar',
        name: 'عربي',
        file: 'ar.json'
      },
      {
        code: 'kz',
        name: 'Қазақша',
        file: 'kz.json'
      }
    ]
  }
})
