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
  },
  devServer: {
    port: 3000,
    host: '127.0.0.1'
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
