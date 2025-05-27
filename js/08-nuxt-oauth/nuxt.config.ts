import tailwindcss from '@tailwindcss/vite'
import { readFileSync } from 'node:fs'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: [
    '@bitrix24/b24ui-nuxt',
    // `@bitrix24/b24jssdk-nuxt`,
    '@nuxt/eslint',
    'nuxt-auth-utils'
  ],
  devtools: { enabled: false },

  css: ['~/assets/css/main.css'],
  runtimeConfig: {
    // @todo remove this wait PR https://github.com/atinux/nuxt-auth-utils/pull/409
    oauth: {
      bitrix24: {
        clientId: '',
        clientSecret: ''
      }
    }
  },
  alias: {
    // @todo Wait PR https://github.com/atinux/nuxt-auth-utils/pull/409
    'nuxt-auth-utils-runtime': 'node_modules/nuxt-auth-utils/dist/runtime/server/lib/utils.js'
  },
  devServer: {
    port: 3000,
    host: '127.0.0.1',
    loadingTemplate: () => {
      return readFileSync('./template/devServer-loading.html', 'utf-8')
    }
    // host: 'custom.mydomain.local',
    // https: {
    //   key: 'E:/source/ssl/custom.mydomain.local-key.pem',
    //   cert: 'E:/source/ssl/custom.mydomain.local.pem'
    // }
  },

  future: {
    compatibilityVersion: 4
  },
  compatibilityDate: '2024-11-27',
  vite: {
    server: {
      // allow incoming requests from this host
      allowedHosts: [
        'currently-main-panda.cloudpub.ru',
        'sullenly-meteoric-partridge.cloudpub.ru'
      ],
      // and don't forget CORS, if needed:
      cors: true
    },
    plugins: [
      tailwindcss()
    ]
  }
})
