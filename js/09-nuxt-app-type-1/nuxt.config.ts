import tailwindcss from '@tailwindcss/vite'
import { readFileSync } from 'node:fs'

/**
 * @see https://nuxt.com/docs/api/configuration/nuxt-config
 */
export default defineNuxtConfig({
  modules: [
    '@bitrix24/b24ui-nuxt',
    '@bitrix24/b24jssdk-nuxt',
    '@nuxt/eslint'
  ],
  ssr: false,
  devtools: { enabled: false },
  app: {
    baseURL: '/dev-folder/'
  },

  css: ['~/assets/css/main.css'],
  /**
   * @see https://nuxt.com/docs/guide/going-further/runtime-config#example
   */
  runtimeConfig: {
    public: {}
  },
  // todo remove this
  // routeRules: {
  //   '/': { redirect: '/index.html' },
  //   '/install': { redirect: '/install.html' }
  //   // '/slider/demo': { redirect: '/slider/demo.html' },
  //   // '/handler/uf.demo': { redirect: '/handler/uf.demo.html' }
  // },
  devServer: {
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
    ],
    server: {
      // allow incoming requests from this host
      allowedHosts: [
        '******.ngrok-free.app',
        'possessively-uncommon-dragonet.cloudpub.ru'
      ],
      // and don't forget CORS, if needed:
      cors: true
    }
  }
})
