import tailwindcss from '@tailwindcss/vite'

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
    session: {
      maxAge: 60 * 60 * 24 * 7, // 1 week,
      password: process.env.NUXT_SESSION_PASSWORD || ''
    },
    /**
     * @memo this will be overwritten from .env or Docker_*
     * @see https://nuxt.com/docs/guide/going-further/runtime-config#example
     */
    appClientSecret: '',
    appScope: '',
    public: {
      appClientId: ''
    }
  },
  devServer: {
    port: 3000
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
